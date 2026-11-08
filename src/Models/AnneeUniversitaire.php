<?php

namespace App\Models;

use App\Core\Database;

class AnneeUniversitaire
{
  private const COLUMNS = ['libelle', 'dateDebut', 'dateFin'];

  public static function findById(int $id): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idAnnee, libelle, dateDebut, dateFin FROM anneeUniversitaire WHERE idAnnee = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  /** Most recent year first. */
  public static function findAll(): array
  {
    return Database::connect()
      ->query('SELECT idAnnee, libelle, dateDebut, dateFin FROM anneeUniversitaire ORDER BY dateDebut DESC')
      ->fetchAll();
  }

  public static function create(array $data): int
  {
    $pdo  = Database::connect();
    $stmt = $pdo->prepare(
      'INSERT INTO anneeUniversitaire (libelle, dateDebut, dateFin) VALUES (:libelle, :dateDebut, :dateFin)'
    );
    $stmt->execute([
      'libelle'   => $data['libelle'],
      'dateDebut' => $data['dateDebut'],
      'dateFin'   => $data['dateFin'],
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
    $stmt = Database::connect()->prepare("UPDATE anneeUniversitaire SET $set WHERE idAnnee = :id");
    $stmt->execute($data + ['id' => $id]);

    return $stmt->rowCount() > 0;
  }
}
