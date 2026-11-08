<?php

use App\Core\Csrf;
use App\Core\View;
use App\Models\Salle;

$editing = $salle !== null;
$action  = $editing ? '/admin/salles/' . (int) $data['idSalle'] : '/admin/salles';
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
      <legend>Salle</legend>
      <div class="form-grid">
        <div class="field">
          <label for="nom">Nom</label>
          <input id="nom" name="nom" type="text" maxlength="50" value="<?= $val('nom') ?>" autocomplete="off" required autofocus>
          <?= $err('nom') ?>
        </div>

        <div class="field">
          <label for="capacite">Capacité</label>
          <input id="capacite" name="capacite" type="number" min="1" max="2000" value="<?= $val('capacite') ?>" required>
          <?= $err('capacite') ?>
        </div>

        <div class="field full">
          <label for="typeSalle">Type <span class="muted">(optionnel)</span></label>
          <select id="typeSalle" name="typeSalle">
            <option value="">— aucun —</option>
<?php foreach (Salle::TYPES as $t): ?>
            <option value="<?= View::e($t) ?>"<?= ($data['typeSalle'] ?? '') === $t ? ' selected' : '' ?>><?= View::e($t) ?></option>
<?php endforeach; ?>
          </select>
          <?= $err('typeSalle') ?>
        </div>
      </div>
    </fieldset>

    <div class="actions">
      <button class="btn" type="submit"><?= $editing ? 'Enregistrer' : 'Ajouter la salle' ?></button>
      <a class="btn light" href="/admin/salles">Annuler</a>
    </div>
  </form>
</div>
