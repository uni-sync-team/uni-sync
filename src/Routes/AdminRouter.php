<?php

namespace App\Routes;

use App\Core\Router;
use App\Controllers\AdminController;
use App\Controllers\AdminHistoriqueController;
use App\Controllers\AdminNotificationController;
use App\Controllers\AdminUserController;
use App\Controllers\AdminStudentController;
use App\Controllers\AdminDepartementController;
use App\Controllers\AdminEnseignantController;
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

// admin - history / nofification
$adminRouter->get('/admin/historique',                     [AdminHistoriqueController::class, 'index'], $admin);
$adminRouter->get('/admin/notifications',                  [AdminNotificationController::class, 'index'],   $admin);
$adminRouter->get('/admin/notifications/nouveau',          [AdminNotificationController::class, 'create'],  $admin);
$adminRouter->post('/admin/notifications',                 [AdminNotificationController::class, 'store'],   $adminPost);
$adminRouter->post('/admin/notifications/{id}/supprimer',  [AdminNotificationController::class, 'destroy'], $adminPost);

// admin - departement
$adminRouter->get('/admin/departements',                [AdminDepartementController::class, 'index'],  $admin);
$adminRouter->get('/admin/departements/nouveau',        [AdminDepartementController::class, 'create'], $admin);
$adminRouter->post('/admin/departements',               [AdminDepartementController::class, 'store'],  $adminPost);
$adminRouter->get('/admin/departements/{id}/modifier',  [AdminDepartementController::class, 'edit'],   $admin);
$adminRouter->post('/admin/departements/{id}',          [AdminDepartementController::class, 'update'], $adminPost);

// admin - Enseignant
$adminRouter->get('/admin/enseignants',                [AdminEnseignantController::class, 'index'],  $admin);
$adminRouter->get('/admin/enseignants/nouveau',        [AdminEnseignantController::class, 'create'], $admin);
$adminRouter->post('/admin/enseignants',               [AdminEnseignantController::class, 'store'],  $adminPost);
$adminRouter->get('/admin/enseignants/{id}/modifier',  [AdminEnseignantController::class, 'edit'],   $admin);
$adminRouter->post('/admin/enseignants/{id}',          [AdminEnseignantController::class, 'update'], $adminPost);

return $adminRouter;
