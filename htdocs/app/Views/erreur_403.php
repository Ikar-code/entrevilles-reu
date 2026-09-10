<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark light">
    <title>Accès refusé — Entrevilles-Reu</title>
    <link rel="stylesheet" href="<?= Format::asset('/assets/css/style.css') ?>">
    <script src="<?= Format::asset('/assets/js/theme.js') ?>"></script>
</head>
<body>
    <main class="conteneur">
        <h1>403 — Accès refusé</h1>
        <p><?= htmlspecialchars($motifRefus ?? 'Tu n\'as pas les droits nécessaires pour accéder à cette page.') ?></p>
        <p>
            <a href="/">Retour à l'accueil</a>
            <?php if (!Auth::estConnecte()): ?> · <a href="/login">Se connecter</a><?php endif; ?>
        </p>
    </main>
</body>
</html>
