<div class="hero">
    <div class="hero-contenu">
        <span class="hero-surtitre">Championnat inter-communes · La Réunion</span>
        <h1>Le défi sportif des <span>communes</span> de La Réunion</h1>
        <p>Épreuves, équipes et classement inter-villes, en direct.</p>
        <div class="hero-actions">
            <a href="/planning" class="bouton">Voir le planning</a>
            <a href="/classement" class="bouton bouton-secondaire">Classement général</a>
        </div>
    </div>
</div>

<h2>En chiffres</h2>

<div class="grille-cartes grille-chiffres">
    <div class="carte carte-chiffre">
        <span class="chiffre"><?= (int) $nombreVilles ?></span>
        <p>Villes participantes</p>
    </div>
    <div class="carte carte-chiffre">
        <span class="chiffre"><?= (int) $nombreSports ?></span>
        <p>Sports au programme</p>
    </div>
    <div class="carte carte-chiffre">
        <span class="chiffre"><?= (int) $nombreEquipes ?></span>
        <p>Équipes inscrites</p>
    </div>
    <div class="carte carte-chiffre">
        <span class="chiffre"><?= (int) $nombreEpreuves ?></span>
        <p>Épreuves au total</p>
    </div>
</div>

<h2>Épreuves par statut</h2>

<div class="grille-cartes grille-chiffres">
    <div class="carte carte-chiffre">
        <span class="badge badge-a_venir">À venir</span>
        <span class="chiffre"><?= (int) $epreuvesParStatut['a_venir'] ?></span>
        <p>épreuves programmées</p>
    </div>
    <div class="carte carte-chiffre">
        <span class="badge badge-en_cours">En cours</span>
        <span class="chiffre"><?= (int) $epreuvesParStatut['en_cours'] ?></span>
        <p>épreuves en cours</p>
    </div>
    <div class="carte carte-chiffre">
        <span class="badge badge-terminee">Terminées</span>
        <span class="chiffre"><?= (int) $epreuvesParStatut['terminee'] ?></span>
        <p>épreuves jouées</p>
    </div>
</div>

<h2>Prochaines épreuves</h2>

<?php if (empty($epreuvesAVenir)): ?>
    <div class="etat-vide">
        <p>Aucune épreuve à venir pour le moment.</p>
        <a href="/planning" class="bouton bouton-secondaire">Voir tout le planning</a>
    </div>
<?php else: ?>
    <div class="grille-cartes">
        <?php foreach ($epreuvesAVenir as $epreuve): ?>
            <div class="carte">
                <span class="badge badge-<?= htmlspecialchars($epreuve['statut']) ?>">
                    <?= htmlspecialchars(Epreuve::STATUTS[$epreuve['statut']] ?? $epreuve['statut']) ?>
                </span>
                <h3><?= htmlspecialchars($epreuve['sport_nom']) ?></h3>
                <p>À <?= htmlspecialchars($epreuve['ville_nom']) ?></p>
                <?php if ($epreuve['date_heure']): ?>
                    <p><?= Format::dateHeure($epreuve['date_heure'], 'd/m/Y à H:i') ?></p>
                <?php endif; ?>
                <a href="/epreuves/<?= (int) $epreuve['id'] ?>" class="bouton">Voir le détail</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
