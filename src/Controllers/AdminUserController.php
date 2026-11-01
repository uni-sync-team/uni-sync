<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Core\Session;
use App\Models\User;
use App\Services\Audit;

class AdminUserController {
  public static function index(): void
  {
    $q = trim((string) ($_GET['q'] ?? ''));

    AdminController::render('admin/users/index', [
      'active'    => 'utilisateurs',
      'pageTitle' => 'Utilisateurs',
      'users'     => User::findAll($q),
      'q'         => $q,
      'me'        => Session::userId(),
    ]);
  }

  public static function create(): void
  {
    self::form(null, ['login' => '', 'email' => '', 'role' => '', 'actif' => true], []);
  }

  public static function store(): void
  {
    [$errors, $d] = self::validate(null);
    if (!$errors) {
      try {
        User::create($d['login'], $d['email'], $d['password'], $d['role']);
      } catch (\PDOException $e) {
        $errors['general'] = 'Impossible de créer le compte (login ou email déjà utilisé ?).';
      }
    }
    if ($errors) {
      self::form(null, $d, $errors);
      return;
    }

    Audit::log("Création de l'utilisateur {$d['login']} (rôle {$d['role']})");
    Flash::set('success', "Compte « {$d['login']} » créé.");
    self::redirect();
  }

  public static function edit(string $id): void
  {
    $user = User::findById((int) $id);
    if ($user === null) {
      self::notFound();
      return;
    }
    self::form($user, [
      'email' => $user['email'],
      'role'  => User::primaryRole($user['roles']) ?? '',
      'actif' => $user['actif'],
    ], []);
  }

  public static function update(string $id): void
  {
    $user = User::findById((int) $id);
    if ($user === null) {
      self::notFound();
      return;
    }

    [$errors, $d] = self::validate($user);
    if (!$errors) {
      try {
        User::update($user['idUser'], $d['email'], $d['actif'], $d['role'], $d['password']);
      } catch (\PDOException $e) {
        $errors['general'] = 'Impossible de modifier le compte (email déjà utilisé ?).';
      }
    }
    if ($errors) {
      self::form($user, $d, $errors);
      return;
    }

    Audit::log("Modification de l'utilisateur {$user['login']} (rôle {$d['role']}, " . ($d['actif'] ? 'actif' : 'désactivé') . ')');
    Flash::set('success', "Compte « {$user['login']} » mis à jour.");
    self::redirect();
  }

  /** @return array{0: array<string,string>, 1: array} errors and cleaned data */
  private static function validate(?array $existing): array
  {
    $errors   = [];
    $isSelf   = $existing !== null && $existing['idUser'] === Session::userId();
    $roles    = array_column(User::allRoles(), 'nom');
    $login    = $existing['login'] ?? trim((string) ($_POST['login'] ?? ''));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $role     = (string) ($_POST['role'] ?? '');
    $actif    = isset($_POST['actif']);

    // An admin can't lock themselves out: role and active flag stay as they are.
    if ($isSelf) {
      $role  = User::primaryRole($existing['roles']) ?? $role;
      $actif = true;
    }

    if ($existing === null && !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $login)) {
      $errors['login'] = '3 à 50 caractères : lettres, chiffres, point, tiret, underscore.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
      $errors['email'] = 'Adresse email invalide.';
    }
    if (($existing === null || $password !== '') && strlen($password) < 8) {
      $errors['password'] = $existing === null ? 'Mot de passe requis (8 caractères minimum).' : '8 caractères minimum (ou laissez vide).';
    }
    if (!in_array($role, $roles, true)) {
      $errors['role'] = 'Choisissez un rôle.';
    }
    if (!isset($errors['login']) && !isset($errors['email'])) {
      $taken = User::conflict($login, $email, $existing['idUser'] ?? null);
      if ($taken !== null) {
        $errors[$taken] = ($taken === 'login' ? 'Ce login' : 'Cet email') . ' est déjà utilisé.';
      }
    }

    return [$errors, ['login' => $login, 'email' => $email, 'password' => $password, 'role' => $role, 'actif' => $actif]];
  }

  private static function form(?array $user, array $data, array $errors): void
  {
    AdminController::render('admin/users/form', [
      'active'    => 'utilisateurs',
      'pageTitle' => $user === null ? 'Nouvel utilisateur' : 'Modifier ' . $user['login'],
      'user'      => $user,
      'data'      => $data,
      'errors'    => $errors,
      'roles'     => User::allRoles(),
      'isSelf'    => $user !== null && $user['idUser'] === Session::userId(),
    ]);
  }

  private static function redirect(): never
  {
    header('Location: /admin/utilisateurs');
    exit;
  }

  private static function notFound(): void
  {
    http_response_code(404);
    echo '404 - Utilisateur introuvable';
  }
}
