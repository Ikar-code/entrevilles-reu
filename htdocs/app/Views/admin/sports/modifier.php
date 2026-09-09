<?php require __DIR__ . '/../../partials/menu_admin.php'; ?>

<h1>Modifier le sport</h1>

<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="POST" action="/admin/sports/<?= (int) $sport['id'] ?>/modifier">
    <?= Csrf::champ() ?>
    <label for="nom">Nom du sport</label>
    <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($sport['nom']) ?>">
    <label for="description">Description (facultatif)</label>
    <textarea id="description" name="description" rows="3"><?= htmlspecialchars($sport['description'] ?? '') ?></textarea>
    <div class="actions">
        <button type="submit">Enregistrer</button>
        <a href="/admin/sports" class="bouton bouton-secondaire">Annuler</a>
    </div>
</form>
