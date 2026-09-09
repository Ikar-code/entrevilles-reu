<?php require __DIR__ . '/../../partials/menu_admin.php'; ?>

<h1>Équipes<?= $villeGeree !== null ? ' de ' . htmlspecialchars($villeGeree['nom']) : '' ?></h1>

<?php if (empty($equipes)): ?>
    <p>Aucune équipe pour le moment.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Nom</th><th>Ville</th><th>Sport</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($equipes as $equipe): ?>
                <tr>
                    <td><?= htmlspecialchars($equipe['nom']) ?></td>
                    <td><?= htmlspecialchars($equipe['ville_nom'] ?? '') ?></td>
                    <td><?= htmlspecialchars($equipe['sport_nom'] ?? '') ?></td>
                    <td class="actions">
                        <a href="/admin/equipes/<?= (int) $equipe['id'] ?>" class="bouton bouton-petit">Membres &amp; inscriptions</a>
                        <form method="POST" action="/admin/equipes/<?= (int) $equipe['id'] ?>/supprimer" class="form-inline" onsubmit="return confirm('Supprimer cette équipe, ses membres et ses inscriptions ?');">
                            <?= Csrf::champ() ?>
                            <button type="submit" class="bouton-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Créer une équipe</h2>

<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="POST" action="/admin/equipes">
    <?= Csrf::champ() ?>

    <label for="nom">Nom de l'équipe</label>
    <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($saisie['nom'] ?? '') ?>">

    <label for="sport_id">Sport</label>
    <select id="sport_id" name="sport_id" required>
        <option value="">— Choisir —</option>
        <?php foreach ($sports as $sport): ?>
            <option value="<?= (int) $sport['id'] ?>" <?= (string) ($saisie['sport_id'] ?? '') === (string) $sport['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sport['nom']) ?></option>
        <?php endforeach; ?>
    </select>

    <?php if ($villeGeree !== null): ?>
        <p class="texte-doux">Ville : <strong><?= htmlspecialchars($villeGeree['nom']) ?></strong> (ta ville)</p>
    <?php else: ?>
        <label for="ville_id">Ville</label>
        <select id="ville_id" name="ville_id" required>
            <option value="">— Choisir —</option>
            <?php foreach ($villes as $ville): ?>
                <option value="<?= (int) $ville['id'] ?>" <?= (string) ($saisie['ville_id'] ?? '') === (string) $ville['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ville['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <button type="submit">Créer l'équipe</button>
</form>
