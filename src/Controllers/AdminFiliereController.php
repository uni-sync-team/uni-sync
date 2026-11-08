<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Models\Departement;
use App\Models\Filiere;
use App\Services\Audit;

class AdminFiliereController
{
  public static function index(): void
  {
    AdminController::render('admin/filieres/index', [
      'active'    => 'formations',
      'pageTitle' => 'Filières',
      'filieres'  => (new Filiere())->findAllWithDepartement(),
    ]);
  }

  public static function create(): void
  {
    self::form(null, ['idDepartement' => '', 'nom' => '', 'description' => ''], []);
  }

  public static function store(): void
  {
    [$errors, $d] = self::validate();

    if (!$errors) {
      try {
        (new Filiere())->create($d);
      } catch (\PDOException) {
        $errors['general'] = "Impossible d'ajouter la filière.";
      }
    }

    if ($errors) {
      self::form(null, $d, $errors);
      return;
    }

    Audit::log("Création de la filière {$d['nom']}");
    Flash::set('success', "Filière « {$d['nom']} » ajoutée.");
    self::redirect();
  }

  public static function edit(string $id): void
  {
    $filiere = (new Filiere())->find((int) $id);
    if ($filiere === null) {
      self::notFound();
      return;
    }

    self::form($filiere, [
      'idFiliere'     => $filiere['idFiliere'],
      'idDepartement' => $filiere['idDepartement'],
      'nom'           => $filiere['nom'],
      'description'   => $filiere['description'],
    ], []);
  }

  public static function update(string $id): void
  {
    $model   = new Filiere();
    $filiere = $model->find((int) $id);
    if ($filiere === null) {
      self::notFound();
      return;
    }

    [$errors, $d] = self::validate();

    if (!$errors) {
      try {
        $model->update((int) $filiere['idFiliere'], $d);   // false = "nothing changed", not an error
      } catch (\PDOException) {
        $errors['general'] = 'Impossible de modifier la filière.';
      }
    }

    if ($errors) {
      $d['idFiliere'] = $filiere['idFiliere'];
      self::form($filiere, $d, $errors);
      return;
    }

    Audit::log("Modification de la filière {$filiere['nom']}");
    Flash::set('success', "Filière « {$d['nom']} » mise à jour.");
    self::redirect();
  }

  /** @return array{0: array<string,string>, 1: array} errors and cleaned data */
  private static function validate(): array
  {
    $errors = [];
    $idDep  = (int) filter_var($_POST['idDepartement'] ?? 0, FILTER_VALIDATE_INT);
    $nom    = trim((string) ($_POST['nom'] ?? ''));
    $desc   = trim((string) ($_POST['description'] ?? ''));

    if ($idDep <= 0 || !Departement::findById($idDep)) {
      $errors['idDepartement'] = 'Choisissez un département valide.';
    }
    if ($nom === '' || mb_strlen($nom) > 100) {
      $errors['nom'] = 'Nom requis (100 caractères maximum).';
    }
    if (mb_strlen($desc) > 255) {
      $errors['description'] = 'Description trop longue (255 caractères maximum).';
    }

    return [$errors, [
      'idDepartement' => $idDep,
      'nom'           => $nom,
      'description'   => $desc !== '' ? $desc : null,
    ]];
  }

  private static function form(?array $filiere, array $data, array $errors): void
  {
    AdminController::render('admin/filieres/form', [
      'active'       => 'formations',
      'pageTitle'    => $filiere === null ? 'Nouvelle filière' : 'Modifier ' . $data['nom'],
      'filiere'      => $filiere,
      'data'         => $data,
      'errors'       => $errors,
      'departements' => Departement::findAll(),
    ]);
  }

  private static function redirect(): never
  {
    header('Location: /admin/filieres');
    exit;
  }

  private static function notFound(): void
  {
    http_response_code(404);
    echo '404 - Filière introuvable';
  }
}
