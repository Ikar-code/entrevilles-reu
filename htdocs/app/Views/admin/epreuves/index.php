<?php require __DIR__ . '/../../partials/menu_admin.php'; ?>

<h1>Épreuves</h1>

<?php if (empty($epreuves)): ?>
    <p>Aucune épreuve pour le moment.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Sport</th><th>Ville</th><th>Date</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($epreuves as $epreuve): ?>
                <tr>
                    <td><?= htmlspecialchars($epreuve['sport_nom'] ?? '') ?></td>
                    <td><?= htmlspecialchars($epreuve['ville_nom'] ?? '') ?></td>
                    <td><?= Format::dateHeure($epreuve['date_heure']) ?></td>
                    <td>
                        <span class="badge badge-<?= htmlspecialchars($epreuve['statut'] ?? '') ?>"><?= htmlspecialchars($statuts[$epreuve['statut']] ?? $epreuve['statut'] ?? '—') ?></span>
                        <form method="POST" action="/admin/epreuves/<?= (int) $epreuve['id'] ?>/statut" class="form-inline">
                            <?= Csrf::champ() ?>
                            <select name="statut" aria-label="Nouveau statut">
                                <?php foreach ($statuts as $valeur => $libelle): ?>
                                    <option value="<?= $valeur ?>" <?= $epreuve['statut'] === $valeur ? 'selected' : '' ?>><?= htmlspecialchars($libelle) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="bouton-secondaire">Changer</button>
                        </form>
                    </td>
                    <td class="actions">
                        <a href="/admin/epreuves/<?= (int) $epreuve['id'] ?>/participations" class="bouton bouton-petit">Participations</a>
                        <a href="/admin/epreuves/<?= (int) $epreuve['id'] ?>/modifier" class="bouton bouton-petit bouton-secondaire">Modifier</a>
                        <form method="POST" action="/admin/epreuves/<?= (int) $epreuve['id'] ?>/supprimer" class="form-inline" onsubmit="return confirm('Supprimer cette épreuve et toutes ses participations ?');">
                            <?= Csrf::champ() ?>
                            <button type="submit" class="bouton-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Créer une épreuve</h2>

<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="POST" action="/admin/epreuves" class="form-large">
    <?= Csrf::champ() ?>

    <label for="sport_id">Sport</label>
    <select id="sport_id" name="sport_id" required>
        <option value="">— Choisir —</option>
        <?php foreach ($sports as $sport): ?>
            <option value="<?= (int) $sport['id'] ?>" <?= (string) ($saisie['sport_id'] ?? '') === (string) $sport['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sport['nom']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="ville_id">Ville (lieu de l'épreuve)</label>
    <select id="ville_id" name="ville_id" required>
        <option value="">— Choisir —</option>
        <?php foreach ($villes as $ville): ?>
            <option value="<?= (int) $ville['id'] ?>" <?= (string) ($saisie['ville_id'] ?? '') === (string) $ville['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ville['nom']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="date_heure">Date et heure (facultatif)</label>
    <input type="datetime-local" id="date_heure" name="date_heure" value="<?= htmlspecialchars($saisie['date_heure'] ?? '') ?>">

    <label for="statut">Statut</label>
    <select id="statut" name="statut">
        <?php foreach ($statuts as $valeur => $libelle): ?>
            <option value="<?= $valeur ?>" <?= ($saisie['statut'] ?? 'a_venir') === $valeur ? 'selected' : '' ?>><?= htmlspecialchars($libelle) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Créer l'épreuve</button>
</form>
