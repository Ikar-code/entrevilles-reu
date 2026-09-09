<?php require __DIR__ . '/../../partials/menu_admin.php'; ?>

<p><a href="/admin/equipes">← Retour aux équipes</a></p>

<h1><?= htmlspecialchars($equipe['nom']) ?></h1>
<p>
    Ville : <?= htmlspecialchars($equipe['ville_nom'] ?? '') ?> — Sport : <?= htmlspecialchars($equipe['sport_nom'] ?? '') ?>
    · <a href="/equipes/<?= (int) $equipe['id'] ?>">Voir la page publique</a>
</p>

<h2>Membres</h2>

<?php if (empty($membres)): ?>
    <p>Aucun membre pour le moment.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Nom</th><th>Email</th><th>Rôle · pénalité · récompense</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($membres as $membre): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($membre['nom_compte'] ?? '') ?>
                        <?php if ($membre['role_interne'] === 'capitaine'): ?>
                            <span class="badge badge-en_cours">Capitaine</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($membre['email'] ?? '') ?></td>
                    <td>
                        <form method="POST" action="/admin/equipes/<?= (int) $equipe['id'] ?>/membres/modifier" class="form-inline">
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="utilisateur_id" value="<?= (int) $membre['utilisateur_id'] ?>">
                            <select name="role_interne" aria-label="Rôle">
                                <?php foreach ($rolesInternes as $valeur => $libelle): ?>
                                    <option value="<?= $valeur ?>" <?= $membre['role_interne'] === $valeur ? 'selected' : '' ?>><?= htmlspecialchars($libelle) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="penalite" placeholder="Pénalité" value="<?= htmlspecialchars($membre['penalite'] ?? '') ?>" style="width:130px">
                            <input type="text" name="recompense" placeholder="Récompense" value="<?= htmlspecialchars($membre['recompense'] ?? '') ?>" style="width:130px">
                            <button type="submit" class="bouton-secondaire">Enregistrer</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="/admin/equipes/<?= (int) $equipe['id'] ?>/membres/retirer" class="form-inline" onsubmit="return confirm('Retirer ce membre de l\'équipe ?');">
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="utilisateur_id" value="<?= (int) $membre['utilisateur_id'] ?>">
                            <button type="submit" class="bouton-danger">Retirer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h3>Ajouter un membre</h3>

<?php if (empty($candidats)): ?>
    <p>
        Aucun compte disponible à ajouter.
        <?php if (Auth::estManager()): ?>
            <span class="texte-doux">Les joueurs doivent d'abord créer un compte sur le site en choisissant ta ville (ou aucune ville).</span>
        <?php endif; ?>
    </p>
<?php else: ?>
    <form method="POST" action="/admin/equipes/<?= (int) $equipe['id'] ?>/membres">
        <?= Csrf::champ() ?>
        <label for="utilisateur_id">Compte</label>
        <select id="utilisateur_id" name="utilisateur_id" required>
            <option value="">— Choisir —</option>
            <?php foreach ($candidats as $candidat): ?>
                <option value="<?= (int) $candidat['id'] ?>">
                    <?= htmlspecialchars($candidat['nom_compte']) ?> (<?= htmlspecialchars($candidat['email']) ?>) — <?= htmlspecialchars($candidat['ville_nom'] ?? 'sans ville') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="role_interne">Rôle dans l'équipe</label>
        <select id="role_interne" name="role_interne">
            <?php foreach ($rolesInternes as $valeur => $libelle): ?>
                <option value="<?= $valeur ?>"><?= htmlspecialchars($libelle) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Ajouter</button>
    </form>
<?php endif; ?>

<h2>Inscriptions aux épreuves</h2>

<?php if (empty($participations)): ?>
    <p>Cette équipe n'est inscrite à aucune épreuve.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Épreuve</th><th>Date</th><th>Épreuve</th><th>Inscription</th><th>Score</th><th>Rang</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($participations as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['sport_nom'] ?? '') ?> à <?= htmlspecialchars($p['ville_nom'] ?? '') ?></td>
                    <td><?= Format::dateHeure($p['epreuve_date']) ?></td>
                    <td><span class="badge badge-<?= htmlspecialchars($p['epreuve_statut'] ?? '') ?>"><?= htmlspecialchars($statutsEpreuve[$p['epreuve_statut']] ?? $p['epreuve_statut'] ?? '—') ?></span></td>
                    <td><span class="badge badge-<?= htmlspecialchars($p['statut'] ?? '') ?>"><?= htmlspecialchars($statutsParticipation[$p['statut']] ?? $p['statut'] ?? '—') ?></span></td>
                    <td><?= htmlspecialchars((string) ($p['score'] ?? '—')) ?></td>
                    <td><?= htmlspecialchars((string) ($p['classement'] ?? '—')) ?></td>
                    <td>
                        <form method="POST" action="/admin/equipes/<?= (int) $equipe['id'] ?>/inscriptions/retirer" class="form-inline" onsubmit="return confirm('Retirer cette inscription ?');">
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="epreuve_id" value="<?= (int) $p['epreuve_id'] ?>">
                            <button type="submit" class="bouton-danger">Retirer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h3>Inscrire à une épreuve</h3>

<?php if (empty($epreuvesDisponibles)): ?>
    <p>Aucune épreuve à venir pour ce sport.</p>
<?php else: ?>
    <form method="POST" action="/admin/equipes/<?= (int) $equipe['id'] ?>/inscriptions">
        <?= Csrf::champ() ?>
        <label for="epreuve_id">Épreuve à venir (<?= htmlspecialchars($equipe['sport_nom'] ?? '') ?>)</label>
        <select id="epreuve_id" name="epreuve_id" required>
            <option value="">— Choisir —</option>
            <?php foreach ($epreuvesDisponibles as $epreuve): ?>
                <option value="<?= (int) $epreuve['id'] ?>">
                    <?= htmlspecialchars($epreuve['sport_nom'] ?? '') ?> à <?= htmlspecialchars($epreuve['ville_nom'] ?? '') ?> — <?= Format::dateHeure($epreuve['date_heure']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Inscrire</button>
        <?php if (Auth::estManager()): ?>
            <p class="texte-doux">Une inscription faite par un manager est « en attente » jusqu'à validation par un super administrateur.</p>
        <?php endif; ?>
    </form>
<?php endif; ?>
