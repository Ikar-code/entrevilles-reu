<?php
/**
 * Sous-menu de l'espace d'administration (à inclure en haut des vues admin).
 * Le super admin voit toutes les rubriques ; le manager seulement les siennes.
 * Le lien de la page en cours est surligné.
 */
$uriActuelle = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$liensMenu = Auth::estSuperAdmin()
    ? [
        '/admin'              => 'Tableau de bord',
        '/admin/villes'       => 'Villes',
        '/admin/sports'       => 'Sports',
        '/admin/utilisateurs' => 'Utilisateurs',
        '/admin/epreuves'     => 'Épreuves',
        '/admin/equipes'      => 'Équipes',
    ]
    : [
        '/gestion'       => 'Ma ville',
        '/admin/equipes' => 'Mes équipes',
    ];
?>
<nav class="menu-admin">
    <?php foreach ($liensMenu as $lien => $libelle): ?>
        <?php $actif = $uriActuelle === $lien || ($lien !== '/admin' && str_starts_with($uriActuelle, $lien . '/')); ?>
        <a href="<?= $lien ?>" class="<?= $actif ? 'actif' : '' ?>"><?= htmlspecialchars($libelle) ?></a>
    <?php endforeach; ?>
</nav>
