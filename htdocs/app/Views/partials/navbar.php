<?php
// Surligne le lien de la page en cours : actif si l'adresse est exactement celle
// du lien, ou commence par l'un des préfixes donnés (ex. /epreuves/5 → Planning).
$uriNav = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$lienActif = function (string ...$chemins) use ($uriNav): string {
    foreach ($chemins as $chemin) {
        if ($uriNav === $chemin || ($chemin !== '/' && str_starts_with($uriNav, $chemin . '/'))) {
            return 'actif';
        }
    }
    return '';
};
?>
<nav class="navbar">
    <a href="/" class="navbar-logo">Entrevilles-Reu 🏝️</a>
    <div class="navbar-liens">
        <a href="/" class="<?= $lienActif('/') ?>">Accueil</a>
        <a href="/planning" class="<?= $lienActif('/planning', '/epreuves') ?>">Planning</a>
        <a href="/equipes" class="<?= $lienActif('/equipes') ?>">Équipes</a>
        <a href="/classement" class="<?= $lienActif('/classement') ?>">Classement</a>

        <?php if (Auth::estConnecte()): ?>
            <?php if (Auth::estSuperAdmin()): ?>
                <a href="/admin" class="<?= $lienActif('/admin') ?>">Administration</a>
            <?php elseif (Auth::estManager()): ?>
                <a href="/gestion" class="<?= $lienActif('/gestion', '/admin') ?>">Ma ville</a>
            <?php endif; ?>
            <a href="/profil" class="<?= $lienActif('/profil') ?>">Profil (<?= htmlspecialchars(Auth::utilisateur()['nom_compte']) ?>)</a>
            <a href="/deconnexion">Déconnexion</a>
        <?php else: ?>
            <a href="/login" class="<?= $lienActif('/login') ?>">Connexion</a>
            <a href="/inscription" class="<?= $lienActif('/inscription') ?>">Créer un compte</a>
        <?php endif; ?>

        <?php // Bouton clair / sombre : son texte est mis à jour par assets/js/theme.js ?>
        <button type="button" id="basculeTheme" class="bouton-theme" aria-pressed="false" title="Passer au thème clair">☀️ Thème clair</button>
    </div>
</nav>
