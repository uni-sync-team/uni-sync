<?php

use App\Core\Csrf;
use App\Core\View;
use App\Models\DemandeAdministrative as Demande;

$labels = [
  Demande::STATUT_EN_ATTENTE => ['En attente', 'warn'],
  Demande::STATUT_TRAITEE    => ['Traitée', 'ok'],
  Demande::STATUT_REFUSEE    => ['Refusée', 'off'],
];

?>
<h1>Demandes administratives</h1>

<form class="filters" action="/admin/demandes" method="get" role="search">
  <div><label for="q">Rechercher</label><input id="q" name="q" type="search" value="<?= View::e($filters['q']) ?>" placeholder="Étudiant, CNE ou type"></div>
  <div><label for="statut">Statut</label>
    <select id="statut" name="statut">
      <option value="">Tous</option>
<?php foreach ($labels as $value => [$text]): ?>
      <option value="<?= View::e($value) ?>"<?= $filters['statut'] === $value ? ' selected' : '' ?>><?= View::e($text) ?></option>
<?php endforeach; ?>
    </select></div>
  <button class="btn light" type="submit">Filtrer</button>
  <a class="btn light" href="/admin/demandes">Réinitialiser</a>
</form>

<p class="hint"><?= (int) $total ?> demande(s). Le traitement notifie automatiquement l’étudiant.</p>

<?php if (!$rows): ?>
<p>Aucune demande trouvée.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Date</th><th>Étudiant</th><th>Demande</th><th>Statut</th><th>Traitée par</th><th></th></tr></thead>
  <tbody>
<?php foreach ($rows as $d): [$text, $cls] = $labels[$d['statut']] ?? [$d['statut'], '']; ?>
    <tr>
      <td><?= View::e(date('d/m/Y H:i', strtotime($d['dateDemande']))) ?></td>
      <td><?= View::e($d['prenom'] . ' ' . $d['nom']) ?><br><small class="muted"><?= View::e($d['cne']) ?></small></td>
      <td><?= View::e($d['typeDemande']) ?></td>
      <td><span class="badge <?= View::e($cls) ?>"><?= View::e($text) ?></span></td>
      <td><?= $d['adminNom'] !== null ? View::e($d['adminPrenom'] . ' ' . $d['adminNom']) : '—' ?></td>
      <td>
<?php if ($d['statut'] === Demande::STATUT_EN_ATTENTE): ?>
        <div class="row-actions">
          <form class="inline" action="/admin/demandes/<?= (int) $d['idDemande'] ?>/traiter" method="post">
            <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">
            <button class="btn" type="submit">Traiter</button>
          </form>
          <form class="inline" action="/admin/demandes/<?= (int) $d['idDemande'] ?>/refuser" method="post" onsubmit="return confirm('Refuser cette demande ?')">
            <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">
            <button class="btn light" type="submit">Refuser</button>
          </form>
        </div>
<?php endif; ?>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>

<?php require __DIR__ . '/../parts/pagination.php'; ?>
