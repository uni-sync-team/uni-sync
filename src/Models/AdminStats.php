<?php

namespace App\Models;

use App\Core\Database;

class AdminStats {
  /** Counters for the admin dashboard. */
  public static function counters(): array
  {
    $row = Database::connect()->query(
      "SELECT (SELECT COUNT(*) FROM utilisateur)  AS utilisateurs,
              (SELECT COUNT(*) FROM etudiant)     AS etudiants,
              (SELECT COUNT(*) FROM enseignant)   AS enseignants,
              (SELECT COUNT(*) FROM demandeAdministrative WHERE statut = 'EN_ATTENTE') AS demandes,
              (SELECT COUNT(*) FROM reclamationNote WHERE statut = 'EN_ATTENTE')      AS reclamations,
              (SELECT COUNT(*) FROM publication
                WHERE actif = 1 AND datePublication <= CURRENT_DATE
                  AND (dateExpiration IS NULL OR dateExpiration >= CURRENT_DATE))    AS annonces"
    )->fetch();

    return array_map('intval', $row);
  }
}
