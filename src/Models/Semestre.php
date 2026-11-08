<?php

namespace App\Models;

use App\Core\Database;

class Semestre
{
  public static function findById(int $id): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idSemestre, idFormation, libelle, numero FROM semestre WHERE idSemestre = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  public static function findByFormation(int $idFormation): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idSemestre, idFormation, libelle, numero
       FROM semestre WHERE idFormation = :id ORDER BY numero'
    );
    $stmt->execute(['id' => $idFormation]);

    return $stmt->fetchAll();
  }

  /** Every semester with its formation title (used by the "Résultats" selector). */
  public static function findAllWithFormation(): array
  {
    return Database::connect()->query(
      'SELECT s.idSemestre, s.libelle, s.numero, f.idFormation, f.intitule AS formation
       FROM semestre s
       JOIN formation f ON f.idFormation = s.idFormation
       ORDER BY f.intitule, s.numero'
    )->fetchAll();
  }

  public static function numeroExists(int $idFormation, int $numero): bool
  {
    $stmt = Database::connect()->prepare(
      'SELECT 1 FROM semestre WHERE idFormation = :f AND numero = :n LIMIT 1'
    );
    $stmt->execute(['f' => $idFormation, 'n' => $numero]);

    return $stmt->fetchColumn() !== false;
  }

  public static function create(array $data): int
  {
    $pdo  = Database::connect();
    $stmt = $pdo->prepare(
      'INSERT INTO semestre (idFormation, libelle, numero) VALUES (:idFormation, :libelle, :numero)'
    );
    $stmt->execute([
      'idFormation' => $data['idFormation'],
      'libelle'     => $data['libelle'],
      'numero'      => $data['numero'],
    ]);

    return (int) $pdo->lastInsertId();
  }
}
