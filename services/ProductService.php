<?php

namespace services;

use Product;
use validators\ListValidator;

class ProductService
{
	const DEFAULT_PRODUCT_PER_PAGE = 10;

	/**
	 * @return array[]
	 */
	private static function getSettingListFields(): array
	{
		return [
			'FILTER' => [
				'ALL' => ['name', 'brand_name', 'price'],
				'INT' => ['price'],
				'ADDITIONAL' => []
			],
			'PAGINATION' => [
				'RANGE' => [5, 10, 15, 20, 25, 30]
			],
			'SORT' => ['name', 'brand_name', 'price', 'discount_price', 'created_at']
		];
	}

    /**
     * Список товаров по категории
     * @param int $categoryId
     * @param array $listParams
     * @return array
     */
	public function getCategoryProductsList(int $categoryId, array $listParams): array
	{
		$validateErrors = ListValidator::validate($listParams, self::getSettingListFields());
		if (count($validateErrors) > 0) {
			return [
				'success' => false,
				'errors' => $validateErrors,
				'status' => 422
			];
		}

		$filters = $listParams['filters'] ?? [];
		$pagination = $listParams['pagination'] ?? [];
		$sort = $listParams['sort'] ?? [];

		$products = Product::findAll(
			array_merge($filters, ['category_id' => $categoryId]),
			$sort['field'] ?? 'created_at',
			$sort['direction'] ?? 'DESC',
			$pagination['page'] ?? 1,
			$pagination['per_page'] ?? self::DEFAULT_PRODUCT_PER_PAGE
		);

		$products['priceRange'] = Product::getProductPriceRangeByCategory($categoryId);

		return $products;
	}
}