<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Core\Session;
use App\Models\Administrateur;
use App\Models\DemandeAdministrative as Demande;
use App\Services\Audit;
use App\Services\Notifier;

class AdminDemandeController {
  private const PER_PAGE = 20;
  private const STATUTS  = [Demande::STATUT_EN_ATTENTE, Demande::STATUT_TRAITEE, Demande::STATUT_REFUSEE];

  public static function index(): void
  {
    $demandes = new Demande();
    $filters  = ['q' => self::param('q'), 'statut' => self::param('statut')];
    if (!in_array($filters['statut'], self::STATUTS, true)) {
      $filters['statut'] = '';
    }

    $total = $demandes->countSearch($filters);
    $pages = max(1, (int) ceil($total / self::PER_PAGE));
    $page  = min(max(1, (int) self::param('p')), $pages);

    AdminController::render('admin/demandes/index', [
      'active'    => 'demandes',
      'pageTitle' => 'Demandes administratives',
      'rows'      => $demandes->search($filters, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
      'filters'   => $filters,
      'query'     => array_filter($filters, fn($v) => $v !== ''),
      'basePath'  => '/admin/demandes',
      'total'     => $total,
      'page'      => $page,
      'pages'     => $pages,
    ]);
  }

  public static function traiter(string $id): void
  {
    self::decide((int) $id, Demande::STATUT_TRAITEE, 'traitée');
  }

  public static function refuser(string $id): void
  {
    self::decide((int) $id, Demande::STATUT_REFUSEE, 'refusée');
  }

  private static function decide(int $id, string $statut, string $label): void
  {
    $demandes = new Demande();
    $demande  = $demandes->findWithStudent($id);
    if ($demande === null) {
      http_response_code(404);
      echo '404 - Demande introuvable';
      return;
    }

    // The handling admin is recorded; an account without an administrateur profile still works (idAdmin stays empty).
    $admin = (new Administrateur())->findByUser((int) Session::userId());

    if (!$demandes->decide($id, $admin['idAdmin'] ?? null, $statut)) {
      Flash::set('error', 'Cette demande a déjà été traitée.');
      self::redirect();
    }

    Audit::log("Demande n°$id ({$demande['typeDemande']}) de {$demande['nom']} {$demande['prenom']} : $label");
    Notifier::demande((int) $demande['idUser'], $demande['typeDemande'], $statut);
    Flash::set('success', "Demande $label. L’étudiant a été notifié.");
    self::redirect();
  }

  private static function redirect(): never
  {
    header('Location: /admin/demandes');
    exit;
  }

  private static function param(string $key): string
  {
    $v = $_GET[$key] ?? '';
    return is_string($v) ? trim($v) : '';
  }
}
