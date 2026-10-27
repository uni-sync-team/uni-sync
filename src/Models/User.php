<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User {
  //Session stores one role so we pick by this order
  private const ROLE_PRIORITY = ['admin', 'prof', 'etudiant'];

  public static function findByLogin(string $login): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idUser, login, email, motDePasse, actif
       FROM utilisateur
       WHERE login = :login
       LIMIT 1'
    );
    $stmt->execute(['login' => $login]);
    $user = $stmt->fetch();

    if ($user === false) {
      return null;
    }

    $user['idUser'] = (int) $user['idUser'];
    $user['actif']  = (bool) $user['actif'];
    $user['roles']  = self::roles($user['idUser']);
    return $user;
  }

  //returns string[] role names like  ['prof']
  public static function roles(int $idUser): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT r.nom
       FROM userRole ur
       JOIN role r ON r.idRole = ur.idRole
       WHERE ur.idUser = :id
       ORDER BY r.idRole'
    );
    $stmt->execute(['id' => $idUser]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }
  //returns the user without the password hash if the credentials are valid and the account is active
  public static function verifyCredentials(string $login, string $password): ?array
  {
    $user = self::findByLogin($login);

    if ($user === null || !$user['actif']) {
      return null;
    }
    if (!password_verify($password, $user['motDePasse'])) {
      return null;
    }

    unset($user['motDePasse']);
    return $user;
  }

  // The single role to store in the session (admin > prof > etudiant)
  public static function primaryRole(array $roles): ?string
  {
    foreach (self::ROLE_PRIORITY as $role) {
      if (in_array($role, $roles, true)) {
        return $role;
      }
    }
    return null;
  }
}
