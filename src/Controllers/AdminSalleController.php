<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Models\Salle;
use App\Services\Audit;

class AdminSalleController
{
  public static function index(): void
  {
    AdminController::render('admin/salles/index', [
      'active'    => 'salles',
      'pageTitle' => 'Salles',
      'salles'    => Salle::findAll(),
    ]);
  }

  public static function create(): void
  {
    self::form(null, ['nom' => '', 'capacite' => '', 'typeSalle' => ''], []);
  }

  public static function store(): void
  {
    [$errors, $d] = self::validate();

    if (!$errors) {
      try {
        Salle::create($d);
      } catch (\PDOException) {
        $errors['general'] = "Impossible d'ajouter la salle.";
      }
    }

    if ($errors) {
      self::form(null, $d, $errors);
      return;
    }

    Audit::log("Création de la salle {$d['nom']}");
    Flash::set('success', "Salle « {$d['nom']} » ajoutée.");
    self::redirect();
  }

  public static function edit(string $id): void
  {
    $salle = Salle::findById((int) $id);
    if ($salle === null) {
      self::notFound();
      return;
    }

    self::form($salle, [
      'idSalle'   => $salle['idSalle'],
      'nom'       => $salle['nom'],
      'capacite'  => $salle['capacite'],
      'typeSalle' => $salle['typeSalle'],
    ], []);
  }

  public static function update(string $id): void
  {
    $salle = Salle::findById((int) $id);
    if ($salle === null) {
      self::notFound();
      return;
    }

    [$errors, $d] = self::validate();

    if (!$errors) {
      try {
        Salle::update($salle['idSalle'], $d);   // false = "nothing changed", not an error
      } catch (\PDOException) {
        $errors['general'] = 'Impossible de modifier la salle.';
      }
    }

    if ($errors) {
      $d['idSalle'] = $salle['idSalle'];
      self::form($salle, $d, $errors);
      return;
    }

    Audit::log("Modification de la salle {$salle['nom']}");
    Flash::set('success', "Salle « {$d['nom']} » mise à jour.");
    self::redirect();
  }

  /** @return array{0: array<string,string>, 1: array} errors and cleaned data */
  private static function validate(): array
  {
    $errors   = [];
    $nom      = trim((string) ($_POST['nom'] ?? ''));
    $capRaw   = trim((string) ($_POST['capacite'] ?? ''));
    $type     = trim((string) ($_POST['typeSalle'] ?? ''));

    if ($nom === '' || mb_strlen($nom) > 50) {
      $errors['nom'] = 'Nom requis (50 caractères maximum).';
    }

    $cap = filter_var($capRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2000]]);
    if ($cap === false) {
      $errors['capacite'] = 'Capacité invalide (nombre entier entre 1 et 2000).';
    }

    if ($type !== '' && !in_array($type, Salle::TYPES, true)) {
      $errors['typeSalle'] = 'Choisissez un type valide.';
    }

    return [$errors, [
      'nom'       => $nom,
      'capacite'  => $cap !== false ? $cap : $capRaw,   // keep what was typed so the form can show it again
      'typeSalle' => $type !== '' ? $type : null,
    ]];
  }

  private static function form(?array $salle, array $data, array $errors): void
  {
    AdminController::render('admin/salles/form', [
      'active'    => 'salles',
      'pageTitle' => $salle === null ? 'Nouvelle salle' : 'Modifier ' . $data['nom'],
      'salle'     => $salle,
      'data'      => $data,
      'errors'    => $errors,
    ]);
  }

  private static function redirect(): never
  {
    header('Location: /admin/salles');
    exit;
  }

  private static function notFound(): void
  {
    http_response_code(404);
    echo '404 - Salle introuvable';
  }
}
