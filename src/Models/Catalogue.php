<?php

class Catalogue {
  /** Départements → filières → formations, as nested arrays. */
  public static function departements(): array
  {
    $rows = Database::connect()->query(
      'SELECT d.idDepartement, d.nom AS dNom, d.description AS dDesc,
              f.idFiliere, f.nom AS fNom, f.description AS fDesc,
              fo.idFormation, fo.intitule, fo.niveau, fo.duree
       FROM departement d
       LEFT JOIN filiere f    ON f.idDepartement = d.idDepartement
       LEFT JOIN formation fo ON fo.idFiliere = f.idFiliere
       ORDER BY d.nom, f.nom, fo.niveau, fo.intitule'
    )->fetchAll();

    $out = [];
    foreach ($rows as $r) {
      $d = $r['idDepartement'];
      $out[$d] ??= ['nom' => $r['dNom'], 'description' => $r['dDesc'], 'filieres' => []];
      if ($r['idFiliere'] === null) {
        continue;
      }
      $f = $r['idFiliere'];
      $out[$d]['filieres'][$f] ??= ['nom' => $r['fNom'], 'description' => $r['fDesc'], 'formations' => []];
      if ($r['idFormation'] !== null) {
        $out[$d]['filieres'][$f]['formations'][] =
          ['intitule' => $r['intitule'], 'niveau' => $r['niveau'], 'duree' => $r['duree']];
      }
    }

    foreach ($out as &$d) {
      $d['filieres'] = array_values($d['filieres']);
    }
    return array_values($out);
  }

  /** Key figures for the home page. */
  public static function stats(): array
  {
    $row = Database::connect()->query(
      'SELECT (SELECT COUNT(*) FROM etudiant)   AS etudiants,
              (SELECT COUNT(*) FROM enseignant) AS enseignants,
              (SELECT COUNT(*) FROM filiere)    AS filieres,
              (SELECT COUNT(*) FROM formation)  AS formations'
    )->fetch();

    return array_map('intval', $row);
  }

  /** Label of the academic year running today, e.g. "2026-2027", or null. */
  public static function anneeCourante(): ?string
  {
    $label = Database::connect()->query(
      'SELECT libelle FROM anneeUniversitaire
       WHERE CURRENT_DATE BETWEEN dateDebut AND dateFin
       ORDER BY dateDebut DESC LIMIT 1'
    )->fetchColumn();

    return $label === false ? null : $label;
  }
}
