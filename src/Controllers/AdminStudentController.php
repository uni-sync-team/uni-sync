<?php

namespace App\Controllers;

use App\Models\Etudiant;
use App\Controllers\AdminController;
use App\Core\Session;
use App\Models\User;
use App\Services\Audit;
use App\Core\Flash;

class AdminStudentController
{

  public static function index(): void
  {
    $q = trim((string) ($_GET['q'] ?? ''));

    AdminController::render('admin/etudiants/index', [
      'active'    => 'etudiants',
      'pageTitle' => 'Etudiant',
      'etudiants' => Etudiant::findAll($q),
      'q'         => $q,
      'me'        => Session::userId(),
    ]);
  }

  public static function create(): void
  {
    self::form(null, [
      'login'         => '',
      'email'         => '',
      'nom'           => '',
      'prenom'        => '',
      'cne'           => '',
      'dateNaissance' => '',
      'telephone'     => '',
     ],
     []);
  }

  private static function form(?array $user, array $data, array $errors): void
  {
    AdminController::render('admin/etudiants/form', [
      'active'    => 'etudiants',
      'pageTitle' => $user === null ? 'Nouvel Etudiant' : 'Modifier ' . $data['nom'] . ' ' . $data['prenom'],
      'user'      => $user,
      'data'      => $data,
      'errors'    => $errors,
      'roles'     => User::allRoles(),
      'isSelf'    => $user !== null && $user['idUser'] === Session::userId(),
    ]);
  }

  public static function store(): void
  {
    [$errors, $d] = self::validate(null);
    if (!$errors) {
      try {
        Etudiant::createWithUser($d);
      } catch (\PDOException) {
        $errors['general'] = "Impossible d'ajouter l'étudiant (CNE ou compte déjà utilisé ?).";
      } catch (\RunTimeException $e) {
        // Configuration fault not the user
        $errors['general'] = $e->getMessage();
      }
    }
    if ($errors) {
      self::form(null, $d, $errors);
      return;
    }

    Audit::log("Création de l'étudiant {$d['nom']} {$d['prenom']}");
    Flash::set('success', "Étudiant « {$d['nom']} {$d['prenom']} » ajouté.");
    self::redirect();
  }

  public static function edit(string $id)
  {
    $etud = Etudiant::findById((int) $id);
    if ($etud === null) {
      self::notFound();
      return;
    }
    $user = User::findById((int) $etud['idUser']);
    if ($user == null) {
      self::notFound();
      return;
    }
    self::form($user, [
      'idEtudiant' => $etud['idEtudiant'],
      'nom' => $etud['nom'],
      'prenom' => $etud['prenom'],
      'cne' => $etud['cne'],
      'telephone' => $etud['telephone'],
      'date' => $etud['telephone'],
      'dateNaissance' => $etud['dateNaissance'],
      'actif' => Etudiant::isActive($etud['idEtudiant']),
      ],
      []);
  }

  public static function update(string $id): void
  {
    $etud = Etudiant::findById((int) $id);
    if ($etud === null) {
      self::notFound();
      return;
    }
    $user = User::findById((int) $etud['idUser']);
    if ($user === null) {
      self::notFound();
      return;
    }

    [$errors, $d, $actif] = self::validate($etud);

    if (!$errors && !Etudiant::update($etud['idEtudiant'], $d, $actif)) {
      $errors['general'] = 'Impossible de modifier l\'étudiant (CNE déjà utilisé ?).';
    }

    if ($errors) {
      $d['idEtudiant'] = $etud['idEtudiant'];
      $d['actif']      = $etud['actif'];
      self::form($user, $d, $errors);
      return;
    }

    Audit::log("Modification de l'etudiant {$etud['nom']} {$etud['prenom']} ");
    Flash::set('success', "Compte « {$etud['nom']} {$etud['prenom']} » mis à jour.");
    self::redirect();
  }

  /** @return array{0: array<string,string>, 1: array} errors and cleaned data */
  private static function validate(?array $existing): array
  {
    $errors = [];

    $login    = trim((string) ($_POST['login'] ?? ''));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    // --- account (only when creating) ---
    if ($existing === null) {
      if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $login)) {
        $errors['login'] = '3 à 50 caractères : lettres, chiffres, point, tiret, underscore.';
      }
      if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors['email'] = 'Adresse email invalide.';
      }
      if (strlen($password) < 8) {
        $errors['password'] = 'Mot de passe requis (8 caractères minimum).';
      }
      if (!isset($errors['login']) && !isset($errors['email'])) {
        $taken = User::conflict($login, $email, null);
        if ($taken !== null) {
          $errors[$taken] = ($taken === 'login' ? 'Ce login' : 'Cet email') . ' est déjà utilisé.';
        }
      }
    }

    $prenom = trim((string) ($_POST['prenom'] ?? ''));
    $nom    = trim((string) ($_POST['nom'] ?? ''));
    $cne    = strtoupper(trim((string) ($_POST['cne'] ?? '')));
    $date   = trim((string) ($_POST['dateNaissance'] ?? ''));
    $tel    = preg_replace('/[\s.\-]/', '', (string) ($_POST['telephone'] ?? ''));
    $actif  = isset($_POST['actif']);

    // --- names ---
    $namePattern = '/^\p{L}[\p{L} \'\-]*$/u';

    if ($prenom === '' || mb_strlen($prenom) > 100 || !preg_match($namePattern, $prenom)) {
      $errors['prenom'] = 'Prénom requis (lettres, espaces, tiret, apostrophe ; 100 max).';
    }
    if ($nom === '' || mb_strlen($nom) > 100 || !preg_match($namePattern, $nom)) {
      $errors['nom'] = 'Nom requis (lettres, espaces, tiret, apostrophe ; 100 max).';
    }

    // --- CNE ---
    if (!preg_match('/^[A-Z0-9]{5,20}$/', $cne)) {
      $errors['cne'] = 'CNE invalide (5 à 20 lettres ou chiffres).';
    } elseif (Etudiant::cneTaken($cne, $existing['idEtudiant'] ?? null)) {
      $errors['cne'] = 'Ce CNE est déjà utilisé.';
    }

    // --- date of birth (optional) ---
    if ($date !== '') {
      $d = \DateTime::createFromFormat('!Y-m-d', $date);
      if ($d === false || $d->format('Y-m-d') !== $date) {
        $errors['dateNaissance'] = 'Date invalide.';
      } elseif ($d > new \DateTime('today') || $d < new \DateTime('-100 years')) {
        $errors['dateNaissance'] = 'Date de naissance non plausible.';
      }
    }

    // --- phone (optional) ---
    if ($tel !== '' && !preg_match('/^(?:\+212|0)[5-7]\d{8}$/', $tel)) {
      $errors['telephone'] = 'Numéro invalide (ex : 0612345678 ou +212612345678).';
    }

    return [$errors, [
      'login'    => $login,
      'email'    => $email,
      'password' => $password,
      'prenom'        => $prenom,
      'nom'           => $nom,
      'cne'           => $cne,
      'dateNaissance' => $date !== '' ? $date : null,
      'telephone'     => $tel !== '' ? $tel : null,
    ], $actif];
  }

  private static function redirect(): never
  {
    header('Location: /admin/etudiants');
    exit;
  }

  private static function notFound(): void
  {
    http_response_code(404);
    echo '404 - Etudiant introuvable';
  }
}
