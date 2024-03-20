<?php

namespace services;

use Config;
use Exception;
use general\Logger;
use helpers\DateTimeHelper;
use Order;
use OrderProducts;
use User;
use validators\ListValidator;
use validators\OrderValidator;

class OrderService
{
	const DEFAULT_ORDER_PER_PAGE = 5;

	private CartService $cartService;
	private MailService $mailService;

	public function __construct()
	{
		$this->cartService = new CartService();
        $this->mailService = new MailService();
	}

	/**
	 * @return array[]
	 */
	private static function getSettingListFields(): array
	{
		return [
			'FILTER' => [
				'ALL' => ['id', 'status'],
				'INT' => ['id', 'status'],
				'ADDITIONAL' => [
					'status' => function ($value) {
						return in_array($value, array_values(Order::STATUSES));
					}
				]
			],
			'PAGINATION' => [
				'RANGE' => [10, 15, 30]
			],
			'SORT' => ['id', 'total_price', 'total_discount', 'total_positions_quantity', 'total_products_quantity', 'created_at']
		];
	}

	/**
	 * @param int|null $userId
	 * @param array $session
	 * @return array
	 * @throws Exception
	 */
	public function create(?int $userId, array $session): array
	{
		if (!$userId || !$user = User::read('id', $userId)) {
			return [
				'success' => false,
				'error' => 'Не удалось получить пользовательскую информацию',
			];
		}

		$cartData = $this->cartService->getCartData($session)['data'];

        // create order
		$totalProductsCount = 0;

		foreach ($cartData['products'] as $product) {
			$totalProductsCount += $product['quantity'];
		}

		$orderData = [
			'user_id' => $userId,
			'total_price' => $cartData['totalPrice'],
			'total_discount' => $cartData['totalDiscountAll'],
			'total_positions_quantity' => count($cartData['products']),
			'total_products_quantity' => $totalProductsCount,
			'user_name' => $user['name'],
			'user_email' => $user['email'],
			'user_delivery_address' => $user['delivery_address'],
			'user_phone' => $user['phone'],
			'status' => Order::STATUSES['NEW']
		];

		$orderId = Order::create($orderData);
		if (!$orderId) {
			return [
				'success' => false,
				'error' => 'Не удалось создать заказ',
			];
		}

        // create order products
        $orderProductsData = [];

        foreach ($cartData['products'] as $product) {
            $orderProductsData[] = [
                'order_id' => $orderId,
                'title' => $product['title'],
                'price' => $product['price'],
                'discount' => $product['discount'],
                'total_discount' => $product['totalDiscount'],
                'quantity' => $product['quantity'],
                'total_price' => $product['total'],
                'image_full_path' => '/resources/img/products/' . $product['id'] . '/' . $product['images'][0]
            ];
        }

        if (!OrderProducts::createMultiple($orderProductsData)) {
            Order::delete($orderId);

            return [
                'success' => false,
                'error' => 'Не удалось создать заказ',
            ];
        }

		unset($_SESSION['cart']);

        $emailAdminContent = $this->prepareEmailOrderContent($cartData, $user, $orderId, true);
        $emailUserContent = $this->prepareEmailOrderContent($cartData, $user, $orderId, false);

        try {
            $this->mailService->sendEmail(Config::COMPANY['ADMIN_EMAIL'], "Новый заказ №{$orderId} на сайте!", $emailAdminContent);
			$this->mailService->sendEmail($user['email'], "Оформление заказа №{$orderId}", $emailUserContent);
        } catch (Exception $exception) {
            Logger::logMessage($exception);
        }

		return [
			'success' => true,
			'data' => ['order_id' => $orderId]
		];
	}

