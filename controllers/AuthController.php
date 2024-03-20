<?php

namespace controllers;

use services\AuthService;

class AuthController extends BaseController
{
	private AuthService $authService;

	public function __construct()
	{
		parent::__construct();
		$this->authService = new AuthService();
	}

	/**
	 * @return void
	 */
	public function register(): void
	{
		$registerData = $this->postParams;
		$result = $this->authService->register($registerData);
		$this->sendJsonResponse($result);
    }

	/**
	 * @return void
	 */
	public function login(): void
	{
		$loginData = $this->postParams;
		$result = $this->authService->login($loginData);
		$this->sendJsonResponse($result);
	}

	/**
	 * @return void
	 */
	public function logout(): void
	{
		$this->authService->logout();
		$this->redirect('/');
	}
}