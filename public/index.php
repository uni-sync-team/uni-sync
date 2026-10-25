<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

spl_autoload_register(function (string $class): void {
  $base = __DIR__ . '/';
  $base = str_replace("public", "src", $base);

  foreach (['Core', 'Controllers', 'Models', 'Services', 'Middleware'] as $dir) {
    $file = $base . $dir . '/' . $class . '.php';
    if (is_file($file)) {
        require $file;
        return;
    }
  }
});

Session::start();
Database::connect();

$router = new Router();

$router->get('/health', function() {
  echo "App is Up";
});

$router->get('/database', function() {
  Database::isUp();
});

$router->get('/', function() {
  echo "hello You";
});

$router->get('/{name}/{id}', function($name, $id) {
  echo "hello $name of id $id";
}, [fn() => Auth::requireRole("admin", 'prof')]);

$router->run($_SERVER["REQUEST_METHOD"], $_SERVER['REQUEST_URI']);

