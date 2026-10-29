<?php 

namespace App\Middleware;

use App\Core\Session;

class Auth {
  public static function requireLogin(): void {
    if (!Session::isLoggedIn()) {
      header("Location: /login");
      exit;
    }
  }

  /**
  * @param string ...$roles
  * some action can be valid for both roles (admin, prof)
  * requireRole('admin', 'prof')
  */
  public static function requireRole(string ...$roles): void {
    self::requireLogin();

    if (!in_array(Session::role(), $roles, true)) {
      http_response_code(403); // 403 is forbidden
      // TODO: better error handling
      echo "403 - Forbidden Action";
      exit();
    }
  }
}
