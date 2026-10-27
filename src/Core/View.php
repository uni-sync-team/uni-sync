<?php

namespace App\Core;

class View {
  private const MONTHS_FR = [
    1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
    5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
    9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
  ];

  /** Load views/<name>.php; the keys of $data become variables in the view. */
  public static function render(string $view, array $data = []): void
  {
    $file = __DIR__ . '/../../views/' . $view . '.php';

    if (!is_file($file)) {
      http_response_code(500);
      echo 'View not found';
      return;
    }

    extract($data, EXTR_SKIP);
    require $file;
  }

  /** Escape a value before printing it in HTML. Use it on every database value. */
  public static function e(?string $value): string
  {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }

  /** '2026-11-03' -> '3 novembre' */
  public static function dateFr(string $date): string
  {
    $time = strtotime($date);
    if ($time === false) {
      return $date;
    }
    return (int) date('j', $time) . ' ' . self::MONTHS_FR[(int) date('n', $time)];
  }
}
