<h1>Classement général inter-villes</h1>
<p>Chaque score obtenu par une équipe s'ajoute au total de sa ville.</p>

<?php if (empty($classement)): ?>
    <div class="etat-vide"><p>Aucune ville classée pour le moment.</p></div>
<?php else: ?>
    <?php $medailles = [1 => '🥇', 2 => '🥈', 3 => '🥉']; ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Ville</th>
                <th>Total points</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($classement as $i => $ligne): ?>
                <?php $rang = $i + 1; ?>
                <tr class="<?= $rang <= 3 ? 'podium podium-' . $rang : '' ?>">
                    <td><?= $rang ?><?= isset($medailles[$rang]) ? ' ' . $medailles[$rang] : '' ?></td>
                    <td><?= htmlspecialchars($ligne['ville_nom']) ?></td>
                    <td><strong><?= (int) $ligne['total_points'] ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
