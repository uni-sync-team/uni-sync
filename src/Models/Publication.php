<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Publication {
  /**
   * Latest announcements that are visible today: active, already published,
   * and not expired. Newest first.
   */
  public static function findActive(int $limit = 4): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idPublication, titre, contenu, datePublication
       FROM publication
       WHERE actif = 1
         AND datePublication <= CURRENT_DATE
         AND (dateExpiration IS NULL OR dateExpiration >= CURRENT_DATE)
       ORDER BY datePublication DESC, idPublication DESC
       LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
  }

  // ----- admin screen -----

  /** Label shown in the admin list: Active, Programmée, Expirée or Désactivée. */
  public static function statut(array $row): string
  {
    $today = date('Y-m-d');
    if (!(int) $row['actif']) {
      return 'Désactivée';
    }
    if ($row['datePublication'] > $today) {
      return 'Programmée';
    }
    if ($row['dateExpiration'] !== null && $row['dateExpiration'] < $today) {
      return 'Expirée';
    }
    return 'Active';
  }

  private static function like(string $v): string
  {
    return '%' . addcslashes($v, '%_\\') . '%';
  }

  /** All announcements (active or not), newest first, optionally filtered on the title. */
  public static function search(string $q, int $limit, int $offset = 0): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT p.idPublication, p.titre, p.datePublication, p.dateExpiration, p.actif, a.nom, a.prenom
       FROM publication p
       JOIN administrateur a ON a.idAdmin = p.idAdmin
       WHERE (:all = 1 OR p.titre LIKE :q)
       ORDER BY p.datePublication DESC, p.idPublication DESC
       LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset
    );
    $stmt->execute(['all' => $q === '' ? 1 : 0, 'q' => self::like($q)]);

    return $stmt->fetchAll();
  }

  public static function count(string $q = ''): int
  {
    $stmt = Database::connect()->prepare('SELECT COUNT(*) FROM publication WHERE (:all = 1 OR titre LIKE :q)');
    $stmt->execute(['all' => $q === '' ? 1 : 0, 'q' => self::like($q)]);

    return (int) $stmt->fetchColumn();
  }

  public static function findById(int $id): ?array
  {
    $stmt = Database::connect()->prepare(
      'SELECT idPublication, idAdmin, titre, contenu, datePublication, dateExpiration, actif
       FROM publication WHERE idPublication = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  public static function create(int $idAdmin, array $d): int
  {
    $pdo = Database::connect();
    $pdo->prepare(
      'INSERT INTO publication (idAdmin, titre, contenu, datePublication, dateExpiration, actif)
       VALUES (:admin, :titre, :contenu, :pub, :exp, :actif)'
    )->execute([
      'admin' => $idAdmin, 'titre' => $d['titre'], 'contenu' => $d['contenu'],
      'pub' => $d['datePublication'], 'exp' => $d['dateExpiration'], 'actif' => (int) $d['actif'],
    ]);

    return (int) $pdo->lastInsertId();
  }

  /** The author (idAdmin) is never changed. */
  public static function update(int $id, array $d): bool
  {
    $stmt = Database::connect()->prepare(
      'UPDATE publication
       SET titre = :titre, contenu = :contenu, datePublication = :pub, dateExpiration = :exp, actif = :actif
       WHERE idPublication = :id'
    );
    $stmt->execute([
      'titre' => $d['titre'], 'contenu' => $d['contenu'], 'pub' => $d['datePublication'],
      'exp' => $d['dateExpiration'], 'actif' => (int) $d['actif'], 'id' => $id,
    ]);

    return $stmt->rowCount() > 0;
  }

  public static function delete(int $id): bool
  {
    $stmt = Database::connect()->prepare('DELETE FROM publication WHERE idPublication = :id');
    $stmt->execute(['id' => $id]);

    return $stmt->rowCount() > 0;
  }
}
