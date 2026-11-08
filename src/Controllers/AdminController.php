<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Core\View;
use App\Models\AdminStats;
use App\Models\Historique;

class AdminController {
  /** Sidebar: [key, label, url]. A null url means the section is not built yet. */
  private const SECTIONS = [
    ['dashboard',    'Tableau de bord',       '/admin'],
    ['utilisateurs', 'Utilisateurs',          '/admin/utilisateurs'],
    ['etudiants',    'Étudiants',             '/admin/etudiants'],
    ['enseignants',  'Enseignants',           '/admin/enseignants'],
    ['departements', 'Départements',          '/admin/departements'],
    ['formations',   'Formations et filières', '/admin/formations'],
    ['salles',       'Salles',                '/admin/salles'],
    ['annee',        'Année universitaire',   '/admin/annees'],
    ['seances',      'Séances',               '/admin/seances'],
    ['examens',      'Examens',               '/admin/examens'],
    ['resultats',    'Résultats',             '/admin/resultats'],
    ['demandes',     'Demandes administratives', null],
    ['reclamations', 'Réclamations',          null],
    ['annonces',     'Annonces',              null],
    ['notifications', 'Notifications',        '/admin/notifications'],
    ['historique',   'Historique des actions', '/admin/historique'],
  ];

  public static function dashboard(): void
  {
    self::render('admin/dashboard', [
      'active'    => 'dashboard',
      'pageTitle' => 'Tableau de bord',
      'counters'  => AdminStats::counters(),
      'actions'   => Historique::recent(8),
    ]);
  }

  /** Renders a page inside the admin layout (sidebar + flash message). */
  public static function render(string $content, array $data = []): void
  {
    View::render('admin/layout', $data + [
      'content'  => $content,
      'flash'    => Flash::pull(),
      'sections' => self::SECTIONS,
    ]);
  }
}
