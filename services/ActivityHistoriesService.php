<?php

namespace services;

use Exception;
use general\Logger;
use helpers\DateTimeHelper;
use helpers\RequestSender;
use UserActivityHistory;
use validators\ListValidator;

class ActivityHistoriesService
{
    const DEFAULT_PROFILE_PER_PAGE = 5;

	/**
	 * @return array[]
	 */
	private static function getSettingListFields(): array
	{
		return [
			'FILTER' => [
				'ALL' => ['type'],
				'INT' => ['type'],
				'ADDITIONAL' => [
					/*'user_id' => function($value) {
						return (bool) User::read('id', $value);
					},*/
					'type' => function ($value) {
						return in_array($value, array_values(UserActivityHistory::TYPES));
					}
				]
			],
            'PAGINATION' => [
                'RANGE' => [5, 10, 15]
            ]
		];
	}

	/**
	 * @param int $userId
	 * @param int $type
	 * @param string|null $additionalText
	 * @return void
	 */
	public static function create(int $userId, int $type, ?string $additionalText = null)
	{
		$userIP = $_SERVER['REMOTE_ADDR'];

		$historyData = [
			'user_id' => $userId,
			'type' => $type,
			'additional_text' => $additionalText,
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'ip' => $userIP,
			'country' => null,
			'region' => null,
			'city' => null
		];

		$geoInfo = self::getGeoInfo($userIP);
		if ($geoInfo === null) {
			Logger::logMessage("Не удалось получить ГЕО данные для пользователя с ID: $userId");
		} else {
			$historyData['country'] = $geoInfo['country'];
			$historyData['region'] = $geoInfo['regionName'];
			$historyData['city'] = $geoInfo['city'];
		}

		if (!UserActivityHistory::create($historyData)) {
			Logger::logMessage("Не удалось сохранить историю активности для пользователя с ID: $userId");
		}
	}

	/**
	 * Валидируем и получаем список активности
	 *
	 * @param int $userId
	 * @param array $listParams
	 * @return void
	 * @throws Exception
	 */
	public function getUserActivityHistoryList(int $userId, array $listParams): array
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

		$activityData = UserActivityHistory::findAll(
			array_merge($filters, ['user_id' => $userId]),
			'created_at',
			'DESC',
			$pagination['page'] ?? 1,
			$pagination['per_page'] ?? self::DEFAULT_PROFILE_PER_PAGE
		);

		$preparedActivityData = $this->prepareActivityHistoriesData($activityData);

		return [
			'success' => true,
			'data' => $preparedActivityData
		];
	}

	/**
	 * Предобработка информации об активностях
	 *
	 * @param array $activityData
	 * @return array
	 * @throws Exception
	 */
	private function prepareActivityHistoriesData(array $activityData): array
	{
		$result = ['items' => [], 'totalCount' => $activityData['totalCount']];

		foreach ($activityData['items'] as $activity) {
			$activity['text'] = UserActivityHistory::getActivityNameByType($activity['type'], $activity['additional_text']);
			$activity['created_at'] = DateTimeHelper::convertFormatDateTime($activity['created_at'], 'd.m.Y H:i:s');
			$activity['warning'] = in_array($activity['type'], UserActivityHistory::WARNING_TYPES);
			$result['items'][] = $activity;
		}

		return $result;
	}

	/**
	 * Получаем ГЕО информацию о пользователе по IP
	 *
	 * @param string $userIP
	 * @return array|null
	 */
	private static function getGeoInfo(string $userIP): ?array
	{
		try {
			$result = RequestSender::sendHttpRequest("http://ip-api.com/json/$userIP?lang=ru");
		} catch (Exception $exception) {
			return null;
		}

		if ($result['error'] || $result['response']['status'] === 'fail') {
			return null;
		}

		return $result['response'];
	}
}