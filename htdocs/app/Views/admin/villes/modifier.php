<?php require __DIR__ . '/../../partials/menu_admin.php'; ?>

<h1>Modifier la ville</h1>

<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="POST" action="/admin/villes/<?= (int) $ville['id'] ?>/modifier">
    <?= Csrf::champ() ?>
    <label for="nom">Nom de la ville</label>
    <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($ville['nom']) ?>">
    <div class="actions">
        <button type="submit">Enregistrer</button>
        <a href="/admin/villes" class="bouton bouton-secondaire">Annuler</a>
    </div>
</form>
