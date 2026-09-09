<?php require __DIR__ . '/../../partials/menu_admin.php'; ?>

<h1>Villes</h1>

<?php if (empty($villes)): ?>
    <p>Aucune ville pour le moment.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Nom</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($villes as $ville): ?>
                <tr>
                    <td><?= htmlspecialchars($ville['nom']) ?></td>
                    <td class="actions">
                        <a href="/admin/villes/<?= (int) $ville['id'] ?>/modifier" class="bouton bouton-petit bouton-secondaire">Modifier</a>
                        <form method="POST" action="/admin/villes/<?= (int) $ville['id'] ?>/supprimer" class="form-inline" onsubmit="return confirm('Supprimer cette ville ?');">
                            <?= Csrf::champ() ?>
                            <button type="submit" class="bouton-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Ajouter une ville</h2>
<form method="POST" action="/admin/villes">
    <?= Csrf::champ() ?>
    <label for="nom">Nom de la ville</label>
    <input type="text" id="nom" name="nom" required>
    <button type="submit">Créer</button>
</form>
