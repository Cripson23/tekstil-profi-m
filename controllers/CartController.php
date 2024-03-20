<?php

namespace controllers;

use Product;
use services\CartService;

class CartController extends BaseController
{
	private CartService $cartService;

	public function __construct()
	{
		parent::__construct();
		$this->cartService = new CartService();
	}

    /**
     * @return void
     */
	public function getCartData(): void
	{
        $this->getAuthUser();

        $result = $this->cartService->getCartData($this->session);

        $this->sendJsonResponse($result);
	}

	/**
	 * @return void
	 */
	public function increaseQuantityItem()
	{
		$this->getAuthUser();

		$productId = $this->postParams['productId'];
		if (!Product::read('id', $productId, false, false)) {
			$this->sendJsonResponse(['success' => false, 'error' => 'Данный товар не существует', 'status' => 422]);
		}

		$result = $this->cartService->increaseQuantityItem($productId);

		$this->sendJsonResponse($result);
	}

	/**
	 * @return void
	 */
	public function decreaseQuantityItem()
	{
		$this->getAuthUser();

		$productId = $this->postParams['productId'];
		if (!Product::read('id', $productId, false, false)) {
			$this->sendJsonResponse(['success' => false, 'error' => 'Данный товар не существует', 'status' => 422]);
		}

		$result = $this->cartService->decreaseQuantityItem($productId);

		$this->sendJsonResponse($result);
	}

    /**
     * @return void
     */
    public function changeQuantityItem()
    {
        $this->getAuthUser();

        $productId = $this->postParams['productId'];
        if (!Product::read('id', $productId, false, false)) {
            $this->sendJsonResponse(['success' => false, 'error' => 'Данный товар не существует', 'status' => 422]);
        }

        $quantity = $this->postParams['quantity'];
        if (!isset($quantity)) {
            $this->sendJsonResponse(['success' => false, 'error' => 'Необходимо указать количество', 'status' => 422]);
        }

        $result = $this->cartService->changeQuantityItem($productId, $quantity);

        $this->sendJsonResponse($result);
    }

	/**
	 * @return void
	 */
	public function removeItem()
	{
		$this->getAuthUser();

		$productId = $this->postParams['productId'];
		/*if (!Product::read('id', $productId, false, false)) {
			$this->sendJsonResponse(['success' => false, 'error' => 'Данный товар не существует', 'status' => 422]);
		}*/

		$result = $this->cartService->removeItem($productId);

		$this->sendJsonResponse($result);
	}
}