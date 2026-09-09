<?php require __DIR__ . '/../partials/menu_admin.php'; ?>

<h1>Administration</h1>
<p class="texte-doux">Connecté en tant que super administrateur (<?= htmlspecialchars(Auth::utilisateur()['nom_compte']) ?>).</p>

<div class="grille-stats">
    <a class="stat" href="/admin/villes"><strong><?= (int) $stats['villes'] ?></strong><span>villes</span></a>
    <a class="stat" href="/admin/sports"><strong><?= (int) $stats['sports'] ?></strong><span>sports</span></a>
    <a class="stat" href="/admin/utilisateurs"><strong><?= (int) $stats['utilisateurs'] ?></strong><span>comptes</span></a>
    <a class="stat" href="/admin/equipes"><strong><?= (int) $stats['equipes'] ?></strong><span>équipes</span></a>
    <a class="stat" href="/admin/epreuves"><strong><?= (int) $stats['epreuves'] ?></strong><span>épreuves</span></a>
</div>

<h2>Épreuves en cours</h2>
<?php if (empty($epreuvesEnCours)): ?>
    <p>Aucune épreuve en cours. <a href="/admin/epreuves">Gérer les épreuves</a></p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Sport</th><th>Ville</th><th>Date</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($epreuvesEnCours as $epreuve): ?>
                <tr>
                    <td><?= htmlspecialchars($epreuve['sport_nom'] ?? '') ?></td>
                    <td><?= htmlspecialchars($epreuve['ville_nom'] ?? '') ?></td>
                    <td><?= Format::dateHeure($epreuve['date_heure']) ?></td>
                    <td><a href="/admin/epreuves/<?= (int) $epreuve['id'] ?>/participations" class="bouton bouton-petit bouton-secondaire">Résultats</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Managers de ville</h2>
<?php if (empty($managers)): ?>
    <p>Aucun manager pour le moment. Crée un compte avec le rôle « Manager de ville » depuis <a href="/admin/utilisateurs">Utilisateurs</a>.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Nom</th><th>Email</th><th>Ville gérée</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($managers as $manager): ?>
                <tr>
                    <td><?= htmlspecialchars($manager['nom_compte']) ?></td>
                    <td><?= htmlspecialchars($manager['email']) ?></td>
                    <td><?= htmlspecialchars($manager['ville_nom'] ?? '— aucune —') ?></td>
                    <td><a href="/admin/utilisateurs/<?= (int) $manager['id'] ?>/modifier" class="bouton bouton-petit bouton-secondaire">Modifier</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
