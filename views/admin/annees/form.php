<?php

use App\Core\Csrf;
use App\Core\View;

$editing = $annee !== null;
$action  = $editing ? '/admin/annees/' . (int) $data['idAnnee'] : '/admin/annees';
$err     = fn(string $f) => isset($errors[$f]) ? '<p class="err" role="alert">' . View::e($errors[$f]) . '</p>' : '';
$val     = fn(string $f) => View::e($data[$f] ?? '');

?>
<h1><?= View::e($pageTitle) ?></h1>

<div class="panel wide">
<?php if (!empty($errors['general'])): ?>
  <p class="flash error" role="alert"><?= View::e($errors['general']) ?></p>
<?php endif; ?>

  <form action="<?= View::e($action) ?>" method="post" novalidate>
    <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">

    <fieldset>
      <legend>Année universitaire</legend>
      <div class="form-grid">
        <div class="field full">
          <label for="libelle">Libellé</label>
          <input id="libelle" name="libelle" type="text" maxlength="50" placeholder="2025-2026"
                 value="<?= $val('libelle') ?>" autocomplete="off" required autofocus>
          <?= $err('libelle') ?>
        </div>

        <div class="field">
          <label for="dateDebut">Date de début</label>
          <input id="dateDebut" name="dateDebut" type="date" value="<?= $val('dateDebut') ?>" required>
          <?= $err('dateDebut') ?>
        </div>

        <div class="field">
          <label for="dateFin">Date de fin</label>
          <input id="dateFin" name="dateFin" type="date" value="<?= $val('dateFin') ?>" required>
          <?= $err('dateFin') ?>
        </div>
      </div>
    </fieldset>

    <div class="actions">
      <button class="btn" type="submit"><?= $editing ? 'Enregistrer' : 'Ajouter l\'année' ?></button>
      <a class="btn light" href="/admin/annees">Annuler</a>
    </div>
  </form>
</div>
