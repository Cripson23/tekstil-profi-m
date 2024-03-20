<?php

class Order extends Model
{
	protected static ?string $table = 'orders';

	const STATUSES = [
		'NEW' => 1,
		'DELIVERY' => 2,
		'SUCCESS' => 3,
		'CANCEL' => 4
	];

	/**
	 * @return array
	 */
	public static function getStatusList(): array
	{
		return [
			self::STATUSES['NEW'] => 'Новый',
			self::STATUSES['DELIVERY'] => 'В доставке',
			self::STATUSES['SUCCESS'] => 'Завершен',
			self::STATUSES['CANCEL'] => 'Отменен',
		];
	}

    /**
     * @param $field
     * @param $value
     * @param bool $withUniqueByCase
     * @return array
     */
    public static function readOrderWithProducts($field, $value, bool $withUniqueByCase = false): array
    {
        self::init();

        if ($withUniqueByCase) {
            $value = mb_strtolower($value);
            $field = "LOWER({$field})";
        }

        $query = "SELECT orders.id as order_id, orders.total_price as order_total_price, orders.total_discount as order_total_discount, orders.*, order_products.* 
              FROM " . static::$table . " AS orders
              LEFT JOIN order_products ON orders.id = order_products.order_id 
              WHERE orders.{$field} = :{$field}";

        $stmt = self::$db->prepare($query);
        $stmt->bindValue(":$field", $value);
        $stmt->execute();
        $rows = $stmt->fetchAll(); // Получаем все строки

        $orderData = []; // Инициализируем пустой массив для данных заказа

        foreach ($rows as $row) {
            // Если массив заказа пуст, заполняем его данными
            if (empty($orderData)) {
                $orderData = [
                    'id' => $row['order_id'],
                    'user_id' => $row['user_id'],
                    'total_price' => $row['order_total_price'],
                    'total_discount' => $row['order_total_discount'],
                    'total_positions_quantity' => $row['total_positions_quantity'],
                    'total_products_quantity' => $row['total_products_quantity'],
                    'user_name' => $row['user_name'],
                    'user_email' => $row['user_email'],
                    'user_delivery_address' => $row['user_delivery_address'],
                    'user_phone' => $row['user_phone'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at'],
                    'products' => [] // Подготавливаем массив для товаров
                ];
            }

            // Добавляем продукты, если они есть
            if (!empty($row['order_id'])) { // Проверяем, существует ли продукт
                $orderData['products'][] = [
                    'title' => $row['title'],
                    'price' => $row['price'],
                    'discount' => $row['discount'],
                    'quantity' => $row['quantity'],
                    'total_discount' => $row['total_discount'],
                    'total_price' => $row['total_price'],
                    'image_full_path' => $row['image_full_path'],
                ];
            }
        }

        return $orderData;
    }
}