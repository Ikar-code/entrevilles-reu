<?php require __DIR__ . '/../../partials/menu_admin.php'; ?>

<h1>Modifier le compte</h1>

<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<?php $roleActuel = $utilisateur['role'] === 'admin' ? Auth::ROLE_SUPER_ADMIN : $utilisateur['role']; ?>

<form method="POST" action="/admin/utilisateurs/<?= (int) $utilisateur['id'] ?>/modifier" class="form-large">
    <?= Csrf::champ() ?>

    <label for="nom_compte">Nom / pseudo</label>
    <input type="text" id="nom_compte" name="nom_compte" required value="<?= htmlspecialchars($utilisateur['nom_compte']) ?>">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($utilisateur['email']) ?>">

    <label for="role">Rôle</label>
    <?php if ($estMoi): ?>
        <select id="role" disabled>
            <option><?= htmlspecialchars($roles[$roleActuel] ?? $roleActuel) ?></option>
        </select>
        <p class="texte-doux">Tu ne peux pas changer ton propre rôle.</p>
    <?php else: ?>
        <select id="role" name="role" required>
            <?php foreach ($roles as $valeur => $libelle): ?>
                <option value="<?= htmlspecialchars($valeur) ?>" <?= $roleActuel === $valeur ? 'selected' : '' ?>><?= htmlspecialchars($libelle) ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <label for="ville_id">Ville (obligatoire pour un manager)</label>
    <select id="ville_id" name="ville_id">
        <option value="">— Aucune —</option>
        <?php foreach ($villes as $ville): ?>
            <option value="<?= (int) $ville['id'] ?>" <?= (string) ($utilisateur['ville_id'] ?? '') === (string) $ville['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ville['nom']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="mot_de_passe">Nouveau mot de passe (laisser vide pour ne pas le changer)</label>
    <input type="password" id="mot_de_passe" name="mot_de_passe" minlength="8">

    <div class="actions">
        <button type="submit">Enregistrer</button>
        <a href="/admin/utilisateurs" class="bouton bouton-secondaire">Annuler</a>
    </div>
</form>
