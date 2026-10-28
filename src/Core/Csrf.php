<?php

namespace App\Core;

class Csrf {
  /** Token stored in the session; print it in a hidden input of every POST form. */
  public static function token(): string
  {
    Session::start();
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
  }

  public static function check(?string $token): bool
  {
    return is_string($token)
      && isset($_SESSION['csrf'])
      && hash_equals($_SESSION['csrf'], $token);
  }
}
