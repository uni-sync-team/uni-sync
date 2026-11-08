<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Models\Evaluation;
use App\Models\Module;
use App\Services\Audit;

class AdminExamenController
{
  public static function index(): void
  {
    $idModule = (int) filter_var($_GET['module'] ?? 0, FILTER_VALIDATE_INT);
    $type     = is_string($_GET['type'] ?? null) ? trim($_GET['type']) : '';
    if ($type !== '' && !in_array($type, Evaluation::TYPES, true)) {
      $type = '';
    }

    AdminController::render('admin/examens/index', [
      'active'    => 'examens',
      'pageTitle' => 'Examens',
      'examens'   => (new Evaluation())->findAllDetailed($idModule, $type),
      'modules'   => (new Module())->findAllWithContext(),
      'types'     => Evaluation::TYPES,
      'idModule'  => $idModule,
      'type'      => $type,
    ]);
  }

  public static function create(): void
  {
    self::form(null, [
      'idModule' => (int) filter_var($_GET['module'] ?? 0, FILTER_VALIDATE_INT) ?: '',
      'type' => '', 'dateEvaluation' => '', 'bareme' => '20', 'coefficient' => '1',
    ], []);
  }

  public static function store(): void
  {
    [$errors, $d] = self::validate();

    if (!$errors) {
      try {
        (new Evaluation())->create($d);
      } catch (\PDOException) {
        $errors['general'] = "Impossible d'ajouter l'examen.";
      }
    }

    if ($errors) {
      self::form(null, $d, $errors);
      return;
    }

    Audit::log("Création d'un examen « {$d['type']} » (module #{$d['idModule']})");
    Flash::set('success', "Examen « {$d['type']} » ajouté.");
    self::redirect();
  }

  public static function edit(string $id): void
  {
    $examen = (new Evaluation())->findDetailed((int) $id);
    if ($examen === null) {
      self::notFound();
      return;
    }

    self::form($examen, [
      'idEvaluation'   => $examen['idEvaluation'],
      'idModule'       => $examen['idModule'],
      'type'           => $examen['type'],
      'dateEvaluation' => $examen['dateEvaluation'],
      'bareme'         => $examen['bareme'],
      'coefficient'    => $examen['coefficient'],
    ], []);
  }

  public static function update(string $id): void
  {
    $model  = new Evaluation();
    $examen = $model->findDetailed((int) $id);
    if ($examen === null) {
      self::notFound();
      return;
    }

    [$errors, $d] = self::validate();

    // changing the scale once grades exist would silently change their meaning
    if (!$errors && (int) $examen['nbNotes'] > 0 && (float) $d['bareme'] !== (float) $examen['bareme']) {
      $errors['bareme'] = 'Le barème ne peut plus changer : des notes sont déjà saisies.';
    }

    if (!$errors) {
      try {
        $model->update((int) $examen['idEvaluation'], $d);
      } catch (\PDOException) {
        $errors['general'] = "Impossible de modifier l'examen.";
      }
    }

    if ($errors) {
      $d['idEvaluation'] = $examen['idEvaluation'];
      self::form($examen, $d, $errors);
      return;
    }

    Audit::log("Modification de l'examen #{$examen['idEvaluation']} ({$examen['moduleCode']})");
    Flash::set('success', 'Examen mis à jour.');
    self::redirect();
  }

  public static function destroy(string $id): void
  {
    $model  = new Evaluation();
    $examen = $model->findDetailed((int) $id);
    if ($examen === null) {
      self::notFound();
      return;
    }

    if ((int) $examen['nbNotes'] > 0) {
      Flash::set('error', 'Impossible de supprimer : des notes sont déjà saisies pour cet examen.');
      self::redirect();
    }

    try {
      $model->delete((int) $examen['idEvaluation']);
      Audit::log("Suppression de l'examen #{$examen['idEvaluation']} ({$examen['moduleCode']})");
      Flash::set('success', 'Examen supprimé.');
    } catch (\PDOException) {
      Flash::set('error', "Impossible de supprimer l'examen (il est référencé ailleurs, ex. réclamations).");
    }
    self::redirect();
  }

  /** @return array{0: array<string,string>, 1: array} errors and cleaned data */
  private static function validate(): array
  {
    $errors   = [];
    $idModule = (int) filter_var($_POST['idModule'] ?? 0, FILTER_VALIDATE_INT);
    $type     = trim((string) ($_POST['type'] ?? ''));
    $dateRaw  = trim((string) ($_POST['dateEvaluation'] ?? ''));
    $baremeRaw = str_replace(',', '.', trim((string) ($_POST['bareme'] ?? '')));
    $coefRaw   = str_replace(',', '.', trim((string) ($_POST['coefficient'] ?? '')));

    if ($idModule <= 0 || (new Module())->find($idModule) === null) {
      $errors['idModule'] = 'Choisissez un module valide.';
    }
    if (!in_array($type, Evaluation::TYPES, true)) {
      $errors['type'] = "Choisissez un type d'examen valide.";
    }

    if ($dateRaw !== '') {
      $dt = \DateTime::createFromFormat('Y-m-d', $dateRaw);
      if (!$dt || $dt->format('Y-m-d') !== $dateRaw) {
        $errors['dateEvaluation'] = 'Date invalide.';
      }
    }

    $bareme = filter_var($baremeRaw, FILTER_VALIDATE_FLOAT);
    if ($bareme === false || $bareme <= 0 || $bareme > 999.99) {
      $errors['bareme'] = 'Barème invalide (nombre positif, 999,99 maximum).';
    }

    $coef = filter_var($coefRaw, FILTER_VALIDATE_FLOAT);
    if ($coef === false || $coef <= 0 || $coef > 99.99) {
      $errors['coefficient'] = 'Coefficient invalide (nombre positif, 99,99 maximum).';
    }

    return [$errors, [
      'idModule'       => $idModule,
      'type'           => $type,
      'dateEvaluation' => $dateRaw !== '' ? $dateRaw : null,
      'bareme'         => $bareme !== false ? $bareme : $baremeRaw,
      'coefficient'    => $coef !== false ? $coef : $coefRaw,
    ]];
  }

  private static function form(?array $examen, array $data, array $errors): void
  {
    AdminController::render('admin/examens/form', [
      'active'    => 'examens',
      'pageTitle' => $examen === null ? 'Nouvel examen' : "Modifier l'examen",
      'examen'    => $examen,
      'data'      => $data,
      'errors'    => $errors,
      'modules'   => (new Module())->findAllWithContext(),
      'types'     => Evaluation::TYPES,
    ]);
  }

  private static function redirect(): never
  {
    header('Location: /admin/examens');
    exit;
  }

  private static function notFound(): void
  {
    http_response_code(404);
    echo '404 - Examen introuvable';
  }
}
