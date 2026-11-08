<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Core\Session;
use App\Models\Departement;
use App\Models\Enseignant;
use App\Models\User;
use App\Services\Audit;

class AdminEnseignantController
{
  public static function index(): void
  {
    $q = trim((string) ($_GET['q'] ?? ''));

    AdminController::render('admin/enseignants/index', [
      'active'      => 'enseignants',
      'pageTitle'   => 'Enseignants',
      'enseignants' => Enseignant::findAll($q),
      'q'           => $q,
      'me'          => Session::userId(),
    ]);
  }

  public static function create(): void
  {
    self::form(null, null, [
      'login'         => '',
      'email'         => '',
      'prenom'        => '',
      'nom'           => '',
      'idDepartement' => '',
      'grade'         => '',
    ], []);
  }

  public static function store(): void
  {
    [$errors, $d] = self::validate(null);

    if (!$errors) {
      try {
        Enseignant::createWithUser($d);
      } catch (\PDOException) {
        $errors['general'] = "Impossible d'ajouter l'enseignant (login ou email déjà utilisé ?).";
      } catch (\RuntimeException $e) {
        $errors['general'] = $e->getMessage();   // e.g. missing role
      }
    }

    if ($errors) {
      $d['password'] = '';
      self::form(null, null, $d, $errors);
      return;
    }

    Audit::log("Création de l'enseignant {$d['nom']} {$d['prenom']}");
    Flash::set('success', "Enseignant « {$d['nom']} {$d['prenom']} » ajouté.");
    self::redirect();
  }

  public static function edit(string $id): void
  {
    [$ens, $user] = self::load((int) $id);
    if ($ens === null) {
      return;
    }

    self::form($ens, $user, [
      'idEnseignant'  => $ens['idEnseignant'],
      'prenom'        => $ens['prenom'],
      'nom'           => $ens['nom'],
      'idDepartement' => $ens['idDepartement'],
      'grade'         => $ens['grade'],
      'actif'         => (bool) $ens['actif'],
    ], []);
  }

  public static function update(string $id): void
  {
    [$ens, $user] = self::load((int) $id);
    if ($ens === null) {
      return;
    }

    [$errors, $d, $actif] = self::validate($ens);

    if (!$errors && !Enseignant::update($ens['idEnseignant'], $d, $actif)) {
      $errors['general'] = "Impossible de modifier l'enseignant.";
    }

    if ($errors) {
      $d['idEnseignant'] = $ens['idEnseignant'];
      $d['actif']        = $actif;
      self::form($ens, $user, $d, $errors);
      return;
    }

    Audit::log("Modification de l'enseignant {$ens['nom']} {$ens['prenom']}");
    Flash::set('success', "Enseignant « {$ens['nom']} {$ens['prenom']} » mis à jour.");
    self::redirect();
  }

  /** @return array{0: array<string,string>, 1: array, 2: bool} errors, cleaned data, actif */
  private static function validate(?array $existing): array
  {
    $errors = [];

    $login    = trim((string) ($_POST['login'] ?? ''));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    // --- account (only when creating) ---
    if ($existing === null) {
      if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $login)) {
        $errors['login'] = '3 à 50 caractères : lettres, chiffres, point, tiret, underscore.';
      }
      if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors['email'] = 'Adresse email invalide.';
      }
      if (strlen($password) < 8) {
        $errors['password'] = 'Mot de passe requis (8 caractères minimum).';
      }
      if (!isset($errors['login']) && !isset($errors['email'])) {
        $taken = User::conflict($login, $email, null);
        if ($taken !== null) {
          $errors[$taken] = ($taken === 'login' ? 'Ce login' : 'Cet email') . ' est déjà utilisé.';
        }
      }
    }

    $prenom = trim((string) ($_POST['prenom'] ?? ''));
    $nom    = trim((string) ($_POST['nom'] ?? ''));
    $idDep  = (int) filter_var($_POST['idDepartement'] ?? 0, FILTER_VALIDATE_INT);
    $grade  = trim((string) ($_POST['grade'] ?? ''));
    $actif  = isset($_POST['actif']);

    // --- names ---
    $namePattern = '/^\p{L}[\p{L} \'\-]*$/u';

    if ($prenom === '' || mb_strlen($prenom) > 100 || !preg_match($namePattern, $prenom)) {
      $errors['prenom'] = 'Prénom requis (lettres, espaces, tiret, apostrophe ; 100 max).';
    }
    if ($nom === '' || mb_strlen($nom) > 100 || !preg_match($namePattern, $nom)) {
      $errors['nom'] = 'Nom requis (lettres, espaces, tiret, apostrophe ; 100 max).';
    }

    // --- department ---
    if ($idDep <= 0 || !Departement::findById($idDep)) {
      $errors['idDepartement'] = 'Choisissez un département valide.';
    }

    // --- grade (optional, must be one of the allowed values) ---
    if ($grade !== '' && !in_array($grade, Enseignant::GRADES, true)) {
      $errors['grade'] = 'Choisissez un grade valide.';
    }

    return [$errors, [
      'login'         => $login,
      'email'         => $email,
      'password'      => $password,
      'prenom'        => $prenom,
      'nom'           => $nom,
      'idDepartement' => $idDep,
      'grade'         => $grade !== '' ? $grade : null,
    ], $actif];
  }

  /** @return array{0: ?array, 1: ?array} teacher and account, or [null, null] after a 404 */
  private static function load(int $id): array
  {
    $ens  = Enseignant::findById($id);
    $user = $ens !== null ? User::findById((int) $ens['idUser']) : null;

    if ($ens === null || !$user) {
      self::notFound();
      return [null, null];
    }
    return [$ens, $user];
  }

  private static function form(?array $ens, ?array $user, array $data, array $errors): void
  {
    AdminController::render('admin/enseignants/form', [
      'active'        => 'enseignants',
      'pageTitle'     => $ens === null ? 'Nouvel Enseignant' : 'Modifier ' . $data['nom'] . ' ' . $data['prenom'],
      'enseignant'    => $ens,
      'user'          => $user,
      'data'          => $data,
      'errors'        => $errors,
      'departements'  => Departement::findAll(),
    ]);
  }

  private static function redirect(): never
  {
    header('Location: /admin/enseignants');
    exit;
  }

  private static function notFound(): void
  {
    http_response_code(404);
    echo '404 - Enseignant introuvable';
  }
}
