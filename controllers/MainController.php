<?php

namespace controllers;

use Config;
use Product;
use ProductCategory;
use ProductType;
use services\ProductService;

class MainController extends BaseController
{
    protected string $viewsSectionName = 'main';

	private ProductService $productService;

	public function __construct()
	{
		parent::__construct();
		$this->productService = new ProductService();
	}

	/**
	 * @return void
	 */
    public function index()
    {
        $authData = $this->getAuthData();

        $this->render('index', [
            'title' => $this->getTitle('Главная страница'),
            'auth' => $authData,
			'indexProductId' => Config::MAIN_INDEX_PRODUCT_ID ?? null
        ]);
    }

	/**
	 * @return void
	 */
	public function productCategoryTypeList()
	{
		if (!isset($this->postParams['key_name'])) {
			$this->sendJsonResponse(['success' => false, 'error' => 'Не передан тип категорий', 'status' => 422]);
		} else {
			$keyName = $this->postParams['key_name'];
			$productType = ProductType::read('key_name', $keyName);
			if ($productType) {
				$categoryList = ProductCategory::findAll(['type_id' => $productType['id']]);
				$this->sendJsonResponse([
					'success' => true,
					'data' => array_merge($categoryList, ['type_name' => $productType['name']])
				]);
			}
			$this->sendJsonResponse(['success' => false, 'error' => 'Некорректный тип категорий', 'status' => 422]);
		}
	}

	/**
	 * @param int $categoryId
	 * @return void
	 */
	public function viewCategoryProducts(int $categoryId)
	{
		$authData = $this->getAuthData();

		$category = ProductCategory::readWithTypeName('id', $categoryId);
		if (!$category) {
			$this->renderNotFoundPage();
			return;
		}

		$this->render('category', [
			'title' => $this->getTitle($category['category_type_name'] . ' | ' . $category['name']),
			'auth' => $authData,
			'categoryId' => $category['id']
		]);
	}

	/**
	 * @param int $categoryId
	 * @return void
	 */
	public function getCategoryProductsList(int $categoryId): void
	{
		$category = ProductCategory::readWithTypeName('id', $categoryId);
		if (!$category) {
			$this->sendJsonResponse(['success' => false, 'error' => 'Категория не найдена', 'status' => 404]);
		}

		$categoryProductsList = $this->productService->getCategoryProductsList($categoryId, $this->postParams);
		if (isset($categoryProductsList['errors'])) {
			$this->sendJsonResponse($categoryProductsList);
		}

		$categoryProductsList['categoryTypeName'] = $category['category_type_name'];
		$categoryProductsList['categoryName'] = $category['name'];

		$this->sendJsonResponse([
			'success' => true,
			'data' => $categoryProductsList,
		]);
	}

	/**
	 * @param int $productId
	 * @return void
	 */
	public function getProduct(int $productId): void
	{
		$product = Product::read('id', $productId);
		if ($product) {
			$this->sendJsonResponse([
				'success' => true,
				'data' => $product,
			]);
		} else {
			$this->sendJsonResponse(['success' => false, 'error' => 'Ошибка при получении главного товара', 'status' => 422]);
		}
	}
}