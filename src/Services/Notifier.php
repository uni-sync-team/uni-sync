<?php

namespace App\Services;

use App\Models\DemandeAdministrative;
use App\Models\Notification;
use App\Models\ReclamationNote;

/** Sends the automatic notifications triggered by an event (a decision on a request or a complaint). */
class Notifier {
  public static function demande(int $idUser, string $type, string $statut): void
  {
    $issue = $statut === DemandeAdministrative::STATUT_TRAITEE ? 'a été traitée' : 'a été refusée';
    self::send($idUser, "Votre demande « $type » $issue.");
  }

  public static function reclamation(int $idUser, string $module, string $evaluation, string $statut): void
  {
    $issue = $statut === ReclamationNote::ACCEPTEE ? 'acceptée' : 'rejetée';
    self::send($idUser, "Votre réclamation sur « $module – $evaluation » a été $issue.");
  }

  /** A notification failure must never undo the decision that triggered it. */
  private static function send(int $idUser, string $message): void
  {
    try {
      Notification::sendToUser($idUser, $message);
    } catch (\Throwable $e) {
      error_log('Notifier failed: ' . $e->getMessage());
    }
  }
}
