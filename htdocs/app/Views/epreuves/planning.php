<h1>Planning des épreuves</h1>

<?php if (empty($epreuves)): ?>
    <div class="etat-vide"><p>Aucune épreuve programmée pour le moment.</p></div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Sport</th>
                <th>Ville</th>
                <th>Date</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($epreuves as $epreuve): ?>
                <tr>
                    <td><a href="/epreuves/<?= (int) $epreuve['id'] ?>"><?= htmlspecialchars($epreuve['sport_nom'] ?? '') ?></a></td>
                    <td><?= htmlspecialchars($epreuve['ville_nom'] ?? '') ?></td>
                    <td><?= Format::dateHeure($epreuve['date_heure']) ?></td>
                    <td><span class="badge badge-<?= htmlspecialchars($epreuve['statut'] ?? '') ?>"><?= htmlspecialchars(Epreuve::STATUTS[$epreuve['statut']] ?? $epreuve['statut'] ?? '—') ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
