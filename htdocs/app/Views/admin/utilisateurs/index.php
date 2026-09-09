<?php require __DIR__ . '/../../partials/menu_admin.php'; ?>

<h1>Utilisateurs</h1>

<?php if (empty($utilisateurs)): ?>
    <p>Aucun compte.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Ville</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($utilisateurs as $u): ?>
                <?php
                    $estMoi = (int) $u['id'] === Auth::utilisateur()['id'];
                    $libelleRole = $roles[$u['role']] ?? ($u['role'] === 'admin' ? 'Super administrateur' : $u['role']);
                ?>
                <tr>
                    <td><?= htmlspecialchars($u['nom_compte']) ?><?= $estMoi ? ' <span class="texte-doux">(moi)</span>' : '' ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge badge-<?= htmlspecialchars($u['role']) ?>"><?= htmlspecialchars($libelleRole) ?></span></td>
                    <td><?= htmlspecialchars($u['ville_nom'] ?? '—') ?></td>
                    <td class="actions">
                        <a href="/admin/utilisateurs/<?= (int) $u['id'] ?>/modifier" class="bouton bouton-petit bouton-secondaire">Modifier</a>
                        <?php if (!$estMoi): ?>
                            <form method="POST" action="/admin/utilisateurs/<?= (int) $u['id'] ?>/supprimer" class="form-inline" onsubmit="return confirm('Supprimer ce compte ? Il sera retiré de toutes ses équipes.');">
                                <?= Csrf::champ() ?>
                                <button type="submit" class="bouton-danger">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Créer un compte</h2>
<p class="texte-doux">
    Pour créer le manager d'une ville : choisis le rôle « Manager de ville » et sa ville.
    Il pourra ensuite gérer les équipes et les membres de cette ville depuis « Ma ville ».
</p>

<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="POST" action="/admin/utilisateurs" class="form-large">
    <?= Csrf::champ() ?>

    <label for="nom_compte">Nom / pseudo</label>
    <input type="text" id="nom_compte" name="nom_compte" required value="<?= htmlspecialchars($saisie['nom_compte'] ?? '') ?>">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($saisie['email'] ?? '') ?>">

    <label for="mot_de_passe">Mot de passe (8 caractères minimum)</label>
    <input type="password" id="mot_de_passe" name="mot_de_passe" minlength="8" required>

    <label for="role">Rôle</label>
    <select id="role" name="role" required>
        <?php foreach ($roles as $valeur => $libelle): ?>
            <option value="<?= htmlspecialchars($valeur) ?>" <?= ($saisie['role'] ?? 'joueur') === $valeur ? 'selected' : '' ?>><?= htmlspecialchars($libelle) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="ville_id">Ville (obligatoire pour un manager)</label>
    <select id="ville_id" name="ville_id">
        <option value="">— Aucune —</option>
        <?php foreach ($villes as $ville): ?>
            <option value="<?= (int) $ville['id'] ?>" <?= (string) ($saisie['ville_id'] ?? '') === (string) $ville['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ville['nom']) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Créer le compte</button>
</form>
