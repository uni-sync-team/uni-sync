<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Core\Session;
use App\Models\Administrateur;
use App\Models\Publication;
use App\Services\Audit;

class AdminAnnonceController {
  private const PER_PAGE    = 15;
  private const MAX_CONTENT = 5000;

  public static function index(): void
  {
    $q     = self::get('q');
    $total = Publication::count($q);
    $pages = max(1, (int) ceil($total / self::PER_PAGE));
    $page  = min(max(1, (int) self::get('p')), $pages);

    AdminController::render('admin/annonces/index', [
      'active'    => 'annonces',
      'pageTitle' => 'Annonces',
      'rows'      => Publication::search($q, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
      'q'         => $q,
      'query'     => $q !== '' ? ['q' => $q] : [],
      'basePath'  => '/admin/annonces',
      'total'     => $total,
      'page'      => $page,
      'pages'     => $pages,
    ]);
  }

  public static function create(): void
  {
    self::form(null, ['titre' => '', 'contenu' => '', 'datePublication' => date('Y-m-d'), 'dateExpiration' => '', 'actif' => true], []);
  }

  public static function store(): void
  {
    [$errors, $d] = self::validate();

    // publication.idAdmin is required: the logged-in account must have an administrateur profile.
    $admin = (new Administrateur())->findByUser((int) Session::userId());
    if (!$errors && $admin === null) {
      $errors['general'] = 'Ce compte n’a pas de profil administrateur : impossible de publier une annonce.';
    }

    if (!$errors) {
      try {
        Publication::create((int) $admin['idAdmin'], $d);
      } catch (\PDOException) {
        $errors['general'] = "Impossible d'enregistrer l'annonce.";
      }
    }
    if ($errors) {
      self::form(null, $d, $errors);
      return;
    }

    Audit::log("Création de l'annonce « {$d['titre']} »");
    Flash::set('success', "Annonce « {$d['titre']} » enregistrée.");
    self::redirect();
  }

  public static function edit(string $id): void
  {
    $annonce = Publication::findById((int) $id);
    if ($annonce === null) {
      self::notFound();
      return;
    }
    self::form($annonce, [
      'titre'           => $annonce['titre'],
      'contenu'         => $annonce['contenu'],
      'datePublication' => $annonce['datePublication'],
      'dateExpiration'  => $annonce['dateExpiration'] ?? '',
      'actif'           => (bool) $annonce['actif'],
    ], []);
  }

  public static function update(string $id): void
  {
    $annonce = Publication::findById((int) $id);
    if ($annonce === null) {
      self::notFound();
      return;
    }

    [$errors, $d] = self::validate();
    if (!$errors) {
      try {
        Publication::update($annonce['idPublication'], $d);   // false = "nothing changed", not an error
      } catch (\PDOException) {
        $errors['general'] = "Impossible de modifier l'annonce.";
      }
    }
    if ($errors) {
      self::form($annonce, $d, $errors);
      return;
    }

    Audit::log("Modification de l'annonce « {$d['titre']} »");
    Flash::set('success', "Annonce « {$d['titre']} » mise à jour.");
    self::redirect();
  }

  public static function destroy(string $id): void
  {
    $annonce = Publication::findById((int) $id);
    if ($annonce === null) {
      self::notFound();
      return;
    }

    Publication::delete($annonce['idPublication']);
    Audit::log("Suppression de l'annonce « {$annonce['titre']} »");
    Flash::set('success', 'Annonce supprimée.');
    self::redirect();
  }

  /** @return array{0: array<string,string>, 1: array} errors and cleaned data */
  private static function validate(): array
  {
    $errors  = [];
    $titre   = self::post('titre');
    $contenu = self::post('contenu');
    $pub     = self::post('datePublication');
    $exp     = self::post('dateExpiration');

    if ($titre === '' || mb_strlen($titre) > 150) {
      $errors['titre'] = 'Titre requis (150 caractères maximum).';
    }
    if ($contenu === '' || mb_strlen($contenu) > self::MAX_CONTENT) {
      $errors['contenu'] = 'Contenu requis (' . self::MAX_CONTENT . ' caractères maximum).';
    }
    if (!self::validDate($pub)) {
      $errors['datePublication'] = 'Date de publication invalide.';
    }
    if ($exp !== '' && !self::validDate($exp)) {
      $errors['dateExpiration'] = 'Date d’expiration invalide.';
    } elseif ($exp !== '' && !isset($errors['datePublication']) && $exp < $pub) {
      $errors['dateExpiration'] = 'L’expiration doit être le jour de la publication ou après.';
    }

    return [$errors, [
      'titre' => $titre, 'contenu' => $contenu, 'datePublication' => $pub,
      'dateExpiration' => $exp !== '' ? $exp : null, 'actif' => isset($_POST['actif']),
    ]];
  }

  private static function form(?array $annonce, array $data, array $errors): void
  {
    AdminController::render('admin/annonces/form', [
      'active'     => 'annonces',
      'pageTitle'  => $annonce === null ? 'Nouvelle annonce' : 'Modifier l’annonce',
      'annonce'    => $annonce,
      'data'       => $data,
      'errors'     => $errors,
      'maxContent' => self::MAX_CONTENT,
    ]);
  }

  private static function validDate(string $v): bool
  {
    $d = \DateTime::createFromFormat('Y-m-d', $v);
    return $d !== false && $d->format('Y-m-d') === $v;
  }

  private static function get(string $key): string
  {
    $v = $_GET[$key] ?? '';
    return is_string($v) ? trim($v) : '';
  }

  private static function post(string $key): string
  {
    $v = $_POST[$key] ?? '';
    return is_string($v) ? trim($v) : '';
  }

  private static function redirect(): never
  {
    header('Location: /admin/annonces');
    exit;
  }

  private static function notFound(): void
  {
    http_response_code(404);
    echo '404 - Annonce introuvable';
  }
}
