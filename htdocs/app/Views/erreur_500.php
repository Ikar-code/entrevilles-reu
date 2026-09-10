<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark light">
    <title>Erreur — Entrevilles-Reu</title>
    <link rel="stylesheet" href="<?= Format::asset('/assets/css/style.css') ?>">
    <script src="<?= Format::asset('/assets/js/theme.js') ?>"></script>
</head>
<body>
    <main class="conteneur">
        <h1>Une erreur est survenue</h1>
        <p>Le site n'a pas pu traiter ta demande. Réessaie dans quelques instants.</p>
        <?php if (!empty($messageErreur)): ?>
            <p class="erreur"><?= htmlspecialchars($messageErreur) ?></p>
        <?php endif; ?>
        <p><a href="/">Retour à l'accueil</a></p>
    </main>
</body>
</html>
