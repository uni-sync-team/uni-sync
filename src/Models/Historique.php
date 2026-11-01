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
}
