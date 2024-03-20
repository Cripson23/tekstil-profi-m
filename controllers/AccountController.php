<?php

namespace controllers;

use Exception;
use helpers\DateTimeHelper;
use services\ActivityHistoriesService;
use services\ProfileService;

class AccountController extends BaseController
{
	protected string $viewsSectionName = 'account';

	private ProfileService $profileService;
	private ActivityHistoriesService $activityHistoriesService;

	public function __construct()
	{
		parent::__construct();
		$this->profileService = new ProfileService();
		$this->activityHistoriesService = new ActivityHistoriesService();
	}

	/**
	 * @throws Exception
	 */
	public function view()
	{
		$userData = $this->getAuthUser();

		$userData['created_at'] = DateTimeHelper::convertFormatDateTime($userData['created_at'], 'd.m.Y H:i');
		$userData['prepared_phone'] = preg_replace('/^(\+\d)(\d{3})(\d{3})(\d{2})(\d{2})$/', '$1 $2 $3 $4 $5', $userData['phone']);

		$this->render('profile', [
			'title' => $this->getTitle('Личный кабинет'),
			'user' => $userData,
            'activityTypesList' => \UserActivityHistory::getTypesList()
		]);
	}

	/**
	 * @return void
	 */
	public function updateProfile()
	{
		$userData = $this->getAuthUser();
		$result = $this->profileService->update($userData, $this->postParams);

		$this->sendJsonResponse($result);
	}

	/**
	 * @return void
	 */
	public function changePassword()
	{
		$userData = $this->getAuthUser();
		$result = $this->profileService->changePassword($userData, $this->postParams);

		$this->sendJsonResponse($result);
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	public function getUserActivityHistory()
	{
		$userData = $this->getAuthUser();

		$result = $this->activityHistoriesService->getUserActivityHistoryList($userData['id'], $this->postParams);

		$this->sendJsonResponse($result);
	}
}