    /**
	 * Подготовка шаблона письма заказа для администратора / пользователя
     * @throws Exception
     */
    private function prepareEmailOrderContent(array $cartData, array $user, int $orderId, bool $isForAdmin): string
    {
        $emailTemplatePath = $isForAdmin ? './email/order.html' : './email/order-user.html';
        if (file_exists($emailTemplatePath)) {
            $emailBody = file_get_contents($emailTemplatePath);

            // Заменяем маркеры на данные пользователя
            $replacements = [
				'{order_id}' => $orderId,
                '{site_name}' => Config::COMPANY['NAME'],
                '{user_id}' => $user['id'],
                '{user_name}' => $user['name'],
                '{user_email}' => $user['email'],
                '{user_delivery_address}' => $user['delivery_address'],
                '{user_phone}' => $user['phone'],
            ];

            foreach ($replacements as $marker => $value) {
                $emailBody = str_replace($marker, $value, $emailBody);
            }

            // Создаем HTML-строку для товаров
            $productsHtml = '';
            foreach ($cartData['products'] as $product) {
                $productHtml = '<tr>
                                <td>' . $product['title'] . '</td>
                                <td>' . round($product['price'], 2) . '</td>
                                <td>' . round($product['discount'], 2) . '</td>
                                <td>' . $product['quantity'] . '</td>
                                <td>' . round($product['total'], 2) . '</td>
                                <td>' . round($product['totalDiscount'], 2) . '</td>
                            </tr>';
                $productsHtml .= $productHtml;
            }
            $productsHtml .= '<tr>
                                <td style="border-bottom: none"></td>
                                <td style="border-bottom: none"></td>
                                <td style="border-bottom: none"></td>
                                <td style="border-bottom: none"></td>
                                <td style="font-weight: bold; border-bottom: none">' . round($cartData['totalPrice'], 2) . '</td>
                                <td style="font-weight: bold; border-bottom: none">' . round($cartData['totalDiscountAll'], 2) . '</td>
                            </tr>';

            // Заменяем маркер товаров в шаблоне
            return str_replace('{products}', $productsHtml, $emailBody);

        } else {
            // Обработка ошибки: файл шаблона не найден
            throw new Exception("Email template file not found.");
        }
    }

	/**
	 * @param int|null $userId
	 * @param array $orderCurtainsData
	 * @return array
	 * @throws Exception
	 */
	public function orderCurtains(?int $userId, array $orderCurtainsData): array
	{
		$validateErrors = OrderValidator::validateCurtainsData($orderCurtainsData);
		if (count($validateErrors) > 0) {
			return [
				'success' => false,
				'errors' => $validateErrors,
				'status' => 422
			];
		}

		if ($userId) {
			$user = User::read('id', $userId);
			if (!$user) {
				return [
					'success' => false,
					'error' => 'Не удалось получить пользовательскую информацию',
				];
			}

			$orderCurtainsData = array_merge($orderCurtainsData, [
				'curtains_name' => $user['name'],
				'curtains_email' => $user['email'],
				'curtains_phone' => $user['phone'],
			]);
		}

		$emailContent = $this->prepareEmailOrderCurtainsContent($orderCurtainsData);

		try {
			$this->mailService->sendEmail(Config::COMPANY['ADMIN_EMAIL'], "Новая заявка на пошив штор на сайте!", $emailContent);
		} catch (Exception $exception) {
			Logger::logMessage($exception);
		}

		return [
			'success' => true,
			'message' => 'Ваша заявка на пошив штор успешно отправлена'
		];
	}

	/**
	 * Подготовка шаблона письма для заявки на пошив штор
	 * @throws Exception
	 */
	private function prepareEmailOrderCurtainsContent(array $orderCurtainsData): string
	{
		$emailTemplatePath = './email/order-curtains.html';
		if (file_exists($emailTemplatePath)) {
			$emailBody = file_get_contents($emailTemplatePath);

			// Заменяем маркеры на данные пользователя
			$replacements = [
				'{site_name}' => Config::COMPANY['NAME'],
				'{user_name}' => $orderCurtainsData['curtains_name'],
				'{user_email}' => $orderCurtainsData['curtains_email'],
				'{user_phone}' => $orderCurtainsData['curtains_phone'],
				'{curtains_wish}' => $orderCurtainsData['curtains_wish'],
			];

			foreach ($replacements as $marker => $value) {
				$emailBody = str_replace($marker, $value, $emailBody);
			}

			return $emailBody;

		} else {
			// Обработка ошибки: файл шаблона не найден
			throw new Exception("Email template file not found.");
		}
	}

	/**
	 * @param int $userId
	 * @param array $listParams
	 * @return array
	 * @throws Exception
	 */
	public function getUserOrdersList(int $userId, array $listParams): array
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

		$ordersData = Order::findAll(
			array_merge($filters, ['user_id' => $userId]),
			$sort['field'] ?? 'created_at',
			$sort['direction'] ?? 'DESC',
			$pagination['page'] ?? 1,
			$pagination['per_page'] ?? self::DEFAULT_ORDER_PER_PAGE
		);

		$ordersData['statusList'] = Order::getStatusList();

		foreach ($ordersData['items'] as &$item) {
			$item['created_at'] = DateTimeHelper::convertFormatDateTime($item['created_at'], 'd.m.Y H:i');
		}

		return [
			'success' => true,
			'data' => $ordersData
		];
	}
}