<nav class="navbar">
    <a href="/" class="navbar-logo">Entrevilles-Reu 🏝️</a>
    <div class="navbar-liens">
        <a href="/">Accueil</a>
        <a href="/planning">Planning</a>
        <a href="/equipes">Équipes</a>
        <a href="/classement">Classement</a>

        <?php if (Auth::estConnecte()): ?>
            <?php if (Auth::estSuperAdmin()): ?>
                <a href="/admin">Administration</a>
            <?php elseif (Auth::estManager()): ?>
                <a href="/gestion">Ma ville</a>
            <?php endif; ?>
            <a href="/profil">Profil (<?= htmlspecialchars(Auth::utilisateur()['nom_compte']) ?>)</a>
            <a href="/deconnexion">Déconnexion</a>
        <?php else: ?>
            <a href="/login">Connexion</a>
            <a href="/inscription">Créer un compte</a>
        <?php endif; ?>
    </div>
</nav>
