<?php require __DIR__ . '/../../partials/menu_admin.php'; ?>

<p><a href="/admin/epreuves">← Retour aux épreuves</a></p>

<span class="badge badge-<?= htmlspecialchars($epreuve['statut'] ?? '') ?>"><?= htmlspecialchars($statutsEpreuve[$epreuve['statut']] ?? $epreuve['statut'] ?? '—') ?></span>
<h1><?= htmlspecialchars($epreuve['sport_nom'] ?? '') ?> à <?= htmlspecialchars($epreuve['ville_nom'] ?? '') ?></h1>
<p><?= Format::dateHeure($epreuve['date_heure'], 'd/m/Y à H:i') ?> · <a href="/epreuves/<?= (int) $epreuve['id'] ?>">Voir la page publique</a></p>

<h2>Équipes inscrites</h2>

<?php if (empty($participations)): ?>
    <p>Aucune équipe inscrite pour le moment.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Équipe</th><th>Ville</th><th>Inscription</th><th>Résultat</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($participations as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['equipe_nom'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['ville_nom'] ?? '') ?></td>
                    <td><span class="badge badge-<?= htmlspecialchars($p['statut'] ?? '') ?>"><?= htmlspecialchars($statuts[$p['statut']] ?? $p['statut'] ?? '—') ?></span></td>
                    <td>
                        <form method="POST" action="/admin/epreuves/<?= (int) $epreuve['id'] ?>/participations/resultat" class="form-inline">
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="equipe_id" value="<?= (int) $p['equipe_id'] ?>">
                            <input type="number" name="score" min="0" placeholder="Score" value="<?= htmlspecialchars((string) ($p['score'] ?? '')) ?>" style="width:90px">
                            <input type="number" name="classement" min="1" placeholder="Rang" value="<?= htmlspecialchars((string) ($p['classement'] ?? '')) ?>" style="width:80px">
                            <button type="submit" class="bouton-secondaire">Enregistrer</button>
                        </form>
                    </td>
                    <td class="actions">
                        <?php if ($p['statut'] === 'en_attente'): ?>
                            <form method="POST" action="/admin/epreuves/<?= (int) $epreuve['id'] ?>/participations/valider" class="form-inline">
                                <?= Csrf::champ() ?>
                                <input type="hidden" name="equipe_id" value="<?= (int) $p['equipe_id'] ?>">
                                <button type="submit">Valider</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" action="/admin/epreuves/<?= (int) $epreuve['id'] ?>/participations/retirer" class="form-inline" onsubmit="return confirm('Retirer cette équipe de l\'épreuve ?');">
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="equipe_id" value="<?= (int) $p['equipe_id'] ?>">
                            <button type="submit" class="bouton-danger">Retirer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="texte-doux">Le score de chaque équipe s'ajoute au total de sa ville dans le classement général.</p>
<?php endif; ?>

<h2>Inscrire une équipe</h2>

<?php if (empty($equipesDisponibles)): ?>
    <p>Aucune autre équipe de ce sport à inscrire. <a href="/admin/equipes">Créer une équipe</a></p>
<?php else: ?>
    <form method="POST" action="/admin/epreuves/<?= (int) $epreuve['id'] ?>/participations">
        <?= Csrf::champ() ?>
        <label for="equipe_id">Équipe (<?= htmlspecialchars($epreuve['sport_nom'] ?? '') ?>)</label>
        <select id="equipe_id" name="equipe_id" required>
            <option value="">— Choisir —</option>
            <?php foreach ($equipesDisponibles as $equipe): ?>
                <option value="<?= (int) $equipe['id'] ?>"><?= htmlspecialchars($equipe['nom']) ?> — <?= htmlspecialchars($equipe['ville_nom'] ?? '') ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Inscrire</button>
    </form>
<?php endif; ?>
