<?php

namespace App\Models;

use App\Core\Database;

class Departement
{
  private const COLUMNS = ['nom', 'description'];

  public static function findById(int $id): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idDepartement, nom, description
       FROM departement
       WHERE idDepartement = :id
       LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  /** All departments, ordered by name (used by the teacher form select). */
  public static function findAll(): array
  {
    return Database::connect()
      ->query('SELECT idDepartement, nom, description FROM departement ORDER BY nom')
      ->fetchAll();
  }

  public static function create(array $data): int
  {
    $pdo  = Database::connect();
    $stmt = $pdo->prepare('INSERT INTO departement (nom, description) VALUES (:nom, :description)');
    $stmt->execute([
      'nom'         => $data['nom'],
      'description' => $data['description'] ?? null,
    ]);

    return (int) $pdo->lastInsertId();
  }

  public static function update(int $id, array $data): bool
  {
    $data = array_intersect_key($data, array_flip(self::COLUMNS));
    if (!$data) {
      return false;
    }

    $set  = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
    $stmt = Database::connect()->prepare("UPDATE departement SET $set WHERE idDepartement = :id");
    $stmt->execute($data + ['id' => $id]);

    return $stmt->rowCount() > 0;
  }

  public static function delete(int $id): bool
  {
    $stmt = Database::connect()->prepare('DELETE FROM departement WHERE idDepartement = :id');
    $stmt->execute(['id' => $id]);

    return $stmt->rowCount() > 0;
  }

  public static function count(): int
  {
    return (int) Database::connect()->query('SELECT COUNT(*) FROM departement')->fetchColumn();
  }

  public static function filieres(int $idDepartement): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idFiliere, nom, description
       FROM filiere
       WHERE idDepartement = :id
       ORDER BY nom'
    );
    $stmt->execute(['id' => $idDepartement]);

    return $stmt->fetchAll();
  }
}
