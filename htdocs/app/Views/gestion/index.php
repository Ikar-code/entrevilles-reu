<?php require __DIR__ . '/../partials/menu_admin.php'; ?>

<h1>Ma ville<?= $ville !== null ? ' : ' . htmlspecialchars($ville['nom']) : '' ?></h1>

<?php if ($ville === null): ?>
    <p class="erreur">Ton compte manager n'est rattaché à aucune ville. Demande à un super administrateur de corriger ton compte.</p>
<?php else: ?>
    <p class="texte-doux">Connecté en tant que manager (<?= htmlspecialchars(Auth::utilisateur()['nom_compte']) ?>). Tu gères les équipes et les membres de <?= htmlspecialchars($ville['nom']) ?>.</p>

    <div class="grille-stats">
        <a class="stat" href="/admin/equipes"><strong><?= count($equipes) ?></strong><span>équipes</span></a>
        <div class="stat"><strong><?= count($enAttente) ?></strong><span>inscriptions en attente</span></div>
        <a class="stat" href="/planning"><strong><?= count($epreuvesAVenir) ?></strong><span>épreuves à venir</span></a>
    </div>

    <h2>Mes équipes</h2>
    <?php if (empty($equipes)): ?>
        <p>Aucune équipe pour le moment. <a href="/admin/equipes">Créer une équipe</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Nom</th><th>Sport</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($equipes as $equipe): ?>
                    <tr>
                        <td><?= htmlspecialchars($equipe['nom']) ?></td>
                        <td><?= htmlspecialchars($equipe['sport_nom'] ?? '') ?></td>
                        <td><a href="/admin/equipes/<?= (int) $equipe['id'] ?>" class="bouton bouton-petit">Membres &amp; inscriptions</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><a href="/admin/equipes" class="bouton bouton-secondaire">+ Créer une équipe</a></p>
    <?php endif; ?>

    <h2>Inscriptions en attente de validation</h2>
    <?php if (empty($enAttente)): ?>
        <p>Aucune inscription en attente.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Équipe</th><th>Épreuve</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($enAttente as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['equipe_nom']) ?></td>
                        <td><?= htmlspecialchars($p['sport_nom'] ?? '') ?> à <?= htmlspecialchars($p['ville_nom'] ?? '') ?></td>
                        <td><?= Format::dateHeure($p['epreuve_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="texte-doux">Un super administrateur doit valider ces inscriptions.</p>
    <?php endif; ?>

    <h2>Prochaines épreuves</h2>
    <?php if (empty($epreuvesAVenir)): ?>
        <p>Aucune épreuve à venir.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Sport</th><th>Lieu</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($epreuvesAVenir as $epreuve): ?>
                    <tr>
                        <td><?= htmlspecialchars($epreuve['sport_nom'] ?? '') ?></td>
                        <td><?= htmlspecialchars($epreuve['ville_nom'] ?? '') ?></td>
                        <td><?= Format::dateHeure($epreuve['date_heure']) ?></td>
                        <td><a href="/epreuves/<?= (int) $epreuve['id'] ?>">Détail</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="texte-doux">Pour inscrire une équipe : ouvre l'équipe depuis « Mes équipes », puis « Inscrire à une épreuve ».</p>
    <?php endif; ?>
<?php endif; ?>
