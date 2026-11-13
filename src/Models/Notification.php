<?php

namespace App\Models;

use App\Core\Database;

class Notification {
  private static function like(string $v): string
  {
    return '%' . addcslashes($v, '%_\\') . '%';
  }

  /** @return array{0: string, 1: array} */
  private static function where(array $f): array
  {
    $sql = ' WHERE 1 = 1';
    $p   = [];
    if (($f['q'] ?? '') !== '') {
      $sql .= ' AND (u.login LIKE :q1 OR n.message LIKE :q2)';
      $p['q1'] = $p['q2'] = self::like($f['q']);
    }
    if (($f['statut'] ?? '') === 'lu') {
      $sql .= ' AND n.lu = 1';
    } elseif (($f['statut'] ?? '') === 'non_lu') {
      $sql .= ' AND n.lu = 0';
    }
    return [$sql, $p];
  }

  /** Filters: q (login or message text), statut ('lu' | 'non_lu'). Newest first. */
  public static function search(array $filters, int $limit, int $offset = 0): array
  {
    [$where, $params] = self::where($filters);
    $stmt = Database::connect()->prepare(
      'SELECT n.idNotification, n.message, n.dateCreation, n.lu, u.login
       FROM notification n
       JOIN utilisateur u ON u.idUser = n.idUser' . $where . '
       ORDER BY n.dateCreation DESC, n.idNotification DESC
       LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset
    );
    $stmt->execute($params);

    return $stmt->fetchAll();
  }

  public static function count(array $filters = []): int
  {
    [$where, $params] = self::where($filters);
    $stmt = Database::connect()->prepare(
      'SELECT COUNT(*) FROM notification n JOIN utilisateur u ON u.idUser = n.idUser' . $where
    );
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
  }

  public static function findById(int $id): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT n.idNotification, n.message, u.login
       FROM notification n JOIN utilisateur u ON u.idUser = n.idUser
       WHERE n.idNotification = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  /** One notification for one user; returns the number of notifications created (1). */
  public static function sendToUser(int $idUser, string $message): int
  {
    Database::connect()
      ->prepare('INSERT INTO notification (idUser, message) VALUES (:id, :m)')
      ->execute(['id' => $idUser, 'm' => $message]);

    return 1;
  }

  /** One notification per active user holding the role; returns how many were created. */
  public static function sendToRole(string $role, string $message): int
  {
    $stmt = Database::connect()->prepare(
      'INSERT INTO notification (idUser, message)
       SELECT DISTINCT u.idUser, :m
       FROM utilisateur u
       JOIN userRole ur ON ur.idUser = u.idUser
       JOIN role r      ON r.idRole = ur.idRole
       WHERE u.actif = 1 AND r.nom = :role'
    );
    $stmt->execute(['m' => $message, 'role' => $role]);

    return $stmt->rowCount();
  }

  /** One notification per active user; returns how many were created. */
  public static function sendToAll(string $message): int
  {
    $stmt = Database::connect()->prepare(
      'INSERT INTO notification (idUser, message) SELECT idUser, :m FROM utilisateur WHERE actif = 1'
    );
    $stmt->execute(['m' => $message]);

    return $stmt->rowCount();
  }

  public static function delete(int $id): bool
  {
    $stmt = Database::connect()->prepare('DELETE FROM notification WHERE idNotification = :id');
    $stmt->execute(['id' => $id]);

    return $stmt->rowCount() > 0;
  }

  // ----- user inbox (student / teacher side) -----

  public static function forUser(int $idUser, int $limit, int $offset = 0): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idNotification, message, dateCreation, lu FROM notification
       WHERE idUser = :id ORDER BY dateCreation DESC, idNotification DESC
       LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset
    );
    $stmt->execute(['id' => $idUser]);

    return $stmt->fetchAll();
  }

  public static function countForUser(int $idUser): int
  {
    $stmt = Database::connect()->prepare('SELECT COUNT(*) FROM notification WHERE idUser = :id');
    $stmt->execute(['id' => $idUser]);

    return (int) $stmt->fetchColumn();
  }

  public static function countUnread(int $idUser): int
  {
    $stmt = Database::connect()->prepare('SELECT COUNT(*) FROM notification WHERE idUser = :id AND lu = 0');
    $stmt->execute(['id' => $idUser]);

    return (int) $stmt->fetchColumn();
  }

  /** Marks one notification as read, only if it belongs to the user. */
  public static function markRead(int $id, int $idUser): bool
  {
    $stmt = Database::connect()->prepare('UPDATE notification SET lu = 1 WHERE idNotification = :id AND idUser = :u AND lu = 0');
    $stmt->execute(['id' => $id, 'u' => $idUser]);

    return $stmt->rowCount() > 0;
  }

  public static function markAllRead(int $idUser): int
  {
    $stmt = Database::connect()->prepare('UPDATE notification SET lu = 1 WHERE idUser = :u AND lu = 0');
    $stmt->execute(['u' => $idUser]);

    return $stmt->rowCount();
  }
}
