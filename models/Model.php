<?php

use general\Database;

class Model {
    protected static PDO $db;
    protected static ?string $table = null;

    public static function init() {
        self::$db = Database::getInstance()->getConnection();
    }

	/**
	 * @param array $data
	 * @return int
	 */
    public static function create(array $data): int
    {
        self::init();  // Проверка инициализации соединения с БД

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $query = "INSERT INTO " . static::$table . " ({$columns}) VALUES ({$placeholders})";

        $stmt = self::$db->prepare($query);

        foreach ($data as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(":$key", $value, $type);
        }

        if ($stmt->execute()) {
            $lastInsertedId = self::$db->lastInsertId();
            return $lastInsertedId ? (int)$lastInsertedId : 0;
        } else {
            return 0;
        }
    }

	/**
	 * @param array $rows
	 * @return bool
	 */
    public static function createMultiple(array $rows): bool
    {
        self::init();

        // Проверяем, не пустой ли массив данных
        if (empty($rows)) {
            return false;
        }

        $columns = implode(', ', array_keys($rows[0]));
        $values = [];
        $bindValues = [];

        foreach ($rows as $rowIndex => $row) {
            $rowValues = [];
            foreach ($row as $columnName => $value) {
                $param = ":{$columnName}_{$rowIndex}";
                $rowValues[] = $param;
                $bindValues[$param] = $value; // Собираем значения для подготовленного запроса
            }
            $values[] = '(' . implode(', ', $rowValues) . ')'; // Формируем строку значений для каждой строки
        }

        $valuesString = implode(', ', $values); // Объединяем все строки в одну строку запроса
        $query = "INSERT INTO " . static::$table . " ({$columns}) VALUES {$valuesString}";

        $stmt = self::$db->prepare($query); // Подготавливаем запрос

        // Привязываем значения параметров
        foreach ($bindValues as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }

        return $stmt->execute(); // Выполняем запрос и возвращаем результат
    }

    /**
     * @param $field
     * @param $value
     * @param bool $withUniqueByCase
     * @return mixed
     */
    public static function read($field, $value, bool $withUniqueByCase = false)
    {
        self::init();

        if ($withUniqueByCase) {
            $value = mb_strtolower($value);
            $field = "LOWER({$field})";
        }

        $query = "SELECT * FROM " . static::$table . " WHERE {$field} = :{$field}";
        $stmt = self::$db->prepare($query);
        $stmt->bindValue(":$field", $value);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool|null
     */
    public static function update(int $id, array $data): bool
    {
        self::init();

        $updates = '';
        foreach ($data as $key => $value) {
            $updates .= "{$key} = :{$key}, ";
        }
        $updates = rtrim($updates, ', ');

        $query = "UPDATE " . static::$table . " SET {$updates} WHERE id = :id";

        $stmt = self::$db->prepare($query);
        $stmt->bindValue(':id', $id);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        return $stmt->execute();
    }

    /**
     * @param int $id
     * @return bool|null
     */
    public static function delete(int $id): bool
    {
        self::init();

        $query = "DELETE FROM " . static::$table . " WHERE id = :id";
        $stmt = self::$db->prepare($query);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

	/**
	 * @param array $filters
	 * @param string $sortField
	 * @param string $sortDirection
	 * @param int $page
	 * @param int $perPage
	 * @return array
	 */
	public static function findAll(
		array $filters = [],
		string $sortField = 'created_at',
		string $sortDirection = 'DESC',
		int $page = 1,
		int $perPage = 10
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

		$query = "SELECT * FROM " . static::$table . $whereSQL . $sortSQL . $limitSQL;
		$stmt = self::$db->prepare($query);

		foreach ($queryParams as $param => $value) {
			$stmt->bindValue(":{$param}", $value);
		}

		$stmt->execute();
		$items = $stmt->fetchAll() ?? [];

        // Возвращаем как результаты запроса, так и общее количество элементов
        return ['items' => $items, 'totalCount' => $totalCount];
	}
}