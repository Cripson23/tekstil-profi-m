<?php

class UserActivityHistory extends Model
{
	const TYPES = [
	  	'REGISTER' => 1,
		'LOGIN_ATTEMPTS' => 2,
		'LOGIN' => 3,
		'LOGOUT' => 4,
		'UPDATE_PROFILE' => 5,
		'CHANGE_PASSWORD' => 6,
		'ORDER' => 7
	];

	const WARNING_TYPES = [
		self::TYPES['LOGIN_ATTEMPTS'],
		self::TYPES['CHANGE_PASSWORD'],
	];

	protected static ?string $table = 'user_activity_histories';

	/**
	 * @param int $type
	 * @param string|null $additionalText
	 * @return string
	 */
	public static function getActivityNameByType(int $type, ?string $additionalText): string
	{
		$additionalText = $additionalText ?? 'Неизвестно';

		switch ($type) {
			case self::TYPES['REGISTER']:
				return "Регистрация с E-mail: $additionalText";
			case self::TYPES['LOGIN_ATTEMPTS']:
				return "3 попытки входа с неверным паролем";
			case self::TYPES['LOGIN']:
				return "Успешная авторизация";
			case self::TYPES['LOGOUT']:
				return "Выход из системы";
			case self::TYPES['UPDATE_PROFILE']:
				return "Обновление данных профиля";
			case self::TYPES['CHANGE_PASSWORD']:
				return "Изменение пароля";
			case self::TYPES['ORDER']:
				return "Оформление заказа #$additionalText";
			default:
				return 'Неизвестно';
		}
	}

    /**
     * @return array
     */
    public static function getTypesList(): array
    {
        return [
            self::TYPES['REGISTER'] => 'Регистрация',
            self::TYPES['LOGIN_ATTEMPTS'] => 'Попытка входа',
            self::TYPES['LOGIN'] => 'Авторизация',
            self::TYPES['LOGOUT'] => 'Выход',
            self::TYPES['UPDATE_PROFILE'] => 'Обновление профиля',
            self::TYPES['CHANGE_PASSWORD'] => 'Изменение пароля',
            self::TYPES['ORDER'] => 'Заказ',
        ];
    }
}