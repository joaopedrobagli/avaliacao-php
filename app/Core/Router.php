<?php

namespace App\Core;

/**
 * Router mapeia "controller/action" vindo
 * da query string para uma chamada.
 */
class Router
{
    private array $routes = [];

    public function add(string $route, string $controller, string $action, string $method = 'GET'): void
    {
        $this->routes[$method][$route] = [$controller, $action];
    }

    public function dispatch(string $route, string $method): void
    {
        $method = strtoupper($method);

        if (!isset($this->routes[$method][$route])) {
            http_response_code(404);
            echo 'Rota não encontrada: ' . htmlspecialchars($route);
            return;
        }

        [$controllerClass, $action] = $this->routes[$method][$route];
        $fullClass = 'App\\Controllers\\' . $controllerClass;

        if (!class_exists($fullClass)) {
            http_response_code(500);
            echo 'Controller não encontrado: ' . htmlspecialchars($fullClass);
            return;
        }

        $controller = new $fullClass();

        if (!method_exists($controller, $action)) {
            http_response_code(500);
            echo 'Ação não encontrada: ' . htmlspecialchars($action);
            return;
        }

        $controller->$action();
    }
}
