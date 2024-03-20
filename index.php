<?php

use general\Router;

// Регистрируем классы и зависимости
spl_autoload_register(function ($class_name) {
    $paths = ['.', 'general', 'general/PHPMailer/src', 'models', 'controllers', 'validators', 'services', 'helpers'];

    foreach ($paths as $path) {
        $class_name = str_replace('\\', '/', $class_name);
        $file = "{$path}/{$class_name}.php";

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

/**** Объявление маршрутов ****/

$router = new Router();

/** Main **/
// Домашняя страница
$router->add('/^\/?$/', \controllers\MainController::class, 'index');

/** Category */
// Получение списка категорий
$router->add('/^product\/category\/type\/list\/?$/', \controllers\MainController::class, 'productCategoryTypeList', 'POST');
// Страница категории
$router->add('/^product\/category\/(\d+)$/', \controllers\MainController::class, 'viewCategoryProducts');
// Товары категории
$router->add('/^product\/category\/(\d+)\/list?$/', \controllers\MainController::class, 'getCategoryProductsList', 'POST');
// Товар по id
$router->add('/^product\/(\d+)?$/', \controllers\MainController::class, 'getProduct');

/** Cart */
$router->add('/^cart\/data?$/', \controllers\CartController::class, 'getCartData');
$router->add('/^cart\/increase-quantity-item?$/', \controllers\CartController::class, 'increaseQuantityItem', 'POST');
$router->add('/^cart\/decrease-quantity-item?$/', \controllers\CartController::class, 'decreaseQuantityItem', 'POST');
$router->add('/^cart\/change-quantity-item?$/', \controllers\CartController::class, 'changeQuantityItem', 'POST');
$router->add('/^cart\/remove-item?$/', \controllers\CartController::class, 'removeItem', 'POST');

/** Order */
$router->add('/^order\/create?$/', \controllers\OrderController::class, 'create', 'POST');
$router->add('/^order\/curtains?$/', \controllers\OrderController::class, 'orderCurtains', 'POST');
$router->add('/^order\/list?$/', \controllers\OrderController::class, 'getOrdersList', 'POST');
$router->add('/^order\/(\d+)?$/', \controllers\OrderController::class, 'getOrder');

/** Auth **/
// Регистрация
$router->add('/^auth\/register\/?$/', \controllers\AuthController::class, 'register', 'POST');
// Авторизация
$router->add('/^auth\/login\/?$/', \controllers\AuthController::class, 'login', 'POST');
// Выход
$router->add('/^auth\/logout\/?$/', \controllers\AuthController::class, 'logout');

/** Account **/
// Страница личного кабинета
$router->add('/^account\/profile?$/', \controllers\AccountController::class, 'view');
// Редактирование профиля
$router->add('/^account\/profile\/update?$/', \controllers\AccountController::class, 'updateProfile', 'POST');
// Изменение пароля
$router->add('/^account\/profile\/change-password?$/', \controllers\AccountController::class, 'changePassword', 'POST');
// Получение списка активности пользователя
$router->add('/^account\/profile\/activity-history?$/', \controllers\AccountController::class, 'getUserActivityHistory', 'POST');

// Orders
$router->add('/^account\/orders?$/', \controllers\OrderController::class, 'index');
/************* *************/

// Получаем путь из запроса, убирая начальный и конечный слэш
$requestUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Отправляем запрос на обработку
$router->dispatch($requestUri);