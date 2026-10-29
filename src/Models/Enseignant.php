<?php
namespace App\Models;
 
use App\Core\Database;
use PDO;
class Enseignant {
  private const COLUMNS = ['idDepartement', 'idUser', 'nom', 'prenom', 'grade'];

  public static function findById(int $id): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT e.idEnseignant, e.idDepartement, e.idUser, e.nom, e.prenom, e.grade,
              d.nom AS departement
       FROM enseignant e
       JOIN departement d ON d.idDepartement = e.idDepartement
       WHERE e.idEnseignant = :id
       LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  public static function findByUser(int $idUser): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT e.idEnseignant, e.idDepartement, e.idUser, e.nom, e.prenom, e.grade,
              d.nom AS departement
       FROM enseignant e
       JOIN departement d ON d.idDepartement = e.idDepartement
       WHERE e.idUser = :id
       LIMIT 1'
    );
    $stmt->execute(['id' => $idUser]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  /** All teachers with department and account info, ordered by name. */
  public static function findAll(): array
  {
    return Database::connect()->query(
      'SELECT e.idEnseignant, e.idDepartement, e.idUser, e.nom, e.prenom, e.grade,
              d.nom AS departement, u.login, u.email, u.actif
       FROM enseignant e
       JOIN departement d ON d.idDepartement = e.idDepartement
       JOIN utilisateur u ON u.idUser = e.idUser
       ORDER BY e.nom, e.prenom'
    )->fetchAll();
  }

  public static function findByDepartement(int $idDepartement): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idEnseignant, idUser, nom, prenom, grade
       FROM enseignant
       WHERE idDepartement = :id
       ORDER BY nom, prenom'
    );
    $stmt->execute(['id' => $idDepartement]);

    return $stmt->fetchAll();
  }

  /** Inserts a teacher, returns the new idEnseignant. */
  public static function create(array $data): int
  {
    $pdo  = Database::connect();
    $stmt = $pdo->prepare(
      'INSERT INTO enseignant (idDepartement, idUser, nom, prenom, grade)
       VALUES (:idDepartement, :idUser, :nom, :prenom, :grade)'
    );
    $stmt->execute([
      'idDepartement' => $data['idDepartement'],
      'idUser'        => $data['idUser'],
      'nom'           => $data['nom'],
      'prenom'        => $data['prenom'],
      'grade'         => $data['grade'] ?? null,
    ]);

    return (int) $pdo->lastInsertId();
  }

  /** Updates only the columns present in $data (idDepartement, idUser, nom, prenom, grade). */
  public static function update(int $id, array $data): bool
  {
    $data = array_intersect_key($data, array_flip(self::COLUMNS));
    if (!$data) {
      return false;
    }

    $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
    $stmt = Database::connect()->prepare("UPDATE enseignant SET $set WHERE idEnseignant = :id");
    $stmt->execute($data + ['id' => $id]);

    return $stmt->rowCount() > 0;
  }

  public static function delete(int $id): bool
  {
    $stmt = Database::connect()->prepare('DELETE FROM enseignant WHERE idEnseignant = :id');
    $stmt->execute(['id' => $id]);

    return $stmt->rowCount() > 0;
  }

  public static function count(): int
  {
    return (int) Database::connect()->query('SELECT COUNT(*) FROM enseignant')->fetchColumn();
  }

  /** Teaching sessions, optionally limited to a date range (Y-m-d H:i:s). */
  public static function seances(int $idEnseignant, ?string $from = null, ?string $to = null): array
  {
    $sql = 'SELECT s.idSeance, s.dateDebut, s.dateFin, s.typeSeance,
                   m.code, m.intitule AS module, sa.nom AS salle
            FROM seance s
            JOIN module m ON m.idModule = s.idModule
            JOIN salle sa ON sa.idSalle = s.idSalle
            WHERE s.idEnseignant = :id';
    $params = ['id' => $idEnseignant];

    if ($from !== null) {
      $sql .= ' AND s.dateDebut >= :from';
      $params['from'] = $from;
    }
    if ($to !== null) {
      $sql .= ' AND s.dateFin <= :to';
      $params['to'] = $to;
    }

    $stmt = Database::connect()->prepare($sql . ' ORDER BY s.dateDebut');
    $stmt->execute($params);

    return $stmt->fetchAll();
  }

  /** Modules taught (derived from the teacher's sessions). */
  public static function modules(int $idEnseignant): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT DISTINCT m.idModule, m.code, m.intitule, m.volumeHoraire, m.coefficient
       FROM module m
       JOIN seance s ON s.idModule = m.idModule
       WHERE s.idEnseignant = :id
       ORDER BY m.code'
    );
    $stmt->execute(['id' => $idEnseignant]);

    return $stmt->fetchAll();
  }
}
