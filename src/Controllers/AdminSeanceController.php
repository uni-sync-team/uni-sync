<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Models\Enseignant;
use App\Models\Module;
use App\Models\Salle;
use App\Models\Seance;
use App\Services\Audit;

class AdminSeanceController
{
  private const PER_PAGE = 20;
  private const INPUT_FMT = 'Y-m-d\TH:i';   // format of <input type="datetime-local">

  public static function index(): void
  {
    $filters = [
      'idModule'     => (int) self::param('module'),
      'idEnseignant' => (int) self::param('enseignant'),
      'idSalle'      => (int) self::param('salle'),
      'du'           => self::date(self::param('du')),
      'au'           => self::date(self::param('au')),
    ];

    $total = Seance::count($filters);
    $pages = max(1, (int) ceil($total / self::PER_PAGE));
    $page  = min(max(1, (int) self::param('p')), $pages);

    // URL parameters of the active filters (for the pagination links)
    $query = array_filter([
      'module'     => $filters['idModule'] ?: '',
      'enseignant' => $filters['idEnseignant'] ?: '',
      'salle'      => $filters['idSalle'] ?: '',
      'du'         => $filters['du'],
      'au'         => $filters['au'],
    ], fn($v) => $v !== '');

    AdminController::render('admin/seances/index', [
      'active'       => 'seances',
      'pageTitle'    => 'Séances',
      'seances'      => Seance::search($filters, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
      'filters'      => $filters,
      'modules'      => (new Module())->findAllWithContext(),
      'enseignants'  => Enseignant::findAll(null, 500),
      'salles'       => Salle::findAll(),
      'query'        => $query,
      'basePath'     => '/admin/seances',
      'total'        => $total,
      'page'         => $page,
      'pages'        => $pages,
    ]);
  }

  public static function create(): void
  {
    self::form(null, [
      'idModule' => '', 'idEnseignant' => '', 'idSalle' => '',
      'dateDebut' => '', 'dateFin' => '', 'typeSeance' => '',
    ], []);
  }

  public static function store(): void
  {
    [$errors, $d] = self::validate(null);

    if (!$errors) {
      try {
        Seance::create(self::toDb($d));
      } catch (\PDOException) {
        $errors['general'] = "Impossible d'ajouter la séance.";
      }
    }

    if ($errors) {
      self::form(null, $d, $errors);
      return;
    }

    Audit::log("Création d'une séance (module #{$d['idModule']}, {$d['dateDebut']})");
    Flash::set('success', 'Séance ajoutée.');
    self::redirect();
  }

  public static function edit(string $id): void
  {
    $seance = Seance::findById((int) $id);
    if ($seance === null) {
      self::notFound();
      return;
    }

    self::form($seance, [
      'idSeance'     => $seance['idSeance'],
      'idModule'     => $seance['idModule'],
      'idEnseignant' => $seance['idEnseignant'],
      'idSalle'      => $seance['idSalle'],
      'dateDebut'    => date(self::INPUT_FMT, strtotime($seance['dateDebut'])),
      'dateFin'      => date(self::INPUT_FMT, strtotime($seance['dateFin'])),
      'typeSeance'   => $seance['typeSeance'],
    ], []);
  }

  public static function update(string $id): void
  {
    $seance = Seance::findById((int) $id);
    if ($seance === null) {
      self::notFound();
      return;
    }

    [$errors, $d] = self::validate((int) $seance['idSeance']);

    if (!$errors) {
      try {
        Seance::update((int) $seance['idSeance'], self::toDb($d));
      } catch (\PDOException) {
        $errors['general'] = 'Impossible de modifier la séance.';
      }
    }

    if ($errors) {
      $d['idSeance'] = $seance['idSeance'];
      self::form($seance, $d, $errors);
      return;
    }

    Audit::log("Modification de la séance #{$seance['idSeance']}");
    Flash::set('success', 'Séance mise à jour.');
    self::redirect();
  }

  public static function destroy(string $id): void
  {
    $seance = Seance::findById((int) $id);
    if ($seance === null) {
      self::notFound();
      return;
    }

    try {
      Seance::delete((int) $seance['idSeance']);
      Audit::log("Suppression de la séance #{$seance['idSeance']} ({$seance['moduleCode']})");
      Flash::set('success', 'Séance supprimée.');
    } catch (\PDOException) {
      Flash::set('error', 'Impossible de supprimer la séance.');
    }
    self::redirect();
  }

  /** @return array{0: array<string,string>, 1: array} errors and cleaned data (dates in input format) */
  private static function validate(?int $excludeId): array
  {
    $errors = [];

    $idModule = (int) filter_var($_POST['idModule'] ?? 0, FILTER_VALIDATE_INT);
    $idEns    = (int) filter_var($_POST['idEnseignant'] ?? 0, FILTER_VALIDATE_INT);
    $idSalle  = (int) filter_var($_POST['idSalle'] ?? 0, FILTER_VALIDATE_INT);
    $debutRaw = trim((string) ($_POST['dateDebut'] ?? ''));
    $finRaw   = trim((string) ($_POST['dateFin'] ?? ''));
    $type     = trim((string) ($_POST['typeSeance'] ?? ''));

    if ($idModule <= 0 || (new Module())->find($idModule) === null) {
      $errors['idModule'] = 'Choisissez un module valide.';
    }
    if ($idEns <= 0 || Enseignant::findById($idEns) === null) {
      $errors['idEnseignant'] = 'Choisissez un enseignant valide.';
    }
    if ($idSalle <= 0 || Salle::findById($idSalle) === null) {
      $errors['idSalle'] = 'Choisissez une salle valide.';
    }

    $debut = \DateTime::createFromFormat(self::INPUT_FMT, $debutRaw);
    $fin   = \DateTime::createFromFormat(self::INPUT_FMT, $finRaw);
    if (!$debut || $debut->format(self::INPUT_FMT) !== $debutRaw) {
      $errors['dateDebut'] = 'Date et heure de début invalides.';
    }
    if (!$fin || $fin->format(self::INPUT_FMT) !== $finRaw) {
      $errors['dateFin'] = 'Date et heure de fin invalides.';
    }
    if ($debut && $fin && !isset($errors['dateDebut']) && !isset($errors['dateFin'])) {
      if ($fin <= $debut) {
        $errors['dateFin'] = 'La fin doit être postérieure au début.';
      } elseif ($debut->format('Y-m-d') !== $fin->format('Y-m-d')) {
        $errors['dateFin'] = 'Une séance doit commencer et finir le même jour.';
      }
    }

    if ($type !== '' && !in_array($type, Seance::TYPES, true)) {
      $errors['typeSeance'] = 'Choisissez un type valide.';
    }

    // room / teacher availability
    if (!$errors) {
      $conflict = Seance::conflict(
        $idSalle, $idEns,
        $debut->format('Y-m-d H:i:s'), $fin->format('Y-m-d H:i:s'),
        $excludeId
      );
      if ($conflict === 'salle') {
        $errors['idSalle'] = 'Cette salle est déjà occupée sur ce créneau.';
      } elseif ($conflict === 'enseignant') {
        $errors['idEnseignant'] = 'Cet enseignant a déjà une séance sur ce créneau.';
      }
    }

    return [$errors, [
      'idModule'     => $idModule,
      'idEnseignant' => $idEns,
      'idSalle'      => $idSalle,
      'dateDebut'    => $debutRaw,
      'dateFin'      => $finRaw,
      'typeSeance'   => $type !== '' ? $type : null,
    ]];
  }

  /** Converts the input-format dates to MySQL DATETIME. */
  private static function toDb(array $d): array
  {
    foreach (['dateDebut', 'dateFin'] as $k) {
      $d[$k] = \DateTime::createFromFormat(self::INPUT_FMT, $d[$k])->format('Y-m-d H:i:s');
    }
    return $d;
  }

  private static function form(?array $seance, array $data, array $errors): void
  {
    AdminController::render('admin/seances/form', [
      'active'      => 'seances',
      'pageTitle'   => $seance === null ? 'Nouvelle séance' : 'Modifier la séance',
      'seance'      => $seance,
      'data'        => $data,
      'errors'      => $errors,
      'modules'     => (new Module())->findAllWithContext(),
      'enseignants' => Enseignant::findAll(null, 500),
      'salles'      => Salle::findAll(),
    ]);
  }

  private static function param(string $key): string
  {
    $v = $_GET[$key] ?? '';
    return is_string($v) ? trim($v) : '';
  }

  /** Keeps a Y-m-d date, drops anything else. */
  private static function date(string $v): string
  {
    $d = \DateTime::createFromFormat('Y-m-d', $v);
    return ($d && $d->format('Y-m-d') === $v) ? $v : '';
  }

  private static function redirect(): never
  {
    header('Location: /admin/seances');
    exit;
  }

  private static function notFound(): void
  {
    http_response_code(404);
    echo '404 - Séance introuvable';
  }
}
