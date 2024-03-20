<?php

namespace general;

class Validator
{
    // Проверка длины строки
    public static function validateLength($string, $min, $max): bool
    {
        $length = strlen($string);
        return $length >= $min && $length <= $max;
    }

    // Валидация электронной почты
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Валидация номера телефона
    public static function validatePhoneNumber($phoneNumber) {
        $pattern = '/^\+7[0-9]{10}$/';
        return preg_match($pattern, $phoneNumber);
    }

    // Валидация пароля
    public static function validatePassword($password) {
        // Проверка на минимальную длину в 8-32 символов, наличие хотя бы одной цифры, одной заглавной буквы
        $pattern = '/^(?=.*\d)(?=.*[A-Z]).{8,32}$/';
        return preg_match($pattern, $password);
    }
}