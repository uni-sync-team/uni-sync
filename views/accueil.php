<?php

use App\Core\View;

$departements ??= [];
$stats ??= ['etudiants' => 0, 'enseignants' => 0, 'filieres' => 0, 'formations' => 0, 'modules' => 0];
$annee ??= null;
// À remplacer par les vraies coordonnées du département
$contact = ['email' => 'contact@unisync.example', 'tel' => '+212 5 00 00 00 00', 'adresse' => 'Adresse du département'];

$styles  = ['/css/styles.css'];
$scripts = ['/script/script.js'];

require __DIR__ . "/parts/header.php";
?>

  <header class="top">
    <div class="topbar">
      <div class="wrap">
        <span><?= $annee ? 'Année universitaire ' . View::e($annee) : 'UniSync' ?></span>
        <a href="mailto:<?= View::e($contact['email']) ?>"><?= View::e($contact['email']) ?></a>
      </div>
    </div>
    <div class="wrap">
      <nav aria-label="Navigation principale">
        <a class="logo" href="/"><i aria-hidden="true"></i>UniSync</a>
        <ul>
          <li><a href="#departements">Départements</a></li>
          <li><a href="#filieres">Filières</a></li>
          <li><a href="#services">Services</a></li>
        </ul>
        <div class="navbtns">
          <a class="btn line small" href="#contact">Contact</a>
          <a class="btn sage small" href="/login">Se connecter</a>
        </div>
      </nav>

      <div class="hero">
        <div>
          <h1>Bienvenue sur votre plateforme web universitaire.</h1>
          <p>Un seul espace pour l'ensemble de votre communauté universitaire.</p>
          <div class="actions">
            <a class="btn sage" href="/login">Se connecter</a>
            <a class="btn line" href="#departements">Nos départements</a>
          </div>
        </div>

        <div class="pubs" role="region" aria-roledescription="carrousel" aria-label="Publications du département">
          <header>
            <h2>Publications</h2><span>Dernières annonces</span>
          </header>
          <?php $publications = $publications ?? [];
          $total = count($publications); ?>
          <div id="slides" aria-live="off">
            <?php if ($total === 0): ?>
              <article class="slide">
                <h3>Aucune publication pour le moment</h3>
                <p>Les annonces du département apparaîtront ici.</p>
              </article>
            <?php endif; ?>
            <?php foreach ($publications as $n => $p): ?>
              <article class="slide" aria-label="<?= $n + 1 ?> sur <?= $total ?>" <?= $n > 0 ? ' hidden' : '' ?>><time datetime="<?= View::e($p['datePublication']) ?>"><?= View::e(View::dateFr($p['datePublication'])) ?></time>
                <h3><?= View::e($p['titre']) ?></h3>
                <p><?= View::e(mb_strimwidth($p['contenu'], 0, 160, '…')) ?></p>
              </article>
            <?php endforeach; ?>
          </div>
          <?php if ($total > 1): ?>
            <div class="ctrl">
              <button type="button" id="pause" aria-label="Mettre le diaporama en pause">Pause</button>
              <div class="dots" id="dots"></div>
              <div>
                <button type="button" id="prev" aria-label="Publication précédente">‹</button>
                <button type="button" id="next" aria-label="Publication suivante">›</button>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <main id="principal">
    <section class="chiffres" aria-label="Chiffres clés">
      <div class="wrap ">
        <dl>
          <?php foreach (['etudiants' => 'Étudiants', 'enseignants' => 'Enseignants', 'filieres' => 'Filières', 'formations' => 'Formations'] as $k => $label): ?>
            <div>
              <dd><?= (int) $stats[$k] ?></dd>
              <dt><?= $label ?></dt>
            </div>
          <?php endforeach; ?>
        </dl>
      </div>
    </section>

    <section id="departements">
      <div class="wrap">
        <h2 class="big">Nos départements</h2>
        <p class="lead">Chaque département regroupe des filières et des formations, de la licence au master.</p>
        <div class="cards">
          <?php if (!$departements): ?>
            <p>Aucun département pour le moment.</p>
          <?php endif; ?>
          <?php foreach ($departements as $d): $nbFor = array_sum(array_map(fn($f) => count($f['formations']), $d['filieres'])); ?>
            <article class="card">
              <h3><?= View::e($d['nom']) ?></h3>
              <p><?= View::e($d['description']) ?></p>
              <p class="meta"><?= count($d['filieres']) ?> filière(s) · <?= $nbFor ?> formation(s)</p>
              <ul class="tags"><?php foreach ($d['filieres'] as $f): ?><li><?= View::e($f['nom']) ?></li><?php endforeach; ?></ul>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section id="filieres">
      <div class="wrap">
        <h2 class="big">Filières et formations</h2>
        <p class="lead">Retrouvez les filières de chaque département et les formations qu’elles proposent.</p>

        <div class="dep-grid">
          <?php foreach ($departements as $d): if (!$d['filieres']) continue; ?>
            <div class="dep-col">
              <h3 class="dep"><?= View::e($d['nom']) ?></h3>
              <div class="stack">
                <?php foreach ($d['filieres'] as $f): ?>
                  <article class="card">
                    <h4><?= View::e($f['nom']) ?></h4>
                    <p><?= View::e($f['description']) ?></p>
                    <ul class="formations">
                      <?php foreach ($f['formations'] as $fo): ?>
                        <li><?= View::e($fo['intitule']) ?><span><?= View::e($fo['niveau']) ?><?= $fo['duree'] ? ' · ' . (int) $fo['duree'] . ' an(s)' : '' ?></span></li>
                      <?php endforeach; ?>
                    </ul>
                  </article>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="band" id="services">
      <div class="wrap cols">
        <div>
          <h2 class="big">Des services près de vous</h2>
          <p class="lead">Pour toutes vos démarches et votre vie d’étudiant, tout au long de l’année.</p>
        </div>
        <ul class="regles">
          <li><strong>Secrétariat pédagogique</strong><span>Inscriptions, attestations et certificats de scolarité : adressez-vous au secrétariat du département.</span></li>
          <li><strong>Bibliothèque universitaire</strong><span>Ouvrages, salles de travail et ressources en ligne pour préparer vos cours et vos examens.</span></li>
          <li><strong>Vie étudiante</strong><span>Clubs, associations et événements pour animer le campus.</span></li>
          <li><strong>Assistance informatique</strong><span>Un souci de connexion ou de mot de passe ? Le service informatique vous répond.</span></li>
        </ul>
      </div>
    </section>

    <section class="cta" id="connexion">
      <div class="wrap">
        <div>
          <h2>Accédez à votre espace.</h2>
          <p>Connectez-vous avec votre login et votre mot de passe. Votre espace s’ouvre selon votre rôle.</p>
        </div>
        <a class="btn" href="/login">Se connecter</a>
      </div>
    </section>

  </main>

<?php require __DIR__ . "/parts/footer.php"; ?>
