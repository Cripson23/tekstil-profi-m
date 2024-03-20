<?php

namespace services;

use Model;
use Product;

class CartService extends Model
{
    /**
     * @param array|null $session
     * @return array
     */
    public function getCartData(?array $session): array
    {
        $totalPrice = 0;
		$totalDiscountAll = 0;
        $resultData = ['products' => []];

        if (isset($session['cart'])) {
            $productIds = array_keys($session['cart']);

			if (count($productIds) > 0) {
				$products = Product::getListByIds($productIds);

				foreach ($products as $product) {
					$productQuantity = $session['cart'][$product['id']];

					$totalProductPrice = $product['price'] * $productQuantity;
					$totalPrice += $totalProductPrice;

					$discount = $product['old_price'] ? ($product['old_price'] - $product['price']) : 0;
					$totalDiscount = $product['old_price'] ? (($product['old_price'] - $product['price']) * $productQuantity) : 0;
					$totalDiscountAll += $totalDiscount;

					$resultData['products'][] = [
						'id' => $product['id'],
						'title' => $product['name'] . ' ' . $product['brand_name'],
						'price' => $product['price'],
						'discount' => round($discount, 2),
						'totalDiscount' => round($totalDiscount, 2),
						'quantity' => $productQuantity,
						'total' => round($totalProductPrice, 2),
						'images' => $product['files']
					];
				}
			}
        }

        $resultData['totalDiscountAll'] = round($totalDiscountAll, 2);
        $resultData['totalPrice'] = round($totalPrice, 2);

        return [
            'success' => true,
            'data' => $resultData
        ];
    }

	/**
	 * @param int $productId
	 * @return array
	 */
	public function increaseQuantityItem(int $productId): array
	{
		if (isset($_SESSION['cart'][$productId])) {
			$_SESSION['cart'][$productId] += 1;
		} else {
			$_SESSION['cart'][$productId] = 1;
		}

		return ['success' => true];
	}

	/**
	 * @param int $productId
	 * @return array
	 */
	public function decreaseQuantityItem(int $productId): array
	{
		if (isset($_SESSION['cart'][$productId])) {
			if ($_SESSION['cart'][$productId] <= 1) {
				unset($_SESSION['cart'][$productId]);
			} else {
				$_SESSION['cart'][$productId] -= 1;
			}
		}

		return ['success' => true];
	}

    /**
     * @param int $productId
     * @param int $quantity
     * @return bool[]
     */
    public function changeQuantityItem(int $productId, int $quantity): array
    {
        if (isset($_SESSION['cart'][$productId])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$productId]);
            } else if ($quantity > 999) {
                $_SESSION['cart'][$productId] = 999;
            } else {
                $_SESSION['cart'][$productId] = $quantity;
            }
        }

        return ['success' => true];
    }

	/**
	 * @param int $productId
	 * @return array
	 */
	public function removeItem(int $productId): array
	{
		unset($_SESSION['cart'][$productId]);

		return ['success' => true];
	}
}