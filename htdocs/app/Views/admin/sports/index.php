<?php require __DIR__ . '/../../partials/menu_admin.php'; ?>

<h1>Sports</h1>

<?php if (empty($sports)): ?>
    <p>Aucun sport pour le moment.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Nom</th><th>Description</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($sports as $sport): ?>
                <tr>
                    <td><?= htmlspecialchars($sport['nom']) ?></td>
                    <td><?= htmlspecialchars($sport['description'] ?? '') ?></td>
                    <td class="actions">
                        <a href="/admin/sports/<?= (int) $sport['id'] ?>/modifier" class="bouton bouton-petit bouton-secondaire">Modifier</a>
                        <form method="POST" action="/admin/sports/<?= (int) $sport['id'] ?>/supprimer" class="form-inline" onsubmit="return confirm('Supprimer ce sport ?');">
                            <?= Csrf::champ() ?>
                            <button type="submit" class="bouton-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Ajouter un sport</h2>
<form method="POST" action="/admin/sports">
    <?= Csrf::champ() ?>
    <label for="nom">Nom du sport</label>
    <input type="text" id="nom" name="nom" required>
    <label for="description">Description (facultatif)</label>
    <textarea id="description" name="description" rows="3"></textarea>
    <button type="submit">Créer</button>
</form>
