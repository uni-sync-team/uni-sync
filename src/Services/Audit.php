<?php

namespace App\Services;

use App\Core\Session;
use App\Models\Historique;

class Audit {
  /** Records "who did what" for the logged-in user. A logging failure never breaks the action. */
  public static function log(string $action): void
  {
    $idUser = Session::userId();
    if ($idUser === null) {
      return;
    }

    try {
      Historique::add($idUser, mb_substr($action, 0, 255));
    } catch (\Throwable $e) {
      error_log('Audit failed: ' . $e->getMessage());
    }
  }
}
