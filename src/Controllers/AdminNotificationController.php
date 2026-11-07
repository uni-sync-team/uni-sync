<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Models\Notification;
use App\Models\User;
use App\Services\Audit;

class AdminNotificationController {
  private const PER_PAGE   = 25;
  private const MAX_LENGTH = 1000;

  public static function index(): void
  {
    $filters = ['q' => self::param($_GET, 'q'), 'statut' => self::param($_GET, 'statut')];
    if (!in_array($filters['statut'], ['lu', 'non_lu'], true)) {
      $filters['statut'] = '';
    }

    $total = Notification::count($filters);
    $pages = max(1, (int) ceil($total / self::PER_PAGE));
    $page  = min(max(1, (int) self::param($_GET, 'p')), $pages);

    AdminController::render('admin/notifications/index', [
      'active'    => 'notifications',
      'pageTitle' => 'Notifications',
      'rows'      => Notification::search($filters, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
      'filters'   => $filters,
      'query'     => array_filter($filters, fn($v) => $v !== ''),
      'basePath'  => '/admin/notifications',
      'total'     => $total,
      'page'      => $page,
      'pages'     => $pages,
    ]);
  }

  public static function create(): void
  {
    self::form(['cible' => 'utilisateur', 'login' => '', 'role' => '', 'message' => ''], []);
  }

  public static function store(): void
  {
    $d = [
      'cible'   => self::param($_POST, 'cible'),
      'login'   => self::param($_POST, 'login'),
      'role'    => self::param($_POST, 'role'),
      'message' => self::param($_POST, 'message'),
    ];
    $errors = [];

    $roles = array_column(User::allRoles(), 'nom');
    $user  = null;

    if (!in_array($d['cible'], ['utilisateur', 'role', 'tous'], true)) {
      $errors['cible'] = 'Choisissez un destinataire.';
    } elseif ($d['cible'] === 'utilisateur') {
      $user = $d['login'] !== '' ? User::findByLogin($d['login']) : null;
      if ($user === null || !$user['actif']) {
        $errors['login'] = 'Aucun compte actif avec ce login.';
      }
    } elseif ($d['cible'] === 'role' && !in_array($d['role'], $roles, true)) {
      $errors['role'] = 'Choisissez un rôle.';
    }

    $length = mb_strlen($d['message']);
    if ($length === 0) {
      $errors['message'] = 'Le message est obligatoire.';
    } elseif ($length > self::MAX_LENGTH) {
      $errors['message'] = 'Le message ne doit pas dépasser ' . self::MAX_LENGTH . ' caractères.';
    }

    if ($errors) {
      self::form($d, $errors);
      return;
    }

    try {
      [$count, $target] = match ($d['cible']) {
        'utilisateur' => [Notification::sendToUser($user['idUser'], $d['message']), "à l'utilisateur {$user['login']}"],
        'role'        => [Notification::sendToRole($d['role'], $d['message']), "aux comptes actifs « {$d['role']} »"],
        'tous'        => [Notification::sendToAll($d['message']), 'à tous les comptes actifs'],
      };
    } catch (\PDOException $e) {
      self::form($d, ['general' => "L'envoi a échoué, veuillez réessayer."]);
      return;
    }

    Audit::log("Envoi d'une notification $target ($count destinataire(s))");
    Flash::set('success', $count > 0 ? "Notification envoyée ($count destinataire(s))." : 'Aucun destinataire actif : rien n’a été envoyé.');
    header('Location: /admin/notifications');
    exit;
  }

  public static function destroy(string $id): void
  {
    $notification = Notification::findById((int) $id);
    if ($notification === null) {
      http_response_code(404);
      echo '404 - Notification introuvable';
      return;
    }

    Notification::delete((int) $id);
    Audit::log("Suppression de la notification n°{$notification['idNotification']} (destinataire {$notification['login']})");
    Flash::set('success', 'Notification supprimée.');
    header('Location: /admin/notifications');
    exit;
  }

  private static function form(array $data, array $errors): void
  {
    AdminController::render('admin/notifications/form', [
      'active'    => 'notifications',
      'pageTitle' => 'Nouvelle notification',
      'data'      => $data,
      'errors'    => $errors,
      'roles'     => User::allRoles(),
      'maxLength' => self::MAX_LENGTH,
    ]);
  }

  private static function param(array $source, string $key): string
  {
    $v = $source[$key] ?? '';
    return is_string($v) ? trim($v) : '';
  }
}
