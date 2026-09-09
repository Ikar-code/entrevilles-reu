<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark light">
    <title><?= isset($titre) ? htmlspecialchars($titre) . ' — ' : '' ?>Entrevilles-Reu</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php // Chargé ici (et non en fin de page) pour appliquer le thème mémorisé avant le premier affichage ?>
    <script src="/assets/js/theme.js"></script>
</head>
<body>
    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="conteneur">
        <?php // Messages "flash" laissés par la page précédente (ex. "Ville créée."), affichés une seule fois ?>
        <?php foreach (Flash::recuperer() as $flash): ?>
            <p class="flash flash-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></p>
        <?php endforeach; ?>

        <?php require $cheminVue; ?>
    </main>

    <?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
