<?php

namespace controllers;

use Exception;
use helpers\DateTimeHelper;
use Order;
use services\OrderService;

class OrderController extends BaseController
{
    protected string $viewsSectionName = 'account';

	private OrderService $orderService;

	public function __construct()
	{
		parent::__construct();
		$this->orderService = new OrderService();
	}

	/**
	 * @return void
	 */
    public function index()
    {
        $userData = $this->getAuthUser();

        $this->render('orders', [
            'title' => $this->getTitle('Мои заказы'),
            'user' => $userData,
        ]);
    }

	/**
	 * @return void
	 * @throws Exception
	 */
	public function getOrdersList()
	{
		$userData = $this->getAuthUser();

		$result = $this->orderService->getUserOrdersList($userData['id'], $this->postParams);

		$this->sendJsonResponse($result);
	}

    /**
     * @throws Exception
     */
    public function getOrder(int $orderId)
    {
        $userData = $this->getAuthUser();

        $orderData = Order::readOrderWithProducts('id', $orderId);
        $orderData['created_at'] = DateTimeHelper::convertFormatDateTime($orderData['created_at'], 'd.m.Y H:i');

        if ($orderData && $userData['id'] === $orderData['user_id']) {
            $this->sendJsonResponse([
                'success' => true,
                'data' => $orderData,
            ]);
        } else {
            $this->sendJsonResponse(['success' => false, 'error' => 'Ошибка при получении данных заказа', 'status' => 422]);
        }
    }

	/**
	 * @return void
	 * @throws Exception
	 */
	public function create()
	{
		$authData = $this->getAuthData();

		$result = $this->orderService->create($authData['user_id'], $this->session);

		$this->sendJsonResponse($result);
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	public function orderCurtains()
	{
		$authData = $this->getAuthData();
		$orderCurtainsData = $this->postParams;

		$result = $this->orderService->orderCurtains($authData['user_id'], $orderCurtainsData);

		$this->sendJsonResponse($result);
	}
}