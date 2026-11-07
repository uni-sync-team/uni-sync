<?php

namespace App\Models;

use App\Core\Database;

class Historique {
  public static function add(int $idUser, string $action): void
  {
    $stmt = Database::connect()->prepare(
      'INSERT INTO historiqueAction (idUser, action) VALUES (:id, :action)'
    );
    $stmt->execute(['id' => $idUser, 'action' => $action]);
  }

  public static function recent(int $limit = 8): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT h.idAction, h.action, h.dateAction, u.login
       FROM historiqueAction h
       JOIN utilisateur u ON u.idUser = h.idUser
       ORDER BY h.dateAction DESC, h.idAction DESC
       LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
  }

  /** Escapes % and _ so user text is matched literally in a LIKE. */
  private static function like(string $v): string
  {
    return '%' . addcslashes($v, '%_\\') . '%';
  }

  /** @return array{0: string, 1: array} WHERE clause and its parameters */
  private static function where(array $f): array
  {
    $sql = ' WHERE 1 = 1';
    $p   = [];
    if (($f['login'] ?? '') !== '') {
      $sql .= ' AND u.login LIKE :login';
      $p['login'] = self::like($f['login']);
    }
    if (($f['q'] ?? '') !== '') {
      $sql .= ' AND h.action LIKE :q';
      $p['q'] = self::like($f['q']);
    }
    if (($f['du'] ?? '') !== '') {
      $sql .= ' AND h.dateAction >= :du';
      $p['du'] = $f['du'] . ' 00:00:00';
    }
    if (($f['au'] ?? '') !== '') {          // inclusive end date
      $sql .= ' AND h.dateAction < :au';
      $p['au'] = date('Y-m-d', strtotime($f['au'] . ' +1 day'));
    }
    return [$sql, $p];
  }

  /** Filters: login, q (text in the action), du / au (Y-m-d). Newest first. */
  public static function search(array $filters, int $limit, int $offset = 0): array
  {
    [$where, $params] = self::where($filters);
    $stmt = Database::connect()->prepare(
      'SELECT h.idAction, h.action, h.dateAction, u.login
       FROM historiqueAction h
       JOIN utilisateur u ON u.idUser = h.idUser' . $where . '
       ORDER BY h.dateAction DESC, h.idAction DESC
       LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset
    );
    $stmt->execute($params);

    return $stmt->fetchAll();
  }

  public static function count(array $filters = []): int
  {
    [$where, $params] = self::where($filters);
    $stmt = Database::connect()->prepare(
      'SELECT COUNT(*) FROM historiqueAction h JOIN utilisateur u ON u.idUser = h.idUser' . $where
    );
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
  }
}
