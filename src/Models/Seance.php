<?php

namespace App\Models;

use App\Core\Database;

class Seance
{
  public const TYPES = ['Cours', 'TD', 'TP'];

  private const COLUMNS = ['idModule', 'idEnseignant', 'idSalle', 'dateDebut', 'dateFin', 'typeSeance'];

  private const SELECT = 's.idSeance, s.idModule, s.idEnseignant, s.idSalle, s.dateDebut, s.dateFin, s.typeSeance,
                          m.code AS moduleCode, m.intitule AS module,
                          e.nom AS enseignantNom, e.prenom AS enseignantPrenom,
                          sa.nom AS salle';

  private const FROM = 'FROM seance s
                        JOIN module m      ON m.idModule      = s.idModule
                        JOIN enseignant e  ON e.idEnseignant  = s.idEnseignant
                        JOIN salle sa      ON sa.idSalle      = s.idSalle';

  public static function findById(int $id): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT ' . self::SELECT . ' ' . self::FROM . ' WHERE s.idSeance = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  /** @return array{0: string, 1: array} WHERE clause and its parameters */
  private static function where(array $f): array
  {
    $sql = ' WHERE 1 = 1';
    $p   = [];
    if ((int) ($f['idModule'] ?? 0) > 0) {
      $sql .= ' AND s.idModule = :idModule';
      $p['idModule'] = (int) $f['idModule'];
    }
    if ((int) ($f['idEnseignant'] ?? 0) > 0) {
      $sql .= ' AND s.idEnseignant = :idEnseignant';
      $p['idEnseignant'] = (int) $f['idEnseignant'];
    }
    if ((int) ($f['idSalle'] ?? 0) > 0) {
      $sql .= ' AND s.idSalle = :idSalle';
      $p['idSalle'] = (int) $f['idSalle'];
    }
    if (($f['du'] ?? '') !== '') {
      $sql .= ' AND s.dateDebut >= :du';
      $p['du'] = $f['du'] . ' 00:00:00';
    }
    if (($f['au'] ?? '') !== '') {          // inclusive end date
      $sql .= ' AND s.dateDebut < :au';
      $p['au'] = date('Y-m-d', strtotime($f['au'] . ' +1 day'));
    }
    return [$sql, $p];
  }

  /** Filters: idModule, idEnseignant, idSalle, du / au (Y-m-d). Soonest first. */
  public static function search(array $filters, int $limit, int $offset = 0): array
  {
    [$where, $params] = self::where($filters);
    $stmt = Database::connect()->prepare(
      'SELECT ' . self::SELECT . ' ' . self::FROM . $where . '
       ORDER BY s.dateDebut, s.idSeance
       LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset
    );
    $stmt->execute($params);

    return $stmt->fetchAll();
  }

  public static function count(array $filters = []): int
  {
    [$where, $params] = self::where($filters);
    $stmt = Database::connect()->prepare('SELECT COUNT(*) ' . self::FROM . $where);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
  }

  /**
   * Another session overlapping [debut, fin) in the same room or with the same teacher.
   * @return 'salle'|'enseignant'|null
   */
  public static function conflict(int $idSalle, int $idEnseignant, string $debut, string $fin, ?int $excludeId = null): ?string
  {
    $pdo = Database::connect();
    foreach (['salle' => $idSalle, 'enseignant' => $idEnseignant] as $kind => $value) {
      $col  = $kind === 'salle' ? 'idSalle' : 'idEnseignant';
      $stmt = $pdo->prepare(
        "SELECT 1 FROM seance
         WHERE $col = :v AND dateDebut < :fin AND dateFin > :debut
           AND (:ex IS NULL OR idSeance <> :ex2)
         LIMIT 1"
      );
      $stmt->execute(['v' => $value, 'debut' => $debut, 'fin' => $fin, 'ex' => $excludeId, 'ex2' => $excludeId]);
      if ($stmt->fetchColumn() !== false) {
        return $kind;
      }
    }
    return null;
  }

  public static function create(array $data): int
  {
    $pdo  = Database::connect();
    $stmt = $pdo->prepare(
      'INSERT INTO seance (idModule, idEnseignant, idSalle, dateDebut, dateFin, typeSeance)
       VALUES (:idModule, :idEnseignant, :idSalle, :dateDebut, :dateFin, :typeSeance)'
    );
    $stmt->execute(array_intersect_key($data, array_flip(self::COLUMNS)));

    return (int) $pdo->lastInsertId();
  }

  public static function update(int $id, array $data): bool
  {
    $data = array_intersect_key($data, array_flip(self::COLUMNS));
    if (!$data) {
      return false;
    }

    $set  = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
    $stmt = Database::connect()->prepare("UPDATE seance SET $set WHERE idSeance = :id");
    $stmt->execute($data + ['id' => $id]);

    return $stmt->rowCount() > 0;
  }

  public static function delete(int $id): bool
  {
    $stmt = Database::connect()->prepare('DELETE FROM seance WHERE idSeance = :id');
    $stmt->execute(['id' => $id]);

    return $stmt->rowCount() > 0;
  }
}
