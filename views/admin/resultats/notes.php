<?php use App\Core\Csrf; use App\Core\View; ?>
<?php
$fmt = fn($n) => rtrim(rtrim(number_format((float) $n, 2, ',', ''), '0'), ',');
?>
<h1><?= View::e($pageTitle) ?></h1>

<p class="hint">
  <?= View::e($examen['module']) ?> – <?= View::e($examen['formation']) ?>
  · barème <?= View::e($fmt($examen['bareme'])) ?>
  · coef. <?= View::e($fmt($examen['coefficient'])) ?>
<?php if ($examen['dateEvaluation']): ?>
  · <?= View::e(date('d/m/Y', strtotime($examen['dateEvaluation']))) ?>
<?php endif; ?>
<?php if ((int) $stats['nbNotes'] > 0): ?>
  <br>Statistiques : <?= (int) $stats['nbNotes'] ?> note(s), moyenne <?= View::e($fmt($stats['moyenne'])) ?>, min <?= View::e($fmt($stats['minimum'])) ?>, max <?= View::e($fmt($stats['maximum'])) ?>
<?php endif; ?>
</p>

<form class="bar" action="/admin/resultats/examens/<?= (int) $examen['idEvaluation'] ?>" method="get">
  <div>
    <label class="sr" for="session">Session</label>
    <select id="session" name="session" onchange="this.form.submit()">
<?php foreach ($sessions as $s): ?>
      <option value="<?= View::e($s) ?>"<?= $s === $session ? ' selected' : '' ?>><?= View::e(ucfirst(strtolower($s))) ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <a class="btn light" href="/admin/examens">Retour aux examens</a>
</form>

<?php if (!$rows): ?>
<p>Aucun étudiant avec une inscription validée dans cette formation.</p>
<?php else: ?>
<form action="/admin/resultats/examens/<?= (int) $examen['idEvaluation'] ?>" method="post" novalidate>
  <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">
  <input type="hidden" name="session" value="<?= View::e($session) ?>">

  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>CNE</th><th>Nom</th><th>Prénom</th><th class="num">Note /<?= View::e($fmt($examen['bareme'])) ?></th></tr></thead>
    <tbody>
<?php foreach ($rows as $r):
  $id  = (int) $r['idEtudiant'];
  $cur = $override[$id] ?? $r['valeur'];
  $cur = $cur === null ? '' : (is_float($cur) || is_numeric($cur) ? $fmt($cur) : (string) $cur);
?>
      <tr>
        <td><?= View::e($r['cne']) ?></td>
        <td><?= View::e($r['nom']) ?></td>
        <td><?= View::e($r['prenom']) ?></td>
        <td class="num">
          <label class="sr" for="note-<?= $id ?>">Note de <?= View::e($r['nom'] . ' ' . $r['prenom']) ?></label>
          <input class="note" id="note-<?= $id ?>" name="notes[<?= $id ?>]" type="text" inputmode="decimal" value="<?= View::e($cur) ?>" autocomplete="off">
<?php if (isset($errors[$id])): ?>
          <p class="err" role="alert"><?= View::e($errors[$id]) ?></p>
<?php endif; ?>
        </td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table></div>

  <p class="hint">Laisser une case vide n'efface pas une note existante.</p>
  <div class="actions">
    <button class="btn" type="submit">Enregistrer les notes</button>
  </div>
</form>
<?php endif; ?>
