<?php

session_start();

// Autoload manual (sem Composer): converte namespace App\Xxx\Yyy
// em app/Xxx/Yyy.php
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

// Formato: $router->add('rota', 'NomeDoController', 'metodo', 'METODO_HTTP');
// GET = mostrar uma tela | POST = processar um formulário
$router->add('auth/login', 'AuthController', 'showLogin', 'GET');
$router->add('auth/login', 'AuthController', 'login', 'POST');
$router->add('auth/register', 'AuthController', 'showRegister', 'GET');
$router->add('auth/register', 'AuthController', 'register', 'POST');
$router->add('auth/logout', 'AuthController', 'logout', 'GET');

$router->add('dashboard/index', 'DashboardController', 'index', 'GET');
$router->add('dashboard/finalizar', 'DashboardController', 'finalizar', 'POST');
$router->add('dashboard/excluir', 'DashboardController', 'excluir', 'POST');

$router->add('service/create', 'ServiceController', 'showCreate', 'GET');
$router->add('service/create', 'ServiceController', 'store', 'POST');
$router->add('service/edit', 'ServiceController', 'showEdit', 'GET');
$router->add('service/edit', 'ServiceController', 'update', 'POST');

$rota = $_GET['rota'] ?? 'auth/login';
$metodo = $_SERVER['REQUEST_METHOD'];

$router->dispatch($rota, $metodo);