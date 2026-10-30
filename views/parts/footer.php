<?php use App\Core\View; ?>

  <footer id="contact">
    <div class="wrap foot">
      <div><strong>UniSync</strong>
        <p>Étudiants, enseignants, administration</p>
      </div>
      <div><strong>Navigation</strong>
        <p><a href="#departements">Départements</a><br><a href="#filieres">Filières</a><br><a href="#connexion">Se connecter</a></p>
      </div>
      <div><strong>Contact</strong>
        <p><?= View::e($contact['adresse']) ?><br><?= View::e($contact['tel']) ?><br><a href="mailto:<?= View::e($contact['email']) ?>"><?= View::e($contact['email']) ?></a></p>
      </div>
    </div>
  </footer>
</body>

</html>
