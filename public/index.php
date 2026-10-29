<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AuthController;
use App\Core\Session;
use App\Core\Database;
use App\Core\Router;
use App\Core\View;
use App\Models\Publication;
use App\Models\Catalogue;
use App\Middleware\Auth;

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

$router->get('/login',   [AuthController::class, 'showLogin']);
$router->post('/login',  [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// One landing page per role (to be replaced by real dashboards)
foreach (['etudiant' => 'Étudiant', 'prof' => 'Enseignant', 'admin' => 'Administrateur'] as $role => $label) {
  $router->get("/$role", function () use ($label) {
    View::render('espace', ['label' => $label]);
  }, [fn() => Auth::requireRole($role)]);
}

$router->run($_SERVER["REQUEST_METHOD"], $_SERVER['REQUEST_URI']);
