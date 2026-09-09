<h1>Créer un compte</h1>

<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="POST" action="/inscription">
    <?= Csrf::champ() ?>

    <label for="nom_compte">Nom / pseudo *</label>
    <input type="text" id="nom_compte" name="nom_compte" required value="<?= htmlspecialchars($_POST['nom_compte'] ?? '') ?>">

    <label for="email">Email *</label>
    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

    <label for="ville_id">Ville (facultatif)</label>
    <select id="ville_id" name="ville_id">
        <option value="">— Aucune —</option>
        <?php foreach ($villes as $ville): ?>
            <option value="<?= (int) $ville['id'] ?>" <?= (string) ($_POST['ville_id'] ?? '') === (string) $ville['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ville['nom']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="mot_de_passe">Mot de passe * (8 caractères minimum)</label>
    <input type="password" id="mot_de_passe" name="mot_de_passe" minlength="8" required>

    <label for="mot_de_passe_confirmation">Confirmer le mot de passe *</label>
    <input type="password" id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" minlength="8" required>

    <button type="submit">Créer mon compte</button>
</form>

<p class="lien-secondaire">Déjà un compte ? <a href="/login">Connecte-toi</a></p>
