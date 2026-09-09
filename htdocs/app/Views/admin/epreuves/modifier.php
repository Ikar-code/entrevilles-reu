<?php require __DIR__ . '/../../partials/menu_admin.php'; ?>

<h1>Modifier l'épreuve</h1>

<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="POST" action="/admin/epreuves/<?= (int) $epreuve['id'] ?>/modifier" class="form-large">
    <?= Csrf::champ() ?>

    <label for="sport_id">Sport</label>
    <select id="sport_id" name="sport_id" required>
        <?php foreach ($sports as $sport): ?>
            <option value="<?= (int) $sport['id'] ?>" <?= (int) $epreuve['sport_id'] === (int) $sport['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sport['nom']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="ville_id">Ville (lieu de l'épreuve)</label>
    <select id="ville_id" name="ville_id" required>
        <?php foreach ($villes as $ville): ?>
            <option value="<?= (int) $ville['id'] ?>" <?= (int) $epreuve['ville_id'] === (int) $ville['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ville['nom']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="date_heure">Date et heure (facultatif)</label>
    <input type="datetime-local" id="date_heure" name="date_heure" value="<?= Format::dateHeureLocal($epreuve['date_heure']) ?>">

    <label for="statut">Statut</label>
    <select id="statut" name="statut">
        <?php foreach ($statuts as $valeur => $libelle): ?>
            <option value="<?= $valeur ?>" <?= $epreuve['statut'] === $valeur ? 'selected' : '' ?>><?= htmlspecialchars($libelle) ?></option>
        <?php endforeach; ?>
    </select>

    <div class="actions">
        <button type="submit">Enregistrer</button>
        <a href="/admin/epreuves" class="bouton bouton-secondaire">Annuler</a>
    </div>
</form>
