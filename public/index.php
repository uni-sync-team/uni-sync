<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Session;
use App\Core\Router;
use App\Core\View;
use App\Middleware\Auth;

Session::start();

$router = new Router();

// One landing page per role (to be replaced by real dashboards)
foreach (['etudiant' => 'Étudiant', 'prof' => 'Enseignant'] as $role => $label) {
  $router->get("/$role", function () use ($label) {
    View::render('espace', ['label' => $label]);
  }, [fn() => Auth::requireRole($role)]);
}

// Routes
$adminRouter = require '../src/Routes/AdminRouter.php';
$authRouter = require '../src/Routes/AuthRouter.php';
$indexRouter = require '../src/Routes/IndexRouter.php';

$router->addRouter($adminRouter);
$router->addRouter($authRouter);
$router->addRouter($indexRouter);

$router->run();
