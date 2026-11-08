<?php

use App\Core\Csrf;
use App\Core\View;

$editing = $examen !== null;
$action  = $editing ? '/admin/examens/' . (int) $data['idEvaluation'] : '/admin/examens';
$err     = fn(string $f) => isset($errors[$f]) ? '<p class="err" role="alert">' . View::e($errors[$f]) . '</p>' : '';
$val     = fn(string $f) => View::e((string) ($data[$f] ?? ''));

?>
<h1><?= View::e($pageTitle) ?></h1>

<div class="panel wide">
<?php if (!empty($errors['general'])): ?>
  <p class="flash error" role="alert"><?= View::e($errors['general']) ?></p>
<?php endif; ?>

  <form action="<?= View::e($action) ?>" method="post" novalidate>
    <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">

    <fieldset>
      <legend>Examen</legend>
      <div class="form-grid">
        <div class="field full">
          <label for="idModule">Module</label>
          <select id="idModule" name="idModule" required autofocus>
            <option value="">— choisir —</option>
<?php foreach ($modules as $m): ?>
            <option value="<?= (int) $m['idModule'] ?>"<?= (string) ($data['idModule'] ?? '') === (string) $m['idModule'] ? ' selected' : '' ?>><?= View::e($m['code'] . ' – ' . $m['intitule'] . ' (' . $m['formation'] . ', ' . $m['semestre'] . ')') ?></option>
<?php endforeach; ?>
          </select>
          <?= $err('idModule') ?>
        </div>

        <div class="field">
          <label for="type">Type</label>
          <select id="type" name="type" required>
            <option value="">— choisir —</option>
<?php foreach ($types as $t): ?>
            <option value="<?= View::e($t) ?>"<?= ($data['type'] ?? '') === $t ? ' selected' : '' ?>><?= View::e($t) ?></option>
<?php endforeach; ?>
          </select>
          <?= $err('type') ?>
        </div>

        <div class="field">
          <label for="dateEvaluation">Date <span class="muted">(optionnel)</span></label>
          <input id="dateEvaluation" name="dateEvaluation" type="date" value="<?= $val('dateEvaluation') ?>">
          <?= $err('dateEvaluation') ?>
        </div>

        <div class="field">
          <label for="bareme">Barème</label>
          <input id="bareme" name="bareme" type="number" min="0.01" max="999.99" step="0.01" value="<?= $val('bareme') ?>" required>
          <?= $err('bareme') ?>
        </div>

        <div class="field">
          <label for="coefficient">Coefficient</label>
          <input id="coefficient" name="coefficient" type="number" min="0.01" max="99.99" step="0.01" value="<?= $val('coefficient') ?>" required>
          <?= $err('coefficient') ?>
        </div>
      </div>
    </fieldset>

    <div class="actions">
      <button class="btn" type="submit"><?= $editing ? 'Enregistrer' : "Ajouter l'examen" ?></button>
      <a class="btn light" href="/admin/examens">Annuler</a>
    </div>
  </form>
</div>
