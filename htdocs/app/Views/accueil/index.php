<div class="hero">
    <div class="hero-contenu">
        <h1>Le défi sportif des communes de La Réunion</h1>
        <p>Épreuves, équipes et classement inter-villes, en direct.</p>
    </div>
</div>

<h2>En chiffres</h2>

<div class="grille-cartes">
    <div class="carte">
        <h3><?= $nombreVilles ?></h3>
        <p>Villes participantes</p>
    </div>
    <div class="carte">
        <h3><?= $nombreSports ?></h3>
        <p>Sports au programme</p>
    </div>
    <div class="carte">
        <h3><?= $nombreEquipes ?></h3>
        <p>Équipes inscrites</p>
    </div>
    <div class="carte">
        <h3><?= $nombreEpreuves ?></h3>
        <p>Épreuves au total</p>
    </div>
</div>

<h2>Épreuves par statut</h2>

<div class="grille-cartes">
    <div class="carte">
        <span class="badge badge-a_venir">à venir</span>
        <h3><?= $epreuvesParStatut['a_venir'] ?></h3>
    </div>
    <div class="carte">
        <span class="badge badge-en_cours">en cours</span>
        <h3><?= $epreuvesParStatut['en_cours'] ?></h3>
    </div>
    <div class="carte">
        <span class="badge badge-terminee">terminées</span>
        <h3><?= $epreuvesParStatut['terminee'] ?></h3>
    </div>
</div>

<h2>Prochaines épreuves</h2>

<?php if (empty($epreuvesAVenir)): ?>
    <p>Aucune épreuve à venir pour le moment.</p>
<?php else: ?>
    <div class="grille-cartes">
        <?php foreach ($epreuvesAVenir as $epreuve): ?>
            <div class="carte">
                <span class="badge badge-<?= htmlspecialchars($epreuve['statut']) ?>">
                    <?= htmlspecialchars($epreuve['statut']) ?>
                </span>
                <h3><?= htmlspecialchars($epreuve['sport_nom']) ?></h3>
                <p>À <?= htmlspecialchars($epreuve['ville_nom']) ?></p>
                <?php if ($epreuve['date_heure']): ?>
                    <p><?= (new DateTime($epreuve['date_heure']))->format('d/m/Y à H:i') ?></p>
                <?php endif; ?>
                <a href="/epreuves/<?= $epreuve['id'] ?>" class="bouton">Voir le détail</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
