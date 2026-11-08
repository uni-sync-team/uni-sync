<?php

namespace App\Models;

use App\Core\Database;

class ResultatSemestre
{
  public const DECISIONS = ['VALIDE', 'NON_VALIDE', 'AJOURNE'];
  public const SEUIL     = 10.0;

  public static function findBySemestre(int $idSemestre): array
  {
    $stmt = Database::connect()->prepare(
      'SELECT r.idResultat, r.idEtudiant, r.moyenne, r.decision, e.cne, e.nom, e.prenom
       FROM resultatSemestre r
       JOIN etudiant e ON e.idEtudiant = r.idEtudiant
       WHERE r.idSemestre = :id
       ORDER BY e.nom, e.prenom'
    );
    $stmt->execute(['id' => $idSemestre]);

    return $stmt->fetchAll();
  }

  /**
   * Recomputes the semester average of every student who has at least one grade
   * (NORMALE session): module average weighted by evaluation coefficient (out of 20),
   * then semester average weighted by module coefficient. Decision: VALIDE if >= 10.
   * @return int number of students computed
   */
  public static function calculer(int $idSemestre): int
  {
    $pdo = Database::connect();

    $stmt = $pdo->prepare(
      "SELECT mm.idEtudiant,
              SUM(mm.moyenne * m.coefficient) / SUM(m.coefficient) AS moyenne
       FROM (
         SELECT n.idEtudiant, ev.idModule,
                SUM((n.valeur / ev.bareme) * 20 * ev.coefficient) / SUM(ev.coefficient) AS moyenne
         FROM note n
         JOIN evaluation ev ON ev.idEvaluation = n.idEvaluation
         WHERE n.valeur IS NOT NULL AND n.session = 'NORMALE'
         GROUP BY n.idEtudiant, ev.idModule
       ) mm
       JOIN module m ON m.idModule = mm.idModule
       WHERE m.idSemestre = :id
       GROUP BY mm.idEtudiant"
    );
    $stmt->execute(['id' => $idSemestre]);
    $rows = $stmt->fetchAll();

    $upsert = $pdo->prepare(
      'INSERT INTO resultatSemestre (idEtudiant, idSemestre, moyenne, decision)
       VALUES (:e, :s, :m, :d)
       ON DUPLICATE KEY UPDATE moyenne = VALUES(moyenne), decision = VALUES(decision)'
    );

    $pdo->beginTransaction();
    try {
      foreach ($rows as $r) {
        $moy = round((float) $r['moyenne'], 2);
        $upsert->execute([
          'e' => $r['idEtudiant'],
          's' => $idSemestre,
          'm' => $moy,
          'd' => $moy >= self::SEUIL ? 'VALIDE' : 'NON_VALIDE',
        ]);
      }
      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }

    return count($rows);
  }
}
