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
use App\Controllers\AdminSalleController;
use App\Controllers\AdminAnneeController;
use App\Controllers\AdminFiliereController;
use App\Controllers\AdminFormationController;
use App\Controllers\AdminSeanceController;
use App\Controllers\AdminExamenController;
use App\Controllers\AdminResultatController;
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

// admin - salle
$adminRouter->get('/admin/salles',                [AdminSalleController::class, 'index'],  $admin);
$adminRouter->get('/admin/salles/nouveau',        [AdminSalleController::class, 'create'], $admin);
$adminRouter->post('/admin/salles',               [AdminSalleController::class, 'store'],  $adminPost);
$adminRouter->get('/admin/salles/{id}/modifier',  [AdminSalleController::class, 'edit'],   $admin);
$adminRouter->post('/admin/salles/{id}',          [AdminSalleController::class, 'update'], $adminPost);

// admin - annee universitaire
$adminRouter->get('/admin/annees',                [AdminAnneeController::class, 'index'],  $admin);
$adminRouter->get('/admin/annees/nouveau',        [AdminAnneeController::class, 'create'], $admin);
$adminRouter->post('/admin/annees',               [AdminAnneeController::class, 'store'],  $adminPost);
$adminRouter->get('/admin/annees/{id}/modifier',  [AdminAnneeController::class, 'edit'],   $admin);
$adminRouter->post('/admin/annees/{id}',          [AdminAnneeController::class, 'update'], $adminPost);

// admin - filiere
$adminRouter->get('/admin/filieres',                [AdminFiliereController::class, 'index'],  $admin);
$adminRouter->get('/admin/filieres/nouveau',        [AdminFiliereController::class, 'create'], $admin);
$adminRouter->post('/admin/filieres',               [AdminFiliereController::class, 'store'],  $adminPost);
$adminRouter->get('/admin/filieres/{id}/modifier',  [AdminFiliereController::class, 'edit'],   $admin);
$adminRouter->post('/admin/filieres/{id}',          [AdminFiliereController::class, 'update'], $adminPost);

// admin - formation 
$adminRouter->get('/admin/formations',                  [AdminFormationController::class, 'index'],         $admin);
$adminRouter->get('/admin/formations/nouveau',          [AdminFormationController::class, 'create'],        $admin);
$adminRouter->post('/admin/formations',                 [AdminFormationController::class, 'store'],         $adminPost);
$adminRouter->get('/admin/formations/{id}/modifier',    [AdminFormationController::class, 'edit'],          $admin);
$adminRouter->post('/admin/formations/{id}',            [AdminFormationController::class, 'update'],        $adminPost);
$adminRouter->post('/admin/formations/{id}/semestres',  [AdminFormationController::class, 'storeSemestre'], $adminPost);

// admin - seance
$adminRouter->get('/admin/seances',                 [AdminSeanceController::class, 'index'],   $admin);
$adminRouter->get('/admin/seances/nouveau',         [AdminSeanceController::class, 'create'],  $admin);
$adminRouter->post('/admin/seances',                [AdminSeanceController::class, 'store'],   $adminPost);
$adminRouter->get('/admin/seances/{id}/modifier',   [AdminSeanceController::class, 'edit'],    $admin);
$adminRouter->post('/admin/seances/{id}',           [AdminSeanceController::class, 'update'],  $adminPost);
$adminRouter->post('/admin/seances/{id}/supprimer', [AdminSeanceController::class, 'destroy'], $adminPost);

// admin - examen
$adminRouter->get('/admin/examens',                 [AdminExamenController::class, 'index'],   $admin);
$adminRouter->get('/admin/examens/nouveau',         [AdminExamenController::class, 'create'],  $admin);
$adminRouter->post('/admin/examens',                [AdminExamenController::class, 'store'],   $adminPost);
$adminRouter->get('/admin/examens/{id}/modifier',   [AdminExamenController::class, 'edit'],    $admin);
$adminRouter->post('/admin/examens/{id}',           [AdminExamenController::class, 'update'],  $adminPost);
$adminRouter->post('/admin/examens/{id}/supprimer', [AdminExamenController::class, 'destroy'], $adminPost);

// admin - resultats 
$adminRouter->get('/admin/resultats',                [AdminResultatController::class, 'index'],     $admin);
$adminRouter->post('/admin/resultats/calculer',      [AdminResultatController::class, 'calculer'],  $adminPost);
$adminRouter->get('/admin/resultats/examens/{id}',   [AdminResultatController::class, 'notes'],     $admin);
$adminRouter->post('/admin/resultats/examens/{id}',  [AdminResultatController::class, 'saveNotes'], $adminPost);

return $adminRouter;
