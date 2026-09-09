<h1><?= htmlspecialchars($equipe['nom']) ?></h1>
<p>Ville : <?= htmlspecialchars($equipe['ville_nom']) ?> — Sport : <?= htmlspecialchars($equipe['sport_nom']) ?></p>

<h2>Membres de l'équipe</h2>

<?php if (empty($membres)): ?>
    <div class="etat-vide"><p>Aucun membre enregistré pour cette équipe.</p></div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Rôle</th>
                <th>Pénalité</th>
                <th>Récompense</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($membres as $membre): ?>
                <tr>
                    <td><?= htmlspecialchars($membre['nom_compte']) ?></td>
                    <td>
                        <?php if ($membre['role_interne'] === 'capitaine'): ?>
                            <span class="badge badge-en_cours">Capitaine</span>
                        <?php else: ?>
                            <?= htmlspecialchars($membre['role_interne'] ?? 'Joueur') ?>
                        <?php endif; ?>
                    </td>
                    <td><?= $membre['penalite'] ? htmlspecialchars($membre['penalite']) : '—' ?></td>
                    <td><?= $membre['recompense'] ? htmlspecialchars($membre['recompense']) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
