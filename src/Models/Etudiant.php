<?php

namespace App\Models;
 
use App\Core\Database;

class Etudiant {
  private const COLUMNS = ['idUser', 'cne', 'nom', 'prenom', 'dateNaissance', 'telephone'];
    
  public static function findById(int $id): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idEtudiant, idUser, cne, nom, prenom, dateNaissance, telephone
       FROM etudiant
       WHERE idEtudiant = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  public static function findByUser(int $idUser): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idEtudiant, idUser, cne, nom, prenom, dateNaissance, telephone
       FROM etudiant
       WHERE idUser = :id
       LIMIT 1'
    );
    $stmt->execute(['id' => $idUser]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  public static function findByCne(string $cne): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idEtudiant, idUser, cne, nom, prenom, dateNaissance, telephone
       FROM etudiant
       WHERE cne = :cne
       LIMIT 1'
    );
    $stmt->execute(['cne' => $cne]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  public static function findAll(?string $q = null, int $limit = 100): array
  {
    $sql = 'SELECT e.idEtudiant, e.idUser, e.cne, e.nom, e.prenom, e.dateNaissance, e.telephone, u.actif
            FROM etudiant e
            JOIN utilisateur u ON u.idUser = e.idUser';
    $params = [];
    if ($q !== null && $q !== '') {
      $sql .= ' WHERE e.nom LIKE :q1 OR e.prenom LIKE :q2';
      $params = ['q1' => "%$q%", 'q2' => "%$q%"];
    }
    $stmt = Database::connect()->prepare($sql . ' ORDER BY e.cne LIMIT ' . (int) $limit);
    $stmt->execute($params);

    return $stmt->fetchAll();
  }

  /** tous les etudiants triés par nom. */
  // OLD VERSION OF FIND ALL
  public static function findAll_(int $limit = 50): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT e.idUser, e.login, u.email, u.actif, u.dateCreation,
      u.login, u.email, u.actif
      FROM etudiant e
      JOIN utilisateur u ON u.idUser = e.idUser
      ORDER BY e.nom, e.prenom
      LIMIT :limit'
    );

    $stmt->bindParam(":limit", $limit, \PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
  }

  /** recherche par nom ou CNE. */
  public static function search(string $term, int $limit = 50): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idEtudiant, cne, nom, prenom, telephone
       FROM etudiant
       WHERE nom LIKE :t1 OR prenom LIKE :t2 OR cne LIKE :t3
       ORDER BY nom, prenom
       LIMIT :limit'
    );
    $like = '%' . $term . '%';
    $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
    $stmt->execute(['t1' => $like, 't2' => $like, 't3' => $like]);

    return $stmt->fetchAll() ?: null;
  }

  public static function create(array $data): int
  {
    $pdo  = Database::connect();
    $stmt = $pdo->prepare(
      'INSERT INTO etudiant (idUser, cne, nom, prenom, dateNaissance, telephone)
       VALUES (:idUser, :cne, :nom, :prenom, :dateNaissance, :telephone)'
    );
    $stmt->execute([
      'idUser'        => $data['idUser'],
      'cne'           => $data['cne'],
      'nom'           => $data['nom'],
      'prenom'        => $data['prenom'],
      'dateNaissance' => $data['dateNaissance'] ?? null,
      'telephone'     => $data['telephone'] ?? null,
    ]);

    return (int) $pdo->lastInsertId();
  }

  // OLD VERSION OF UPDATE
  public static function _update(int $id, array $data): bool
  {
    $data = array_intersect_key($data, array_flip(self::COLUMNS));
    if (!$data) {
      return false;
    }

    $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
    $stmt = Database::connect()->prepare("UPDATE etudiant SET $set WHERE idEtudiant = :id");
    $stmt->execute($data + ['id' => $id]);

    return $stmt->rowCount() > 0;
  }

  public static function update(int $id, array $data, bool $actif): bool
  {
    $data = array_intersect_key($data, array_flip(self::COLUMNS));
    if (!$data) {
      return false;
    }

    $pdo = Database::connect();
    $pdo->beginTransaction();

    try {
      $set  = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
      $stmt = $pdo->prepare("UPDATE etudiant SET $set WHERE idEtudiant = :id");
      $stmt->execute($data + ['id' => $id]);

      $stmt = $pdo->prepare("
        UPDATE utilisateur u
        JOIN etudiant e ON e.idUser = u.idUser
        SET u.actif = :actif
        WHERE e.idEtudiant = :id
      ");
      $stmt->execute(['actif' => (int) $actif, 'id' => $id]);

      $pdo->commit();
      return true;
    } catch (\PDOException) {
      $pdo->rollBack();
      return false;   // e.g. duplicate CNE inserted by someone else between validation and update
    }
  }

  public static function delete(int $id): bool
  {
    $stmt = Database::connect()->prepare('DELETE FROM etudiant WHERE idEtudiant = :id');
    $stmt->execute(['id' => $id]);

    return $stmt->rowCount() > 0;
  }

  public static function count(): int
  {
    return (int) Database::connect()->query('SELECT COUNT(*) FROM etudiant')->fetchColumn();
  }

  public static function inscriptions(int $idEtudiant): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT i.idInscription, i.dateInscription, i.statut,
              f.idFormation, f.intitule AS formation, f.niveau,
              a.libelle AS annee
       FROM inscription i
       JOIN formation f          ON f.idFormation = i.idFormation
       JOIN anneeUniversitaire a ON a.idAnnee = i.idAnnee
       WHERE i.idEtudiant = :id
       ORDER BY a.dateDebut DESC'
    );
    $stmt->execute(['id' => $idEtudiant]);

    return $stmt->fetchAll();
  }

  public static function notes(int $idEtudiant): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT n.idNote, n.valeur, n.session,
              ev.idEvaluation, ev.type, ev.dateEvaluation, ev.bareme,
              m.code, m.intitule AS module
       FROM note n
       JOIN evaluation ev ON ev.idEvaluation = n.idEvaluation
       JOIN module m      ON m.idModule = ev.idModule
       WHERE n.idEtudiant = :id
       ORDER BY ev.dateEvaluation DESC'
    );
    $stmt->execute(['id' => $idEtudiant]);

    return $stmt->fetchAll();
  }

  public static function resultats(int $idEtudiant): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT r.idResultat, r.moyenne, r.decision,
              s.idSemestre, s.libelle AS semestre, s.numero
       FROM resultatSemestre r
       JOIN semestre s ON s.idSemestre = r.idSemestre
       WHERE r.idEtudiant = :id
       ORDER BY s.numero'
    );
    $stmt->execute(['id' => $idEtudiant]);

    return $stmt->fetchAll();
  }

  public static function demandes(int $idEtudiant): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idDemande, typeDemande, dateDemande, statut
       FROM demandeAdministrative
       WHERE idEtudiant = :id
       ORDER BY dateDemande DESC'
    );
    $stmt->execute(['id' => $idEtudiant]);

    return $stmt->fetchAll();
  }

  public static function reclamations(int $idEtudiant): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT rn.idReclamation, rn.motif, rn.statut,
              ev.type, m.intitule AS module
       FROM reclamationNote rn
       JOIN evaluation ev ON ev.idEvaluation = rn.idEvaluation
       JOIN module m      ON m.idModule = ev.idModule
       WHERE rn.idEtudiant = :id
       ORDER BY rn.idReclamation DESC'
    );
    $stmt->execute(['id' => $idEtudiant]);

    return $stmt->fetchAll();
  }

  public static function isActive(int $id) {
    $stmt = Database::connect()->prepare("
      SELECT u.actif FROM utilisateur u
      JOIN etudiant e ON e.idUser = u.idUser 
      WHERE e.idEtudiant = :id
    ");
    $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
    $stmt->execute();
    return (bool) $stmt->fetchColumn();
  }

  public static function cneTaken(string $cne, ?int $exceptId = null): bool
  {
    $stmt = Database::connect()->prepare(
      'SELECT 1 FROM etudiant WHERE cne = ? AND (? IS NULL OR idEtudiant <> ?) LIMIT 1'
    );
    $stmt->execute([$cne, $exceptId, $exceptId]);
    return (bool) $stmt->fetchColumn();
  }

  public static function createWithUser(array $d): int
  {
    $pdo = Database::connect();
    $pdo->beginTransaction();

    try {
      $stmt = $pdo->prepare("
        INSERT INTO utilisateur (login, email, motDePasse)
        VALUES (:login, :email, :pwd)
      ");
      $stmt->execute([
        'login' => $d['login'],
        'email' => $d['email'],
        'pwd'   => password_hash($d['password'], PASSWORD_DEFAULT),
      ]);
      $idUser = (int) $pdo->lastInsertId();

      $stmt = $pdo->prepare("
        INSERT INTO userRole (idUser, idRole)
        SELECT :idUser, idRole FROM role WHERE nom = 'etudiant'
      ");
      $stmt->execute(['idUser' => $idUser]);

      if ($stmt->rowCount() === 0) {
        // no role named 'etudiant' in the table: abort so we never create an account without a role
        throw new \RuntimeException("Le rôle 'etudiant' n'existe pas dans la table role.");
      }

      $stmt = $pdo->prepare("
        INSERT INTO etudiant (idUser, cne, nom, prenom, dateNaissance, telephone)
        VALUES (:idUser, :cne, :nom, :prenom, :dateNaissance, :telephone)
      ");

      $stmt->execute([
        'idUser'        => $idUser,
        'cne'           => $d['cne'],
        'nom'           => $d['nom'],
        'prenom'        => $d['prenom'],
        'dateNaissance' => $d['dateNaissance'],
        'telephone'     => $d['telephone'],
      ]);
      $idEtudiant = (int) $pdo->lastInsertId();

      $pdo->commit();
      return $idEtudiant;
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;   // store() catches PDOException and shows the error
    }
  }
}
