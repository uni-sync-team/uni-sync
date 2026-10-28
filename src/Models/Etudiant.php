<?php
class Etudiant {
  private const COLUMNS = ['idUser', 'cne', 'nom', 'prenom', 'dateNaissance', 'telephone'];

  public static function findById(int $id): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idEtudiant, idUser, cne, nom, prenom, dateNaissance, telephone
       FROM etudiant
       WHERE idEtudiant = :id
       LIMIT 1'
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

  /** tous les etudiants triés par nom. */
  public static function findAll(): array
  {
    return Database::connect()->query(
      'SELECT e.idEtudiant, e.idUser, e.cne, e.nom, e.prenom, e.dateNaissance, e.telephone,
              u.login, u.email, u.actif
       FROM etudiant e
       JOIN utilisateur u ON u.idUser = e.idUser
       ORDER BY e.nom, e.prenom'
    )->fetchAll();
  }

  /** recherche par nom ou CNE. */
  public static function search(string $term): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idEtudiant, cne, nom, prenom, telephone
       FROM etudiant
       WHERE nom LIKE :t1 OR prenom LIKE :t2 OR cne LIKE :t3
       ORDER BY nom, prenom
       LIMIT 50'
    );
    $like = '%' . $term . '%';
    $stmt->execute(['t1' => $like, 't2' => $like, 't3' => $like]);

    return $stmt->fetchAll();
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

  public static function update(int $id, array $data): bool
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
}