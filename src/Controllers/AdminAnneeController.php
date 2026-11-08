<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Models\AnneeUniversitaire;
use App\Services\Audit;

class AdminAnneeController
{
  public static function index(): void
  {
    AdminController::render('admin/annees/index', [
      'active'    => 'annee',
      'pageTitle' => 'Années universitaires',
      'annees'    => AnneeUniversitaire::findAll(),
    ]);
  }

  public static function create(): void
  {
    self::form(null, ['libelle' => '', 'dateDebut' => '', 'dateFin' => ''], []);
  }

  public static function store(): void
  {
    [$errors, $d] = self::validate();

    if (!$errors) {
      try {
        AnneeUniversitaire::create($d);
      } catch (\PDOException) {
        $errors['general'] = "Impossible d'ajouter l'année universitaire.";
      }
    }

    if ($errors) {
      self::form(null, $d, $errors);
      return;
    }

    Audit::log("Création de l'année universitaire {$d['libelle']}");
    Flash::set('success', "Année « {$d['libelle']} » ajoutée.");
    self::redirect();
  }

  public static function edit(string $id): void
  {
    $annee = AnneeUniversitaire::findById((int) $id);
    if ($annee === null) {
      self::notFound();
      return;
    }

    self::form($annee, [
      'idAnnee'   => $annee['idAnnee'],
      'libelle'   => $annee['libelle'],
      'dateDebut' => $annee['dateDebut'],
      'dateFin'   => $annee['dateFin'],
    ], []);
  }

  public static function update(string $id): void
  {
    $annee = AnneeUniversitaire::findById((int) $id);
    if ($annee === null) {
      self::notFound();
      return;
    }

    [$errors, $d] = self::validate();

    if (!$errors) {
      try {
        AnneeUniversitaire::update($annee['idAnnee'], $d);   // false = "nothing changed", not an error
      } catch (\PDOException) {
        $errors['general'] = "Impossible de modifier l'année universitaire.";
      }
    }

    if ($errors) {
      $d['idAnnee'] = $annee['idAnnee'];
      self::form($annee, $d, $errors);
      return;
    }

    Audit::log("Modification de l'année universitaire {$annee['libelle']}");
    Flash::set('success', "Année « {$d['libelle']} » mise à jour.");
    self::redirect();
  }

  /** @return array{0: array<string,string>, 1: array} errors and cleaned data */
  private static function validate(): array
  {
    $errors  = [];
    $libelle = trim((string) ($_POST['libelle'] ?? ''));
    $debut   = trim((string) ($_POST['dateDebut'] ?? ''));
    $fin     = trim((string) ($_POST['dateFin'] ?? ''));

    if ($libelle === '' || mb_strlen($libelle) > 50) {
      $errors['libelle'] = 'Libellé requis (50 caractères maximum), par exemple 2025-2026.';
    }

    $okDebut = self::validDate($debut);
    $okFin   = self::validDate($fin);

    if (!$okDebut) {
      $errors['dateDebut'] = 'Date de début invalide.';
    }
    if (!$okFin) {
      $errors['dateFin'] = 'Date de fin invalide.';
    }
    if ($okDebut && $okFin && $fin <= $debut) {   // Y-m-d strings sort chronologically
      $errors['dateFin'] = 'La date de fin doit être après la date de début.';
    }

    return [$errors, ['libelle' => $libelle, 'dateDebut' => $debut, 'dateFin' => $fin]];
  }

  private static function validDate(string $s): bool
  {
    $d = \DateTime::createFromFormat('!Y-m-d', $s);
    return $d !== false && $d->format('Y-m-d') === $s;
  }

  private static function form(?array $annee, array $data, array $errors): void
  {
    AdminController::render('admin/annees/form', [
      'active'    => 'annee',
      'pageTitle' => $annee === null ? 'Nouvelle année universitaire' : 'Modifier ' . $data['libelle'],
      'annee'     => $annee,
      'data'      => $data,
      'errors'    => $errors,
    ]);
  }

  private static function redirect(): never
  {
    header('Location: /admin/annees');
    exit;
  }

  private static function notFound(): void
  {
    http_response_code(404);
    echo '404 - Année universitaire introuvable';
  }
}
