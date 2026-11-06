<?php

namespace App\Routes;

use App\Core\Router;
use App\Controllers\AuthController;

$authRouter = Router::subRouter();

$authRouter->get('/login',   [AuthController::class, 'showLogin']);
$authRouter->post('/login',  [AuthController::class, 'login']);
$authRouter->post('/logout', [AuthController::class, 'logout']);

return $authRouter;
