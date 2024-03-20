<?php

namespace services;

use Exception;
use general\Logger;
use UserActivityHistory;
use validators\UserValidator;
use User;

class ProfileService
{
    /**
     * @param array $user
     * @param array $updateData
     * @return array
     */
	public function update(array $user, array $updateData): array
	{
		$validateErrors = UserValidator::validateProfile($updateData, $user['email']);
		if (count($validateErrors) > 0) {
			return [
			  'success' => false,
			  'errors' => $validateErrors,
			  'status' => 422
			];
		}

		$updateStatus = true;

		try {
			if (!User::update($user['id'], $updateData)) {
				$updateStatus = false;
			}
		} catch (Exception $ex) {
			$updateStatus = false;
			Logger::logMessage('Ошибка при сохранении пользователя: ' . $ex);
		}

		if (!$updateStatus) {
			return [
			  'success' => false,
			  'error' => 'Произошла ошибка при обновлении профиля! Попробуйте ещё раз'
			];
		}

		ActivityHistoriesService::create($user['id'], UserActivityHistory::TYPES['UPDATE_PROFILE']);

		return [
		  'success' => true,
		  'message' => 'Данные вашего профиля успешно изменены',
		];
	}

    /**
     * @param array $user
     * @param array $changePasswordData
     * @return array
	 */
    public function changePassword(array $user, array $changePasswordData): array
	{
        $validateErrors = UserValidator::validateChangePassword($user['password'], $changePasswordData);
        if (count($validateErrors) > 0) {
            return [
                'success' => false,
                'errors' => $validateErrors,
                'status' => 422
            ];
        }

        $newPasswordHashed = password_hash($changePasswordData['new_password'], PASSWORD_DEFAULT);
        $updateStatus = true;

        try {
            if (!User::update($user['id'], ['password' => $newPasswordHashed])) {
                $updateStatus = false;
            }
        } catch (Exception $ex) {
            $updateStatus = false;
            Logger::logMessage('Ошибка при изменении пароля: ' . $ex);
        }

        if (!$updateStatus) {
            return [
                'success' => false,
                'error' => 'Произошла ошибка при изменении пароля! Попробуйте ещё раз'
            ];
        }

		ActivityHistoriesService::create($user['id'], UserActivityHistory::TYPES['CHANGE_PASSWORD']);

        return [
            'success' => true,
            'message' => 'Ваш пароль успешно изменён',
        ];
    }
}