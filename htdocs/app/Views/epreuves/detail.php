<span class="badge badge-<?= htmlspecialchars($epreuve['statut'] ?? '') ?>"><?= htmlspecialchars($epreuve['statut'] ?? '—') ?></span>
<h1><?= htmlspecialchars($epreuve['sport_nom']) ?></h1>
<p>À <?= htmlspecialchars($epreuve['ville_nom']) ?></p>
<?php if ($epreuve['date_heure']): ?>
    <p><?= (new DateTime($epreuve['date_heure']))->format('d/m/Y à H:i') ?></p>
<?php endif; ?>

<h2>Équipes participantes</h2>

<?php if (empty($participations)): ?>
    <div class="etat-vide"><p>Aucune équipe inscrite pour le moment.</p></div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Classement</th>
                <th>Équipe</th>
                <th>Ville</th>
                <th>Score</th>
                <th>Statut inscription</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($participations as $participation): ?>
                <tr>
                    <td><?= htmlspecialchars((string) ($participation['classement'] ?? '—')) ?></td>
                    <td><?= htmlspecialchars($participation['equipe_nom']) ?></td>
                    <td><?= htmlspecialchars($participation['ville_nom']) ?></td>
                    <td><?= htmlspecialchars((string) ($participation['score'] ?? '—')) ?></td>
                    <td><?= htmlspecialchars($participation['statut'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
