<?php

namespace App\Routes;

use App\Core\Router;
use App\Controllers\AdminController;
use App\Controllers\AdminUserController;
use App\Controllers\AdminStudentController;
use App\Middleware\Auth;
use App\Middleware\CsrfGuard;

$adminRouter = Router::subRouter();

// ----- Administration -----
$admin     = [fn() => Auth::requireRole('admin')];
$adminPost = [fn() => Auth::requireRole('admin'), fn() => CsrfGuard::verify()];

// admin
$adminRouter->get('/admin',                             [AdminController::class, 'dashboard'], $admin);

// admin - user
$adminRouter->get('/admin/utilisateurs',                [AdminUserController::class, 'index'],  $admin);
$adminRouter->get('/admin/utilisateurs/nouveau',        [AdminUserController::class, 'create'], $admin);
$adminRouter->post('/admin/utilisateurs',               [AdminUserController::class, 'store'],  $adminPost);
$adminRouter->get('/admin/utilisateurs/{id}/modifier',  [AdminUserController::class, 'edit'],   $admin);
$adminRouter->post('/admin/utilisateurs/{id}',          [AdminUserController::class, 'update'], $adminPost);

// admin - etudiant
$adminRouter->get('/admin/etudiants',                [AdminStudentController::class, 'index'],  $admin);
$adminRouter->get('/admin/etudiants/nouveau',        [AdminStudentController::class, 'create'], $admin);
$adminRouter->post('/admin/etudiants',               [AdminStudentController::class, 'store'],  $adminPost);
$adminRouter->get('/admin/etudiants/{id}/modifier',  [AdminStudentController::class, 'edit'],   $admin);
$adminRouter->post('/admin/etudiants/{id}',          [AdminStudentController::class, 'update'], $adminPost);

return $adminRouter;
