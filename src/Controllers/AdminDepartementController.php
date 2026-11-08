<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Models\Departement;
use App\Services\Audit;

class AdminDepartementController
{
  public static function index(): void
  {
    $q = trim((string) ($_GET['q'] ?? ''));

    AdminController::render('admin/departements/index', [
      'active'       => 'departements',
      'pageTitle'    => 'Départements',
      'departements' => Departement::findAll($q),
      'q'            => $q
    ]);
  }

  public static function create(): void
  {
    self::form(null, ['nom' => '', 'description' => ''], []);
  }

  public static function store(): void
  {
    [$errors, $d] = self::validate();

    if (!$errors) {
      try {
        Departement::create($d);
      } catch (\PDOException) {
        $errors['general'] = "Impossible d'ajouter le département.";
      }
    }

    if ($errors) {
      self::form(null, $d, $errors);
      return;
    }

    Audit::log("Création du département {$d['nom']}");
    Flash::set('success', "Département « {$d['nom']} » ajouté.");
    self::redirect();
  }

  public static function edit(string $id): void
  {
    $dep = Departement::findById((int) $id);
    if ($dep === null) {
      self::notFound();
      return;
    }

    self::form($dep, [
      'idDepartement' => $dep['idDepartement'],
      'nom'           => $dep['nom'],
      'description'   => $dep['description'],
    ], []);
  }

  public static function update(string $id): void
  {
    $dep = Departement::findById((int) $id);
    if ($dep === null) {
      self::notFound();
      return;
    }

    [$errors, $d] = self::validate();

    if (!$errors) {
      try {
        // the return value only says "something changed", so don't use it as success
        Departement::update($dep['idDepartement'], $d);
      } catch (\PDOException) {
        $errors['general'] = 'Impossible de modifier le département.';
      }
    }

    if ($errors) {
      $d['idDepartement'] = $dep['idDepartement'];
      self::form($dep, $d, $errors);
      return;
    }

    Audit::log("Modification du département {$dep['nom']}");
    Flash::set('success', "Département « {$d['nom']} » mis à jour.");
    self::redirect();
  }

  /** @return array{0: array<string,string>, 1: array} errors and cleaned data */
  private static function validate(): array
  {
    $errors      = [];
    $nom         = trim((string) ($_POST['nom'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));

    if ($nom === '' || mb_strlen($nom) > 100) {
      $errors['nom'] = 'Nom requis (100 caractères maximum).';
    }
    if (mb_strlen($description) > 255) {
      $errors['description'] = '255 caractères maximum.';
    }

    return [$errors, [
      'nom'         => $nom,
      'description' => $description !== '' ? $description : null,
    ]];
  }

  private static function form(?array $dep, array $data, array $errors): void
  {
    AdminController::render('admin/departements/form', [
      'active'      => 'departements',
      'pageTitle'   => $dep === null ? 'Nouveau département' : 'Modifier ' . $data['nom'],
      'departement' => $dep,
      'data'        => $data,
      'errors'      => $errors,
    ]);
  }

  private static function redirect(): never
  {
    header('Location: /admin/departements');
    exit;
  }

  private static function notFound(): void
  {
    http_response_code(404);
    echo '404 - Département introuvable';
  }
}
