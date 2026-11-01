<?php

namespace App\Core;

/** One-shot messages shown on the next page (after a redirect). */
class Flash {
  public static function set(string $type, string $message): void
  {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
  }

  public static function pull(): ?array
  {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
  }
}
