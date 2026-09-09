<h1>Connexion</h1>

<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="POST" action="/login">
    <?= Csrf::champ() ?>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>

    <label for="mot_de_passe">Mot de passe</label>
    <input type="password" id="mot_de_passe" name="mot_de_passe" required>

    <button type="submit">Se connecter</button>
</form>

<p class="lien-secondaire">Pas encore de compte ? <a href="/inscription">Crée-en un</a></p>
