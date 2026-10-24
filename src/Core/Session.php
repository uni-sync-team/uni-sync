<?php

class Session {
  public static function start() {
    if (session_status() === PHP_SESSION_ACTIVE)
      return;

    session_set_cookie_params([
      'lifetime' => 0, // untill the browser is closed
      'path' => '/',
      'httponly' => true, // javascript can't read session
      'samesite' => 'Lax',
    ]);

    session_start();
  }

  // insert new user data into the session
  public static function login(int $userId, string $role): void {
    session_regenerate_id(true);
    $_SESSION["user_id"] = $userId;
    $_SESSION["role"] = $role;
  }

  // destory the session after login out
  public function logout(): void {
    $_SESSION = [];
    session_destroy();
    setcookie(session_name(), "", time() - 1, '/');
  }

  public static function userId(): ?int {
    return $_SESSION['user_id'] ?? null;
  }

  public static function role(): ?string {
    return $_SESSION['role'] ?? null;
  }

  public static function isLoggedIn(): bool {
    return self::userId() !== null;
  }
}
