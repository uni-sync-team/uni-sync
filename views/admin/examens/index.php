<?php use App\Core\Csrf; use App\Core\View; ?>
<h1>Examens</h1>

<div class="bar">
  <form action="/admin/examens" method="get">
    <label class="sr" for="module">Module</label>
    <select id="module" name="module">
      <option value="">Tous les modules</option>
<?php foreach ($modules as $m): ?>
      <option value="<?= (int) $m['idModule'] ?>"<?= $idModule === (int) $m['idModule'] ? ' selected' : '' ?>><?= View::e($m['code'] . ' – ' . $m['intitule']) ?></option>
<?php endforeach; ?>
    </select>
    <label class="sr" for="type">Type</label>
    <select id="type" name="type">
      <option value="">Tous les types</option>
<?php foreach ($types as $t): ?>
      <option value="<?= View::e($t) ?>"<?= $type === $t ? ' selected' : '' ?>><?= View::e($t) ?></option>
<?php endforeach; ?>
    </select>
    <button class="btn light" type="submit">Filtrer</button>
  </form>
  <a class="btn" href="/admin/examens/nouveau">Nouvel examen</a>
</div>

<?php if (!$examens): ?>
<p>Aucun examen trouvé.</p>
<?php else: ?>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Date</th><th>Module</th><th>Type</th><th class="num">Barème</th><th class="num">Coef.</th><th class="num">Notes</th><th></th></tr></thead>
  <tbody>
<?php foreach ($examens as $x): ?>
    <tr>
      <td><?= $x['dateEvaluation'] ? View::e(date('d/m/Y', strtotime($x['dateEvaluation']))) : '—' ?></td>
      <td><?= View::e($x['moduleCode'] . ' – ' . $x['module']) ?><br><span class="muted"><?= View::e($x['formation']) ?></span></td>
      <td><?= View::e($x['type']) ?></td>
      <td class="num"><?= View::e(rtrim(rtrim(number_format((float) $x['bareme'], 2, ',', ''), '0'), ',')) ?></td>
      <td class="num"><?= View::e(rtrim(rtrim(number_format((float) $x['coefficient'], 2, ',', ''), '0'), ',')) ?></td>
      <td class="num"><?= (int) $x['nbNotes'] ?></td>
      <td>
        <a href="/admin/resultats/examens/<?= (int) $x['idEvaluation'] ?>">Notes</a> ·
        <a href="/admin/examens/<?= (int) $x['idEvaluation'] ?>/modifier">Modifier</a> ·
        <form class="inline-form" action="/admin/examens/<?= (int) $x['idEvaluation'] ?>/supprimer" method="post" onsubmit="return confirm('Supprimer cet examen ?');">
          <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">
          <button class="linklike" type="submit">Supprimer</button>
        </form>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
