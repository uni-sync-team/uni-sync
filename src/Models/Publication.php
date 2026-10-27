<?php

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
}
