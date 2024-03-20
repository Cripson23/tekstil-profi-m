<?php

namespace services;

use Exception;
use general\Logger;
use UserActivityHistory;
use validators\UserValidator;
use User;

class AuthService
{
    /**
     * @param array $registerData
     * @return array
     */
    public function register(array $registerData): array
    {
        $validateErrors = UserValidator::validateRegister($registerData);
        if (count($validateErrors) > 0) {
            return [
                'success' => false,
                'errors' => $validateErrors,
                'status' => 422
            ];
        }

        $registerData['password'] = password_hash($registerData['password'], PASSWORD_DEFAULT);
        unset($registerData['password_confirm']);

        $createStatus = true;

        try {
            if (!$userId = User::create($registerData)) {
                $createStatus = false;
            } else {
				ActivityHistoriesService::create($userId, UserActivityHistory::TYPES['REGISTER']);
			}
        } catch (Exception $ex) {
            $createStatus = false;
            Logger::logMessage('Ошибка при сохранении пользователя: ' . $ex);
        }

        if (!$createStatus) {
            return [
                'success' => false,
                'error' => 'Произошла ошибка во время регистрации! Попробуйте ещё раз'
            ];
        }

        return [
            'success' => true,
            'message' => 'Вы успешно зарегистрировались на сайте! Можете войти с вашими данными',
        ];
    }

    /**
     * @param array $loginData
     * @return array
     */
    public function login(array $loginData): array
    {
        $validateErrors = UserValidator::validateLogin($loginData);
        if (count($validateErrors) > 0) {
            return [
                'success' => false,
                'errors' => $validateErrors,
                'status' => 422
            ];
        }

        $user = User::read('username', $loginData['username']);
		if ($user['status'] == User::STATUSES['STATUS_BLOCK']) {
			return [
				'success' => false,
			  	'error' => 'К сожалению, ваш аккаунт заблокирован. Авторизация невозможна',
				'status' => 403
			];
		}

        // Установка пользовательских сессионных переменных
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

		ActivityHistoriesService::create($user['id'], UserActivityHistory::TYPES['LOGIN']);
		self::updateLoginAttempts($user['id'], true);

        return [
            'success' => true,
            'message' => 'Вы успешно авторизовались',
        ];
    }

	/**
	 * @return void
	 */
	public function logout()
	{
		if ($_SESSION['user_id'] != null) {
			ActivityHistoriesService::create($_SESSION['user_id'], UserActivityHistory::TYPES['LOGOUT']);
		}

		$_SESSION['user_id'] = null;
		$_SESSION['username'] = null;
	}

	/**
	 * @param int $userId
	 * @param bool $successAuth
	 * @return void
	 */
	public static function updateLoginAttempts(int $userId, bool $successAuth = false)
	{
		if ($successAuth) {
			$_SESSION['login_attempts'][$userId] = null;
			return;
		}

		if (!isset($_SESSION['login_attempts'][$userId])) {
			$_SESSION['login_attempts'][$userId] = 0;
		}

		$_SESSION['login_attempts'][$userId] += 1;

		if ($_SESSION['login_attempts'][$userId] >= 3) {
			$_SESSION['login_attempts'][$userId] = 0;
			ActivityHistoriesService::create($userId, UserActivityHistory::TYPES['LOGIN_ATTEMPTS']);
		}
	}
}