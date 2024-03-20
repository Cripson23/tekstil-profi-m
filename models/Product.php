<?php

class Product extends Model
{
	protected static ?string $table = 'products';

	/**
	 * @param $field
	 * @param $value
	 * @param bool $withUniqueByCase
	 * @param bool $withImages
	 * @return mixed
	 */
	public static function read($field, $value, bool $withUniqueByCase = false, bool $withImages = true)
	{
		self::init();

		if ($withUniqueByCase) {
			$value = mb_strtolower($value);
			$field = "LOWER({$field})";
		}

		if ($withImages) {
			// Основной запрос для продуктов с объединением с таблицей изображений
			$query = "
				SELECT products.*, GROUP_CONCAT(product_images.file_name SEPARATOR '|') AS files FROM " . static::$table . " 
				LEFT JOIN product_images ON products.id = product_images.product_id 
				WHERE products.{$field} = :{$field}
			";
		} else {
			$query = "SELECT * FROM " . static::$table . " WHERE {$field} = :{$field}";
		}

		$stmt = self::$db->prepare($query);
		$stmt->bindValue(":$field", $value);
		$stmt->execute();

		$product = $stmt->fetch();

		if ($product) {
			$product['files'] = explode('|', $product['files']);
		}

		return $product;
	}

    /**
     * @param array $ids
     * @param bool $withImages
     * @return array|false
     */
    public static function getListByIds(array $ids, bool $withImages = true)
    {
        self::init();

        // Создаем плейсхолдеры для каждого элемента массива $ids
        $placeholders = implode(', ', array_fill(0, count($ids), '?'));

        if ($withImages) {
            // Основной запрос для продуктов с объединением с таблицей изображений
            $query = "
				SELECT products.*, GROUP_CONCAT(product_images.file_name SEPARATOR '|') AS files FROM " . static::$table . " 
				LEFT JOIN product_images ON products.id = product_images.product_id 
				WHERE products.id IN ($placeholders) GROUP BY products.id
			";
        } else {
            $query = "SELECT * FROM " . static::$table . " WHERE products.id IN ($placeholders) GROUP BY products.id";
        }

        $stmt = self::$db->prepare($query);
        // Привязываем каждый $id из $ids к соответствующему плейсхолдеру в запросе
        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 1, $id);
        }
        $stmt->execute();

        $products = $stmt->fetchAll() ?? [];

        self::prepareProductFiles($products);

        return $products;
    }

	/**
	 * @param array $filters
	 * @param string $sortField
	 * @param string $sortDirection
	 * @param int $page
	 * @param int $perPage
	 * @param bool $withImages
	 * @return array
	 */
	public static function findAll(
		array $filters = [],
		string $sortField = 'created_at',
		string $sortDirection = 'DESC',
		int $page = 1,
		int $perPage = 10,
		bool $withImages = true
	): array {
		self::init();

		$whereConditions = [];
		$queryParams = [];

		foreach ($filters as $field => $value) {
			// Для диапазона значений (предполагается, что $value - массив с ключами 'from' и 'to')
			if (is_array($value) && isset($value['from']) && isset($value['to'])) {
				$whereConditions[] = "{$field} BETWEEN :{$field}_from AND :{$field}_to";
				$queryParams["{$field}_from"] = $value['from'];
				$queryParams["{$field}_to"] = $value['to'];
				// Для текстового поиска (можно использовать LIKE для частичного совпадения)
			} elseif (is_string($value)) {
				$whereConditions[] = "{$field} LIKE :{$field}";
				$queryParams[$field] = "%$value%";
				// Для числовых значений
			} elseif (is_numeric($value)) {
				$whereConditions[] = "{$field} = :{$field}";
				$queryParams[$field] = $value;
			}
		}

		$whereSQL = !empty($whereConditions) ? ' WHERE ' . implode(' AND ', $whereConditions) : '';

		// Запрос для подсчета общего количества элементов
		$countQuery = "SELECT COUNT(*) FROM " . static::$table . $whereSQL;
		$countStmt = self::$db->prepare($countQuery);
		foreach ($queryParams as $param => $value) {
			$countStmt->bindValue(":{$param}", $value);
		}
		$countStmt->execute();
		$totalCount = $countStmt->fetchColumn(); // Получаем общее количество элементов

		// Сортировка
		$sortSQL = " ORDER BY {$sortField} {$sortDirection}";
		$offset = ($page - 1) * $perPage;
		$limitSQL = " LIMIT {$perPage} OFFSET {$offset}";

        if ($withImages) {
			// Основной запрос для продуктов с объединением с таблицей изображений
			$query = "
				SELECT products.*, GROUP_CONCAT(product_images.file_name SEPARATOR '|') AS files,
				       GREATEST(0, COALESCE(products.old_price, products.price) - products.price) AS discount_price
				FROM " . static::$table . " AS products
				LEFT JOIN product_images ON products.id = product_images.product_id
				{$whereSQL}
				GROUP BY products.id
				{$sortSQL}
				{$limitSQL}
			";
		} else {
			$query = "SELECT *, GREATEST(0, COALESCE(products.old_price, products.price) - products.price) AS discount_price FROM " . static::$table . $whereSQL . $sortSQL . $limitSQL;
		}

		$stmt = self::$db->prepare($query);

		foreach ($queryParams as $param => $value) {
			$stmt->bindValue(":{$param}", $value);
		}

		$stmt->execute();
		$items = $stmt->fetchAll() ?? [];

        self::prepareProductFiles($items);

		// Возвращаем как результаты запроса, так и общее количество элементов
		return ['items' => $items, 'totalCount' => $totalCount];
	}

	/**
	 * @param int $categoryId
	 * @return mixed
	 */
	public static function getProductPriceRangeByCategory(int $categoryId)
	{
		// Запрос для получения минимальной стоимости
		$rangePriceQuery = "SELECT MIN(price) as minPrice, CEIL(MAX(price)) as maxPrice FROM " . static::$table . " WHERE category_id = :categoryId";
		$rangePriceStmt = self::$db->prepare($rangePriceQuery);
		$rangePriceStmt->bindValue(':categoryId', $categoryId);
		$rangePriceStmt->execute();

		return $rangePriceStmt->fetch();
	}

    /**
     * @param array $items
     * @return void
     */
    private static function prepareProductFiles(array &$items)
    {
        // Обработка файлов
        foreach ($items as &$item) {
            if (!empty($item['files'])) {
                $item['files'] = explode('|', $item['files']);
            } else {
                $item['files'] = [];
            }
        }
    }
}