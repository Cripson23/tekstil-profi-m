<?php

namespace general;

class Router
{
    protected array $routes = [];

    /**
     * Добавляет маршрут в объект
     *
     * @param $uri
     * @param $controller
     * @param $method
     * @param string $methodType
     * @return void
     */
    public function add($uri, $controller, $method, string $methodType = 'GET')
    {
        $this->routes[$uri] = [
            'controller' => $controller,
            'method' => $method,
            'methodType' => strtoupper($methodType)
        ];
    }

    /**
     * Вызывает метод контроллера в зависимости от маршрута
     *
     * @param $requestUri
     * @return void
     */
    public function dispatch($requestUri)
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $pattern => $route) {
            if (preg_match($pattern, $requestUri, $params) && $requestMethod == $route['methodType']) {
                array_shift($params); // Удаляем полное совпадение из результатов

                $controllerName = $route['controller'];
                $methodName = $route['method'];

                $controller = new $controllerName();
                call_user_func_array([$controller, $methodName], $params);
                return;
            }
        }
        // Если маршрут не найден
		include 'views/common/404.php';
    }
}