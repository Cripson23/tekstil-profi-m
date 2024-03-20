<?php

namespace validators;

use general\Validator;

class OrderValidator
{
	/**
	 * @param array $curtainsData
	 * @return array
	 */
	public static function validateCurtainsData(array $curtainsData): array
	{
		$errors = [];

		// authStatus
		if (!isset($curtainsData['authStatus'])) {
			$errors['authStatus'][] = 'Ошибка проверки авторизации при отправке';
		}

		$authStatus = boolval($curtainsData['authStatus']);

		if (!$authStatus) {
			// name
			if (empty($curtainsData['curtains_name'])) {
				$errors['curtains-name'][] = 'Необходимо заполнить ФИО';
			} else if (!Validator::validateLength($curtainsData['curtains_name'], 5, 128)) {
				$errors['curtains-name'][] = 'Длина ФИО должна быть от 5 до 128 символов';
			}

			// email
			if (empty($curtainsData['curtains_email'])) {
				$errors['curtains-email'][] = 'Необходимо заполнить Email';
			} else if (!Validator::validateLength($curtainsData['curtains_email'], 6, 254)) {
				$errors['curtains-email'][] = 'Длина Email должна быть от 6 до 254 символов';
			} else if (!Validator::validateEmail($curtainsData['curtains_email'])) {
				$errors['curtains-email'][] = 'Введён некорректный Email';
			}

			// phone
			if (empty($curtainsData['curtains_phone'])) {
				$errors['curtains-phone'][] = 'Необходимо заполнить Номер телефона';
			} else if (!Validator::validatePhoneNumber($curtainsData['curtains_phone'])) {
				$errors['curtains-phone'][] = 'Введён некорректный Номер телефона';
			}
		}

		if (empty($curtainsData['curtains_wish'])) {
			$errors['curtains-wish'][] = 'Необходимо указать Пожелания к пошиву';
		}

		return $errors;
	}
}