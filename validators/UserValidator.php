<?php

namespace validators;

use general\Validator;
use services\AuthService;
use User;

class UserValidator
{
    /**
     * @param array $registerData
     * @return array
     */
    public static function validateRegister(array $registerData): array
    {
		$errors = [];

		// username
		if (empty($registerData['username'])) {
			$errors['username'][] = 'Необходимо заполнить Имя пользователя';
		} else if (!Validator::validateLength($registerData['username'], 2, 25)) {
			$errors['username'][] = 'Длина Имени пользователя должна быть от 2 до 25 символов';
		} else if (User::read('username', $registerData['username'])) {
			$errors['username'][] = 'Пользователь с таким Именем уже зарегистрирован';
		}

        return array_merge(
		  $errors,
		  self::validateProfile($registerData),
		  self::validatePassword(array_intersect_key($registerData, array_flip(['password', 'password_confirm'])))
		);
    }

    /**
     * @param array $profileData
     * @param string|null $userEmail
     * @return array
     */
	public static function validateProfile(array $profileData, string $userEmail = null): array
	{
		$errors = [];

		// name
		if (empty($profileData['name'])) {
			$errors['name'][] = 'Необходимо заполнить ФИО';
		} else if (!Validator::validateLength($profileData['name'], 5, 128)) {
			$errors['name'][] = 'Длина ФИО должна быть от 5 до 128 символов';
		}

		// email
		if (empty($profileData['email'])) {
			$errors['email'][] = 'Необходимо заполнить Email';
		} else if (!Validator::validateLength($profileData['email'], 6, 254)) {
			$errors['email'][] = 'Длина Email должна быть от 6 до 254 символов';
		} else if (!Validator::validateEmail($profileData['email'])) {
			$errors['email'][] = 'Введён некорректный Email';
		} else if (User::read('email', $profileData['email'])
            && ($userEmail != null && $userEmail != $profileData['email'])
        ) {
			$errors['email'][] = 'Пользователь с таким Email уже зарегистрирован';
		}

		// phone
		if (empty($profileData['phone'])) {
			$errors['phone'][] = 'Необходимо заполнить Номер телефона';
		} else if (!Validator::validatePhoneNumber($profileData['phone'])) {
			$errors['phone'][] = 'Введён некорректный Номер телефона';
		}

		// delivery address
		if (empty($profileData['delivery_address'])) {
			$errors['delivery_address'][] = 'Необходимо заполнить Адрес доставки';
		} else if (!Validator::validateLength($profileData['delivery_address'], 30, 256)) {
			$errors['delivery_address'][] = 'Длина Адреса доставки должна быть от 30 до 256 символов';
		}

		return $errors;
	}

    /**
     * @param array $passwordData
     * @return array
     */
	public static function validatePassword(array $passwordData): array
	{
		$errors = [];

		// password
		if (empty($passwordData['password'])) {
			$errors['password'][] = 'Необходимо заполнить Пароль';
		} else if (!Validator::validatePassword($passwordData['password'])) {
			$errors['password'][] = 'От 8 до 32 символов, хотя бы одна цифры и одна заглавная буква';
		}

		// confirm password
		if ($passwordData['password'] !== $passwordData['password_confirm']) {
			$errors['password_confirm'][] = 'Пароли не совпадают';
		}

		return $errors;
	}

    /**
     * @param string $passwordHashed
     * @param array $changePasswordData
     * @return array
     */
    public static function validateChangePassword(string $passwordHashed, array $changePasswordData): array
    {
        $errors = [];

        // password
        if (empty($changePasswordData['password'])) {
            $errors['password'][] = 'Необходимо заполнить Текущий пароль';
        } else if (!password_verify($changePasswordData['password'], $passwordHashed)) {
            $errors['password'][] = 'Неверный Текущий пароль';
        }

        // new password
        if (empty($changePasswordData['new_password'])) {
            $errors['new_password'][] = 'Необходимо заполнить Новый пароль';
        } else if (!Validator::validatePassword($changePasswordData['new_password'])) {
            $errors['new_password'][] = 'От 8 до 32 символов, хотя бы одна цифра и одна заглавная буква';
        }

        if ($changePasswordData['new_password'] !== $changePasswordData['new_password_confirm']) {
            $errors['new_password_confirm'][] = 'Пароли не совпадают';
        }

        return $errors;
    }

    /**
     * @param array $loginData
     * @return array
     */
    public static function validateLogin(array $loginData): array
    {
        $errors = [];

        if (empty($loginData['username']) || empty($loginData['password'])) {
            // username
            if (empty($loginData['username'])) {
                $errors['login_username'][] = 'Необходимо заполнить Имя пользователя';
            }

            // password
            if (empty($loginData['password'])) {
                $errors['login_password'][] = 'Необходимо заполнить Пароль';
            }
        } else {
            $user = User::read('username', $loginData['username']);

            if (!isset($user) || !password_verify($loginData['password'], $user['password']))
            {
				if (isset($user)) {
					AuthService::updateLoginAttempts($user['id']);
				}

                $errors['login_password'][] = 'Неверный логин или пароль';
            }
        }

        return $errors;
    }
}