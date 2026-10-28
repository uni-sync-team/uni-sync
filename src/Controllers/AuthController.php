<?php

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Models\User;

class AuthController {
  private const HOME = ['admin' => '/admin', 'prof' => '/prof', 'etudiant' => '/etudiant'];

  public static function showLogin(): void
  {
    if (Session::isLoggedIn()) {
      self::redirectHome();
    }

    $error = $_SESSION['login_error'] ?? null;
    unset($_SESSION['login_error']);

    View::render('login', ['error' => $error]);
  }

  public static function login(): void
  {
    if (!Csrf::check($_POST['csrf'] ?? null)) {
      self::fail('Session expirée, veuillez réessayer.');
    }

    $login    = trim((string) ($_POST['login'] ?? ''));
    $password = (string) ($_POST['motDePasse'] ?? '');

    $user = User::verifyCredentials($login, $password);
    $role = $user ? User::primaryRole($user['roles']) : null;

    if ($user === null || $role === null) {
      self::fail('Login ou mot de passe incorrect.');
    }

    Session::login($user['idUser'], $role);
    self::redirectHome();
  }

  public static function logout(): void
  {
    if (!Csrf::check($_POST['csrf'] ?? null)) {
      http_response_code(403);
      echo '403 - Forbidden Action';
      return;
    }

    Session::logout();
    header('Location: /');
    exit;
  }

  private static function fail(string $message): never
  {
    $_SESSION['login_error'] = $message;
    header('Location: /login');
    exit;
  }

  private static function redirectHome(): never
  {
    header('Location: ' . (self::HOME[Session::role()] ?? '/'));
    exit;
  }
}
