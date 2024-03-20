<?php

class ProductCategory extends Model
{
	protected static ?string $table = 'product_categories';

	/**
	 * @param $field
	 * @param $value
	 * @return array|null
	 */
	public static function readWithTypeName($field, $value): ?array
	{
		self::init();

		$query = "SELECT product_categories.*, product_types.name AS category_type_name FROM "
			. static::$table . " JOIN product_types ON product_categories.type_id = product_types.id WHERE product_categories.id = :id";
		$stmt = self::$db->prepare($query);
		$stmt->bindValue(":$field", $value);
		$stmt->execute();

		return $stmt->fetch();
	}
}