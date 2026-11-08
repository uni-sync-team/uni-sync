<?php

use App\Core\Csrf;
use App\Core\View;

$editing = $filiere !== null;
$action  = $editing ? '/admin/filieres/' . (int) $data['idFiliere'] : '/admin/filieres';
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
      <legend>Filière</legend>
      <div class="form-grid">
        <div class="field">
          <label for="nom">Nom</label>
          <input id="nom" name="nom" type="text" maxlength="100" value="<?= $val('nom') ?>" autocomplete="off" required autofocus>
          <?= $err('nom') ?>
        </div>

        <div class="field">
          <label for="idDepartement">Département</label>
          <select id="idDepartement" name="idDepartement" required>
            <option value="">— choisir —</option>
<?php foreach ($departements as $d): ?>
            <option value="<?= (int) $d['idDepartement'] ?>"<?= (string) ($data['idDepartement'] ?? '') === (string) $d['idDepartement'] ? ' selected' : '' ?>><?= View::e($d['nom']) ?></option>
<?php endforeach; ?>
          </select>
          <?= $err('idDepartement') ?>
        </div>

        <div class="field full">
          <label for="description">Description <span class="muted">(optionnel)</span></label>
          <input id="description" name="description" type="text" maxlength="255" value="<?= $val('description') ?>">
          <?= $err('description') ?>
        </div>
      </div>
    </fieldset>

    <div class="actions">
      <button class="btn" type="submit"><?= $editing ? 'Enregistrer' : 'Ajouter la filière' ?></button>
      <a class="btn light" href="/admin/filieres">Annuler</a>
    </div>
  </form>
</div>
