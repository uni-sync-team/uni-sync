<?php

namespace App\Controllers;

use App\Models\Historique;

/** Read-only audit trail: the log is never edited or deleted from the interface. */
class AdminHistoriqueController {
  private const PER_PAGE = 25;

  public static function index(): void
  {
    $filters = [
      'login' => self::param('login'),
      'q'     => self::param('q'),
      'du'    => self::date(self::param('du')),
      'au'    => self::date(self::param('au')),
    ];

    $total = Historique::count($filters);
    $pages = max(1, (int) ceil($total / self::PER_PAGE));
    $page  = min(max(1, (int) self::param('p')), $pages);

    AdminController::render('admin/historique/index', [
      'active'    => 'historique',
      'pageTitle' => 'Historique des actions',
      'rows'      => Historique::search($filters, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
      'filters'   => $filters,
      'query'     => array_filter($filters, fn($v) => $v !== ''),
      'basePath'  => '/admin/historique',
      'total'     => $total,
      'page'      => $page,
      'pages'     => $pages,
    ]);
  }

  private static function param(string $key): string
  {
    $v = $_GET[$key] ?? '';
    return is_string($v) ? trim($v) : '';
  }

  /** Keeps a Y-m-d date, drops anything else. */
  private static function date(string $v): string
  {
    $d = \DateTime::createFromFormat('Y-m-d', $v);
    return ($d && $d->format('Y-m-d') === $v) ? $v : '';
  }
}
