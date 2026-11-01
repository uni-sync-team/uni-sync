<?php

namespace App\Middleware;

use App\Core\Csrf;

class CsrfGuard {
  /** Router middleware for every POST route: rejects the request without a valid token. */
  public static function verify(): void
  {
    if (!Csrf::check($_POST['csrf'] ?? null)) {
      http_response_code(403);
      echo '403 - Forbidden Action (invalid CSRF token)';
      exit;
    }
  }
}
