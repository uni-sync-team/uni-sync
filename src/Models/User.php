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

  // ----- admin management -----

  /** Users with their roles as text, optionally filtered by login/email. */
  public static function findAll(?string $q = null, int $limit = 100): array
  {
    $sql = 'SELECT u.idUser, u.login, u.email, u.actif, u.dateCreation,
                   GROUP_CONCAT(r.nom ORDER BY r.idRole SEPARATOR \', \') AS roles
            FROM utilisateur u
            LEFT JOIN userRole ur ON ur.idUser = u.idUser
            LEFT JOIN role r ON r.idRole = ur.idRole';
    $params = [];
    if ($q !== null && $q !== '') {
      $sql .= ' WHERE u.login LIKE :q1 OR u.email LIKE :q2';
      $params = ['q1' => "%$q%", 'q2' => "%$q%"];
    }
    $stmt = Database::connect()->prepare($sql . ' GROUP BY u.idUser ORDER BY u.login LIMIT ' . (int) $limit);
    $stmt->execute($params);

    return $stmt->fetchAll();
  }

  public static function findById(int $id): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idUser, login, email, actif FROM utilisateur WHERE idUser = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch();
    if ($user === false) {
      return null;
    }

    $user['actif'] = (bool) $user['actif'];
    $user['roles'] = self::roles((int) $user['idUser']);
    return $user;
  }

  public static function allRoles(): array
  {
    return Database::connect()->query('SELECT idRole, nom, description FROM role ORDER BY idRole')->fetchAll();
  }

  /** Returns 'login' or 'email' if already used by another account, else null. */
  public static function conflict(string $login, string $email, ?int $exceptId = null): ?string
  {
    $stmt = Database::connect()->prepare(
      'SELECT login, email FROM utilisateur
       WHERE (login = :login OR email = :email) AND idUser <> :except
       LIMIT 1'
    );
    $stmt->execute(['login' => $login, 'email' => $email, 'except' => $exceptId ?? 0]);
    $row = $stmt->fetch();
    if ($row === false) {
      return null;
    }
    return strcasecmp($row['login'], $login) === 0 ? 'login' : 'email';
  }

  /** Creates the account and its single role; returns the new idUser. */
  public static function create(string $login, string $email, string $password, string $role): int
  {
    $pdo = Database::connect();
    $pdo->beginTransaction();
    try {
      $pdo->prepare('INSERT INTO utilisateur (login, email, motDePasse) VALUES (:l, :e, :p)')
          ->execute(['l' => $login, 'e' => $email, 'p' => password_hash($password, PASSWORD_DEFAULT)]);
      $id = (int) $pdo->lastInsertId();
      $pdo->prepare('INSERT INTO userRole (idUser, idRole) SELECT :id, idRole FROM role WHERE nom = :role')
          ->execute(['id' => $id, 'role' => $role]);
      $pdo->commit();
      return $id;
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  /** Updates email, active flag, role and (optionally) the password. */
  public static function update(int $id, string $email, bool $actif, string $role, ?string $password = null): void
  {
    $pdo = Database::connect();
    $pdo->beginTransaction();
    try {
      $pdo->prepare('UPDATE utilisateur SET email = :e, actif = :a WHERE idUser = :id')
          ->execute(['e' => $email, 'a' => (int) $actif, 'id' => $id]);
      if ($password !== null && $password !== '') {
        $pdo->prepare('UPDATE utilisateur SET motDePasse = :p WHERE idUser = :id')
            ->execute(['p' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id]);
      }
      $pdo->prepare('DELETE FROM userRole WHERE idUser = :id')->execute(['id' => $id]);
      $pdo->prepare('INSERT INTO userRole (idUser, idRole) SELECT :id, idRole FROM role WHERE nom = :role')
          ->execute(['id' => $id, 'role' => $role]);
      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }
}
