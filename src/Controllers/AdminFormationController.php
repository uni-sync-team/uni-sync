<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Models\Filiere;
use App\Models\Formation;
use App\Models\Semestre;
use App\Services\Audit;

class AdminFormationController
{
  public static function index(): void
  {
    $idFiliere  = (int) filter_var($_GET['filiere'] ?? 0, FILTER_VALIDATE_INT);
    $formations = (new Formation())->findAllWithFiliere();

    if ($idFiliere > 0) {
      $formations = array_values(array_filter($formations, fn($f) => (int) $f['idFiliere'] === $idFiliere));
    }

    AdminController::render('admin/formations/index', [
      'active'     => 'formations',
      'pageTitle'  => 'Formations',
      'formations' => $formations,
      'filieres'   => (new Filiere())->findAll('nom'),
      'idFiliere'  => $idFiliere,
    ]);
  }

  public static function create(): void
  {
    self::form(null, ['idFiliere' => '', 'intitule' => '', 'niveau' => '', 'duree' => ''], []);
  }

  public static function store(): void
  {
    [$errors, $d] = self::validate();

    if (!$errors) {
      try {
        (new Formation())->create($d);
      } catch (\PDOException) {
        $errors['general'] = "Impossible d'ajouter la formation.";
      }
    }

    if ($errors) {
      self::form(null, $d, $errors);
      return;
    }

    Audit::log("Création de la formation {$d['intitule']}");
    Flash::set('success', "Formation « {$d['intitule']} » ajoutée.");
    self::redirect();
  }

  public static function edit(string $id): void
  {
    $formation = (new Formation())->find((int) $id);
    if ($formation === null) {
      self::notFound();
      return;
    }

    self::form($formation, [
      'idFormation' => $formation['idFormation'],
      'idFiliere'   => $formation['idFiliere'],
      'intitule'    => $formation['intitule'],
      'niveau'      => $formation['niveau'],
      'duree'       => $formation['duree'],
    ], []);
  }

  public static function update(string $id): void
  {
    $model     = new Formation();
    $formation = $model->find((int) $id);
    if ($formation === null) {
      self::notFound();
      return;
    }

    [$errors, $d] = self::validate();

    if (!$errors) {
      try {
        $model->update((int) $formation['idFormation'], $d);
      } catch (\PDOException) {
        $errors['general'] = 'Impossible de modifier la formation.';
      }
    }

    if ($errors) {
      $d['idFormation'] = $formation['idFormation'];
      self::form($formation, $d, $errors);
      return;
    }

    Audit::log("Modification de la formation {$formation['intitule']}");
    Flash::set('success', "Formation « {$d['intitule']} » mise à jour.");
    self::redirect();
  }

  /** Adds a semester to a formation (from the edit page). */
  public static function storeSemestre(string $id): void
  {
    $formation = (new Formation())->find((int) $id);
    if ($formation === null) {
      self::notFound();
      return;
    }

    $numero  = filter_var($_POST['numero'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]]);
    $libelle = trim((string) ($_POST['libelle'] ?? ''));
    if ($libelle === '' && $numero !== false) {
      $libelle = 'Semestre ' . $numero;
    }

    $error = null;
    if ($numero === false) {
      $error = 'Numéro de semestre invalide (entier entre 1 et 20).';
    } elseif (mb_strlen($libelle) > 50) {
      $error = 'Libellé trop long (50 caractères maximum).';
    } elseif (Semestre::numeroExists((int) $formation['idFormation'], $numero)) {
      $error = "Le semestre $numero existe déjà pour cette formation.";
    }

    if ($error === null) {
      try {
        Semestre::create([
          'idFormation' => (int) $formation['idFormation'],
          'libelle'     => $libelle,
          'numero'      => $numero,
        ]);
        Audit::log("Ajout du semestre $numero à la formation {$formation['intitule']}");
        Flash::set('success', "Semestre « $libelle » ajouté.");
      } catch (\PDOException) {
        $error = "Impossible d'ajouter le semestre.";
      }
    }

    if ($error !== null) {
      Flash::set('error', $error);
    }

    header('Location: /admin/formations/' . (int) $formation['idFormation'] . '/modifier');
    exit;
  }

  /** @return array{0: array<string,string>, 1: array} errors and cleaned data */
  private static function validate(): array
  {
    $errors   = [];
    $idFil    = (int) filter_var($_POST['idFiliere'] ?? 0, FILTER_VALIDATE_INT);
    $intitule = trim((string) ($_POST['intitule'] ?? ''));
    $niveau   = trim((string) ($_POST['niveau'] ?? ''));
    $dureeRaw = trim((string) ($_POST['duree'] ?? ''));

    if ($idFil <= 0 || (new Filiere())->find($idFil) === null) {
      $errors['idFiliere'] = 'Choisissez une filière valide.';
    }
    if ($intitule === '' || mb_strlen($intitule) > 150) {
      $errors['intitule'] = 'Intitulé requis (150 caractères maximum).';
    }
    if ($niveau !== '' && !in_array($niveau, self::niveaux(), true)) {
      $errors['niveau'] = 'Choisissez un niveau valide.';
    }

    $duree = null;
    if ($dureeRaw !== '') {
      $duree = filter_var($dureeRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10]]);
      if ($duree === false) {
        $errors['duree'] = 'Durée invalide (entier entre 1 et 10 ans).';
      }
    }

    return [$errors, [
      'idFiliere' => $idFil,
      'intitule'  => $intitule,
      'niveau'    => $niveau !== '' ? $niveau : null,
      'duree'     => $duree !== false ? $duree : $dureeRaw,   // keep what was typed so the form can show it again
    ]];
  }

  /** Allowed levels (VARCHAR(50)). Adjust to your faculty. */
  public static function niveaux(): array
  {
    return ['Licence', 'Master', 'Doctorat', 'Cycle ingénieur'];
  }

  private static function form(?array $formation, array $data, array $errors): void
  {
    AdminController::render('admin/formations/form', [
      'active'     => 'formations',
      'pageTitle'  => $formation === null ? 'Nouvelle formation' : 'Modifier ' . $data['intitule'],
      'formation'  => $formation,
      'data'       => $data,
      'errors'     => $errors,
      'filieres'   => (new Filiere())->findAll('nom'),
      'niveaux'    => self::niveaux(),
      'semestres'  => $formation === null ? [] : Semestre::findByFormation((int) $formation['idFormation']),
    ]);
  }

  private static function redirect(): never
  {
    header('Location: /admin/formations');
    exit;
  }

  private static function notFound(): void
  {
    http_response_code(404);
    echo '404 - Formation introuvable';
  }
}
