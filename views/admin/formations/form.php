<?php

use App\Core\Csrf;
use App\Core\View;

$editing = $formation !== null;
$action  = $editing ? '/admin/formations/' . (int) $data['idFormation'] : '/admin/formations';
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
      <legend>Formation</legend>
      <div class="form-grid">
        <div class="field full">
          <label for="intitule">Intitulé</label>
          <input id="intitule" name="intitule" type="text" maxlength="150" value="<?= $val('intitule') ?>" autocomplete="off" required autofocus>
          <?= $err('intitule') ?>
        </div>

        <div class="field full">
          <label for="idFiliere">Filière</label>
          <select id="idFiliere" name="idFiliere" required>
            <option value="">— choisir —</option>
<?php foreach ($filieres as $f): ?>
            <option value="<?= (int) $f['idFiliere'] ?>"<?= (string) ($data['idFiliere'] ?? '') === (string) $f['idFiliere'] ? ' selected' : '' ?>><?= View::e($f['nom']) ?></option>
<?php endforeach; ?>
          </select>
          <?= $err('idFiliere') ?>
        </div>

        <div class="field">
          <label for="niveau">Niveau <span class="muted">(optionnel)</span></label>
          <select id="niveau" name="niveau">
            <option value="">— aucun —</option>
<?php foreach ($niveaux as $n): ?>
            <option value="<?= View::e($n) ?>"<?= ($data['niveau'] ?? '') === $n ? ' selected' : '' ?>><?= View::e($n) ?></option>
<?php endforeach; ?>
          </select>
          <?= $err('niveau') ?>
        </div>

        <div class="field">
          <label for="duree">Durée en années <span class="muted">(optionnel)</span></label>
          <input id="duree" name="duree" type="number" min="1" max="10" value="<?= $val('duree') ?>">
          <?= $err('duree') ?>
        </div>
      </div>
    </fieldset>

    <div class="actions">
      <button class="btn" type="submit"><?= $editing ? 'Enregistrer' : 'Ajouter la formation' ?></button>
      <a class="btn light" href="/admin/formations">Annuler</a>
    </div>
  </form>
</div>

<?php if ($editing): ?>
<h2>Semestres</h2>
<div class="panel wide">
<?php if (!$semestres): ?>
  <p>Aucun semestre pour cette formation.</p>
<?php else: ?>
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>N°</th><th>Libellé</th></tr></thead>
    <tbody>
<?php foreach ($semestres as $s): ?>
      <tr><td><?= (int) $s['numero'] ?></td><td><?= View::e($s['libelle']) ?></td></tr>
<?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>

  <form action="/admin/formations/<?= (int) $data['idFormation'] ?>/semestres" method="post" novalidate>
    <input type="hidden" name="csrf" value="<?= View::e(Csrf::token()) ?>">
    <fieldset>
      <legend>Ajouter un semestre</legend>
      <div class="form-grid">
        <div class="field">
          <label for="sem-numero">Numéro</label>
          <input id="sem-numero" name="numero" type="number" min="1" max="20" value="<?= count($semestres) + 1 ?>" required>
        </div>
        <div class="field">
          <label for="sem-libelle">Libellé <span class="muted">(optionnel)</span></label>
          <input id="sem-libelle" name="libelle" type="text" maxlength="50" placeholder="Semestre <?= count($semestres) + 1 ?>">
        </div>
      </div>
    </fieldset>
    <div class="actions"><button class="btn light" type="submit">Ajouter le semestre</button></div>
  </form>
</div>
<?php endif; ?>
