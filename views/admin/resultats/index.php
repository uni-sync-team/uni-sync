<?php use App\Core\Csrf; use App\Core\View; ?>
<h1>Résultats</h1>

<div class="bar">
  <form action="/admin/resultats" method="get">
    <label class="sr" for="semestre">Semestre</label>
    <select id="semestre" name="semestre" required>
      <option value="">— choisir un semestre —</option>
<?php foreach ($semestres as $s): ?>
      <option value="<?= (int) $s['idSemestre'] ?>"<?= $idSemestre === (int) $s['idSemestre'] ? ' selected' : '' ?>><?= View::e($s['formation'] . ' – ' . $s['libelle']) ?></option>
<?php endforeach; ?>
    </select>
    <button class="btn light" type="submit">Afficher</button>
  </form>

<?php if ($idSemestre): ?>
  <form action="/admin/resultats/calculer" method="post">
    <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">
    <input type="hidden" name="idSemestre" value="<?= (int) $idSemestre ?>">
    <button class="btn" type="submit">Calculer les résultats</button>
  </form>
<?php endif; ?>
</div>

<?php if (!$idSemestre): ?>
<p class="hint">Choisissez un semestre pour voir les moyennes et décisions. Les notes se saisissent depuis la page <a href="/admin/examens">Examens</a>.</p>
<?php elseif (!$resultats): ?>
<p>Aucun résultat pour ce semestre. Saisissez des notes puis cliquez sur « Calculer les résultats ».</p>
<?php else: ?>
<p class="hint">Moyenne pondérée par les coefficients des modules ; validé à partir de <?= View::e(rtrim(rtrim(number_format($seuil, 2, ',', ''), '0'), ',')) ?>/20.</p>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>CNE</th><th>Nom</th><th>Prénom</th><th class="num">Moyenne /20</th><th>Décision</th></tr></thead>
  <tbody>
<?php foreach ($resultats as $r): ?>
    <tr>
      <td><?= View::e($r['cne']) ?></td>
      <td><?= View::e($r['nom']) ?></td>
      <td><?= View::e($r['prenom']) ?></td>
      <td class="num"><?= $r['moyenne'] !== null ? View::e(number_format((float) $r['moyenne'], 2, ',', '')) : '—' ?></td>
      <td><span class="badge<?= $r['decision'] === 'VALIDE' ? '' : ' off' ?>"><?= View::e(match ($r['decision']) {
        'VALIDE' => 'Validé', 'NON_VALIDE' => 'Non validé', 'AJOURNE' => 'Ajourné', default => '—',
      }) ?></span></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
