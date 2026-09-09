<h1>Mon profil</h1>

<div class="carte">
    <p><strong>Nom :</strong> <?= htmlspecialchars($utilisateur['nom_compte']) ?></p>
    <p><strong>Email :</strong> <?= htmlspecialchars($utilisateur['email']) ?></p>
    <p><strong>Rôle :</strong> <?= htmlspecialchars($utilisateur['role']) ?></p>
</div>

<h2>Mes équipes</h2>

<?php if (empty($equipes)): ?>
    <div class="etat-vide">
        <p>Tu ne fais partie d'aucune équipe pour le moment.</p>
        <a href="/equipes" class="bouton bouton-secondaire">Découvrir les équipes</a>
    </div>
<?php else: ?>
    <div class="grille-cartes">
        <?php foreach ($equipes as $membre): ?>
            <div class="carte">
                <h3><?= htmlspecialchars($membre['equipe_nom']) ?></h3>
                <p><?= htmlspecialchars($membre['ville_nom'] ?? '') ?> — <?= htmlspecialchars($membre['sport_nom'] ?? '') ?></p>
                <?php if ($membre['role_interne'] === 'capitaine'): ?>
                    <span class="badge badge-en_cours">Capitaine</span>
                <?php endif; ?>
                <a href="/equipes/<?= $membre['equipe_id'] ?>" class="bouton">Voir l'équipe</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
