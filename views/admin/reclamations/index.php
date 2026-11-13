<?php

use App\Core\Csrf;
use App\Core\View;
use App\Models\ReclamationNote as R;

$labels = [
  R::EN_ATTENTE => ['En attente', 'warn'],
  R::ACCEPTEE   => ['Acceptée', 'ok'],
  R::REJETEE    => ['Rejetée', 'off'],
];
$num = fn($v) => rtrim(rtrim((string) $v, '0'), '.');

?>
<h1>Réclamations</h1>

<form class="filters" action="/admin/reclamations" method="get" role="search">
  <div><label for="q">Rechercher</label><input id="q" name="q" type="search" value="<?= View::e($filters['q']) ?>" placeholder="Étudiant, CNE ou module"></div>
  <div><label for="statut">Statut</label>
    <select id="statut" name="statut">
      <option value="">Tous</option>
<?php foreach ($labels as $value => [$text]): ?>
      <option value="<?= View::e($value) ?>"<?= $filters['statut'] === $value ? ' selected' : '' ?>><?= View::e($text) ?></option>
<?php endforeach; ?>
    </select></div>
  <button class="btn light" type="submit">Filtrer</button>
  <a class="btn light" href="/admin/reclamations">Réinitialiser</a>
</form>

<p class="hint"><?= (int) $total ?> réclamation(s). Elles sont en principe traitées par l’enseignant du module ; l’administrateur peut intervenir. La décision est définitive et notifie l’étudiant.</p>

<?php if (!$rows): ?>
<p>Aucune réclamation trouvée.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Étudiant</th><th>Évaluation</th><th>Note</th><th>Motif</th><th>Statut</th><th></th></tr></thead>
  <tbody>
<?php foreach ($rows as $r): [$text, $cls] = $labels[$r['statut']] ?? [$r['statut'], '']; ?>
    <tr>
      <td><?= View::e($r['prenom'] . ' ' . $r['nom']) ?><br><small class="muted"><?= View::e($r['cne']) ?></small></td>
      <td><?= View::e($r['code'] . ' – ' . $r['module']) ?><br><small class="muted"><?= View::e($r['type']) ?><?= $r['dateEvaluation'] ? ' · ' . View::e(date('d/m/Y', strtotime($r['dateEvaluation']))) : '' ?></small></td>
      <td><?= $r['valeur'] !== null ? View::e($num($r['valeur'])) . ' / ' . View::e($num($r['bareme'])) : '—' ?></td>
      <td><details><summary><?= View::e(mb_strimwidth((string) $r['motif'], 0, 60, '…')) ?></summary><p><?= nl2br(View::e((string) $r['motif'])) ?></p></details></td>
      <td><span class="badge <?= View::e($cls) ?>"><?= View::e($text) ?></span></td>
      <td>
<?php if ($r['statut'] === R::EN_ATTENTE): ?>
        <div class="row-actions">
          <form class="inline" action="/admin/reclamations/<?= (int) $r['idReclamation'] ?>/accepter" method="post" onsubmit="return confirm('Accepter cette réclamation ?')">
            <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">
            <button class="btn" type="submit">Accepter</button>
          </form>
          <form class="inline" action="/admin/reclamations/<?= (int) $r['idReclamation'] ?>/rejeter" method="post" onsubmit="return confirm('Rejeter cette réclamation ?')">
            <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">
            <button class="btn light" type="submit">Rejeter</button>
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
