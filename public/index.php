<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../autoloader.php';

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
  View::render('accueil', [
    'publications' => Publication::findActive(4),
    'departements' => Catalogue::departements(),
    'stats'        => Catalogue::stats(),
    'annee'        => Catalogue::anneeCourante(),
  ]);
});

$router->get('/{name}/{id}', function($name, $id) {
  echo "hello $name of id $id";
}, [fn() => Auth::requireRole("admin", 'prof')]);

$router->run($_SERVER["REQUEST_METHOD"], $_SERVER['REQUEST_URI']);

