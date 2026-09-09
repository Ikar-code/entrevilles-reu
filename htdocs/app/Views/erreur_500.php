<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Erreur — Entrevilles-Reu</title>
    <link rel="stylesheet" href="/assets/css/style.css">
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
