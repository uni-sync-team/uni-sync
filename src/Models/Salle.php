<?php

namespace App\Models;

use App\Core\Database;

class Salle
{
  /** Allowed room types (each must fit in VARCHAR(50)). Adjust to your faculty. */
  public const TYPES = ['Salle de cours', 'Amphithéâtre', 'Laboratoire', 'Salle informatique'];

  private const COLUMNS = ['nom', 'capacite', 'typeSalle'];

  public static function findById(int $id): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idSalle, nom, capacite, typeSalle FROM salle WHERE idSalle = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  public static function findAll(): array
  {
    return Database::connect()
      ->query('SELECT idSalle, nom, capacite, typeSalle FROM salle ORDER BY nom')
      ->fetchAll();
  }

  public static function create(array $data): int
  {
    $pdo  = Database::connect();
    $stmt = $pdo->prepare(
      'INSERT INTO salle (nom, capacite, typeSalle) VALUES (:nom, :capacite, :typeSalle)'
    );
    $stmt->execute([
      'nom'       => $data['nom'],
      'capacite'  => $data['capacite'],
      'typeSalle' => $data['typeSalle'],
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
    $stmt = Database::connect()->prepare("UPDATE salle SET $set WHERE idSalle = :id");
    $stmt->execute($data + ['id' => $id]);

    return $stmt->rowCount() > 0;
  }
}
