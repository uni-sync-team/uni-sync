<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Models\ReclamationNote;
use App\Services\Audit;
use App\Services\Notifier;

class AdminReclamationController {
  private const PER_PAGE = 15;

  public static function index(): void
  {
    $filters = ['q' => self::param('q'), 'statut' => self::param('statut')];
    if (!in_array($filters['statut'], ReclamationNote::STATUTS, true)) {
      $filters['statut'] = '';
    }

    $total = ReclamationNote::count($filters);
    $pages = max(1, (int) ceil($total / self::PER_PAGE));
    $page  = min(max(1, (int) self::param('p')), $pages);

    AdminController::render('admin/reclamations/index', [
      'active'    => 'reclamations',
      'pageTitle' => 'Réclamations',
      'rows'      => ReclamationNote::search($filters, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
      'filters'   => $filters,
      'query'     => array_filter($filters, fn($v) => $v !== ''),
      'basePath'  => '/admin/reclamations',
      'total'     => $total,
      'page'      => $page,
      'pages'     => $pages,
    ]);
  }

  public static function accepter(string $id): void
  {
    self::decide((int) $id, ReclamationNote::ACCEPTEE, 'acceptée');
  }

  public static function rejeter(string $id): void
  {
    self::decide((int) $id, ReclamationNote::REJETEE, 'rejetée');
  }

  private static function decide(int $id, string $statut, string $label): void
  {
    $reclamation = ReclamationNote::findById($id);
    if ($reclamation === null) {
      http_response_code(404);
      echo '404 - Réclamation introuvable';
      return;
    }

    if (!ReclamationNote::decide($id, $statut)) {
      Flash::set('error', 'Cette réclamation a déjà été traitée.');
      self::redirect();
    }

    Audit::log("Réclamation n°$id ({$reclamation['module']}, {$reclamation['nom']} {$reclamation['prenom']}) : $label");
    Notifier::reclamation((int) $reclamation['idUser'], $reclamation['module'], $reclamation['type'], $statut);
    Flash::set('success', "Réclamation $label. L’étudiant a été notifié.");
    self::redirect();
  }

  private static function redirect(): never
  {
    header('Location: /admin/reclamations');
    exit;
  }

  private static function param(string $key): string
  {
    $v = $_GET[$key] ?? '';
    return is_string($v) ? trim($v) : '';
  }
}
