<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Models\Evaluation;
use App\Models\Note;
use App\Models\ResultatSemestre;
use App\Models\Semestre;
use App\Services\Audit;

/**
 * "Résultats": grade entry per examen + semester results (averages and decisions).
 */
class AdminResultatController
{
  private const SESSIONS = ['NORMALE', 'RATTRAPAGE'];

  /** Semester results; the semester is picked with ?semestre=ID. */
  public static function index(): void
  {
    $idSemestre = (int) filter_var($_GET['semestre'] ?? 0, FILTER_VALIDATE_INT);
    $semestre   = $idSemestre > 0 ? Semestre::findById($idSemestre) : null;

    AdminController::render('admin/resultats/index', [
      'active'     => 'resultats',
      'pageTitle'  => 'Résultats',
      'semestres'  => Semestre::findAllWithFormation(),
      'idSemestre' => $semestre ? (int) $semestre['idSemestre'] : 0,
      'resultats'  => $semestre ? ResultatSemestre::findBySemestre((int) $semestre['idSemestre']) : [],
      'seuil'      => ResultatSemestre::SEUIL,
    ]);
  }

  /** Recomputes the results of one semester from the grades. */
  public static function calculer(): void
  {
    $idSemestre = (int) filter_var($_POST['idSemestre'] ?? 0, FILTER_VALIDATE_INT);
    $semestre   = $idSemestre > 0 ? Semestre::findById($idSemestre) : null;

    if ($semestre === null) {
      Flash::set('error', 'Choisissez un semestre valide.');
      header('Location: /admin/resultats');
      exit;
    }

    try {
      $n = ResultatSemestre::calculer($idSemestre);
      Audit::log("Calcul des résultats du semestre #{$idSemestre} ($n étudiant(s))");
      Flash::set('success', $n > 0
        ? "Résultats calculés pour $n étudiant(s)."
        : 'Aucune note saisie pour ce semestre : rien à calculer.');
    } catch (\Throwable) {
      Flash::set('error', 'Impossible de calculer les résultats.');
    }

    header('Location: /admin/resultats?semestre=' . $idSemestre);
    exit;
  }

  /** Grading sheet of one examen. */
  public static function notes(string $id): void
  {
    $examen = (new Evaluation())->findDetailed((int) $id);
    if ($examen === null) {
      self::notFound();
      return;
    }

    $session = self::session($_GET['session'] ?? 'NORMALE');
    self::sheet($examen, $session, null, []);
  }

  public static function saveNotes(string $id): void
  {
    $evalModel = new Evaluation();
    $examen    = $evalModel->findDetailed((int) $id);
    if ($examen === null) {
      self::notFound();
      return;
    }

    $session = self::session($_POST['session'] ?? 'NORMALE');
    $bareme  = (float) $examen['bareme'];
    $sheet   = $evalModel->getFeuilleNotes((int) $examen['idEvaluation'], $session);
    $posted  = is_array($_POST['notes'] ?? null) ? $_POST['notes'] : [];

    $errors = [];
    $values = [];   // idEtudiant => ?float  (null = no grade)
    foreach ($sheet as $row) {
      $idE = (int) $row['idEtudiant'];
      $raw = $posted[$idE] ?? '';
      $raw = is_string($raw) ? str_replace(',', '.', trim($raw)) : '';

      if ($raw === '') {
        $values[$idE] = null;
        continue;
      }
      $v = filter_var($raw, FILTER_VALIDATE_FLOAT);
      if ($v === false || $v < 0 || $v > $bareme) {
        $errors[$idE] = 'Note entre 0 et ' . rtrim(rtrim(number_format($bareme, 2, '.', ''), '0'), '.') . '.';
        $values[$idE] = $raw;   // keep what was typed
        continue;
      }
      $values[$idE] = round($v, 2);
    }

    if ($errors) {
      self::sheet($examen, $session, $values, $errors);
      return;
    }

    $noteModel = new Note();
    $saved = 0;
    try {
      foreach ($values as $idEtudiant => $valeur) {
        // an empty field never erases an existing grade: only grades that were typed are saved
        if ($valeur !== null) {
          $noteModel->saveNote($idEtudiant, (int) $examen['idEvaluation'], $valeur, $session);
          $saved++;
        }
      }
    } catch (\PDOException) {
      Flash::set('error', "Impossible d'enregistrer les notes.");
      header('Location: /admin/resultats/examens/' . (int) $examen['idEvaluation'] . '?session=' . $session);
      exit;
    }

    Audit::log("Saisie des notes de l'examen #{$examen['idEvaluation']} ({$examen['moduleCode']}, $session) : $saved note(s)");
    Flash::set('success', "$saved note(s) enregistrée(s).");
    header('Location: /admin/resultats/examens/' . (int) $examen['idEvaluation'] . '?session=' . $session);
    exit;
  }

  private static function session(mixed $v): string
  {
    return is_string($v) && in_array($v, self::SESSIONS, true) ? $v : 'NORMALE';
  }

  private static function sheet(array $examen, string $session, ?array $override, array $errors): void
  {
    $evalModel = new Evaluation();
    $rows      = $evalModel->getFeuilleNotes((int) $examen['idEvaluation'], $session);

    AdminController::render('admin/resultats/notes', [
      'active'    => 'resultats',
      'pageTitle' => 'Notes – ' . $examen['moduleCode'] . ' (' . $examen['type'] . ')',
      'examen'    => $examen,
      'rows'      => $rows,
      'override'  => $override,   // values re-displayed after a validation error
      'errors'    => $errors,
      'session'   => $session,
      'sessions'  => self::SESSIONS,
      'stats'     => $evalModel->getStats((int) $examen['idEvaluation']),
    ]);
  }

  private static function notFound(): void
  {
    http_response_code(404);
    echo '404 - Examen introuvable';
  }
}
