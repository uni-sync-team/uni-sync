<?php

namespace App\Models;

use App\Core\Database;

class ReclamationNote {
  public const EN_ATTENTE = 'EN_ATTENTE';
  public const ACCEPTEE   = 'ACCEPTEE';
  public const REJETEE    = 'REJETEE';
  public const STATUTS    = [self::EN_ATTENTE, self::ACCEPTEE, self::REJETEE];

  private const FROM = ' FROM reclamationNote r
       JOIN etudiant e   ON e.idEtudiant = r.idEtudiant
       JOIN evaluation ev ON ev.idEvaluation = r.idEvaluation
       JOIN module m     ON m.idModule = ev.idModule';

  /** @return array{0: string, 1: array} */
  private static function where(array $f): array
  {
    $sql = ' WHERE 1 = 1';
    $p   = [];
    if (($f['q'] ?? '') !== '') {
      $sql .= ' AND (e.nom LIKE :q1 OR e.prenom LIKE :q2 OR e.cne LIKE :q3 OR m.intitule LIKE :q4 OR m.code LIKE :q5)';
      $like = '%' . addcslashes($f['q'], '%_\\') . '%';
      foreach (['q1', 'q2', 'q3', 'q4', 'q5'] as $k) {
        $p[$k] = $like;
      }
    }
    if (in_array($f['statut'] ?? '', self::STATUTS, true)) {
      $sql .= ' AND r.statut = :statut';
      $p['statut'] = $f['statut'];
    }
    return [$sql, $p];
  }

  /** Pending complaints first, then newest. Includes the student's current grade. */
  public static function search(array $filters, int $limit, int $offset = 0): array
  {
    [$where, $params] = self::where($filters);
    $stmt = Database::connect()->prepare(
      'SELECT r.idReclamation, r.motif, r.statut,
              e.nom, e.prenom, e.cne,
              ev.type, ev.dateEvaluation, ev.bareme,
              m.code, m.intitule AS module,
              (SELECT n.valeur FROM note n
                WHERE n.idEtudiant = r.idEtudiant AND n.idEvaluation = r.idEvaluation
                ORDER BY n.idNote DESC LIMIT 1) AS valeur'
      . self::FROM . $where . '
       ORDER BY (r.statut = \'EN_ATTENTE\') DESC, r.idReclamation DESC
       LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset
    );
    $stmt->execute($params);

    return $stmt->fetchAll();
  }

  public static function count(array $filters = []): int
  {
    [$where, $params] = self::where($filters);
    $stmt = Database::connect()->prepare('SELECT COUNT(*)' . self::FROM . $where);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
  }

  /** The complaint with what is needed to notify the student. */
  public static function findById(int $id): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT r.idReclamation, r.statut, e.idUser, e.nom, e.prenom,
              ev.type, m.intitule AS module'
      . self::FROM . ' WHERE r.idReclamation = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  /** Decides a pending complaint. False if it was already decided (or does not exist). */
  public static function decide(int $id, string $statut): bool
  {
    $stmt = Database::connect()->prepare(
      'UPDATE reclamationNote SET statut = :statut WHERE idReclamation = :id AND statut = :pending'
    );
    $stmt->execute(['statut' => $statut, 'id' => $id, 'pending' => self::EN_ATTENTE]);

    return $stmt->rowCount() > 0;
  }

  // ----- student side -----

  /** A student's own complaints, newest first (RG4: always filtered by the student id). */
  public static function findByStudent(int $idEtudiant): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT r.idReclamation, r.motif, r.statut, ev.type, ev.dateEvaluation, ev.bareme,
              m.code, m.intitule AS module,
              (SELECT n.valeur FROM note n
                WHERE n.idEtudiant = r.idEtudiant AND n.idEvaluation = r.idEvaluation
                ORDER BY n.idNote DESC LIMIT 1) AS valeur'
      . ' FROM reclamationNote r
          JOIN evaluation ev ON ev.idEvaluation = r.idEvaluation
          JOIN module m ON m.idModule = ev.idModule
          WHERE r.idEtudiant = :id
          ORDER BY r.idReclamation DESC'
    );
    $stmt->execute(['id' => $idEtudiant]);

    return $stmt->fetchAll();
  }

  public static function hasPending(int $idEtudiant, int $idEvaluation): bool
  {
    $stmt = Database::connect()->prepare(
      'SELECT 1 FROM reclamationNote WHERE idEtudiant = :e AND idEvaluation = :v AND statut = :s LIMIT 1'
    );
    $stmt->execute(['e' => $idEtudiant, 'v' => $idEvaluation, 's' => self::EN_ATTENTE]);

    return $stmt->fetchColumn() !== false;
  }

  public static function create(int $idEtudiant, int $idEvaluation, string $motif): int
  {
    $pdo = Database::connect();
    $pdo->prepare('INSERT INTO reclamationNote (idEtudiant, idEvaluation, motif) VALUES (:e, :v, :m)')
        ->execute(['e' => $idEtudiant, 'v' => $idEvaluation, 'm' => $motif]);

    return (int) $pdo->lastInsertId();
  }
}
