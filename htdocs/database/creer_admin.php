<?php
/**
 * Script à lancer UNE FOIS, en ligne de commande, pour créer le premier
 * compte SUPER ADMINISTRATEUR (l'inscription publique ne crée que des joueurs).
 *
 * Prérequis : avoir exécuté dans Supabase, dans l'ordre,
 *   1. le schéma d'origine,
 *   2. database/migration_mot_de_passe.sql,
 *   3. database/migration_roles.sql,
 * et avoir créé config/env.local.php (ou .env) avec SUPABASE_URL / SUPABASE_KEY.
 *
 * Utilisation :
 *   php database/creer_admin.php
 *   php database/creer_admin.php admin@exemple.re MonMotDePasse "Prénom Nom"
 *
 * Supprime ce fichier une fois utilisé — ne le laisse jamais accessible
 * publiquement en production (le dossier database/ est déjà protégé par .htaccess).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Ce script ne s\'exécute qu\'en ligne de commande.');
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/autoload.php';

$email           = $argv[1] ?? 'admin@entrevilles-reu.re';
$motDePasseClair = $argv[2] ?? 'changez-moi123'; // j'ai changé juste après la première connexion
$nomCompte       = $argv[3] ?? 'Super administrateur';

if (strlen($motDePasseClair) < 8) {
    echo "Le mot de passe doit faire au moins 8 caractères.\n";
    exit(1);
}

$existant = Utilisateur::trouverParEmail($email);
if ($existant !== null) {
    echo "Un compte existe déjà avec l'email $email (rôle : {$existant['role']}).\n";
    echo "Pour le passer super admin, exécute dans Supabase :\n";
    echo "  UPDATE utilisateur SET role = 'super_admin' WHERE email = '$email';\n";
    exit(1);
}

$id = Utilisateur::creer($nomCompte, $email, $motDePasseClair, Auth::ROLE_SUPER_ADMIN);

echo "Super administrateur créé (id $id) : $email / $motDePasseClair\n";
echo "Connecte-toi sur /login, change ce mot de passe depuis /admin/utilisateurs, puis supprime ce fichier.\n";
