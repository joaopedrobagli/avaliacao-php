<?php

session_start();

// Autoload manual
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/../app/' . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($path)) {
        require $path;
    }
});

use App\Core\Router;
$router = new Router();

// Rotas serão registradas aqui conforme os controllers.
// Ex.: $router->add('auth/login', 'AuthController', 'login', 'GET');

$rota = $_GET['rota'] ?? 'auth/login';
$metodo = $_SERVER['REQUEST_METHOD'];

$router->dispatch($rota, $metodo);
