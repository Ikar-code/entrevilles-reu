<?php
/**
 * Déclaration de toutes les routes de l'application.
 * Format : Router::get('/chemin/{param}', Controleur::class, 'methode');
 */

// Accueil
Router::get('/', AccueilController::class, 'index');

// Authentification
Router::get('/login', AuthController::class, 'afficherLogin');
Router::post('/login', AuthController::class, 'traiterLogin');
Router::get('/inscription', AuthController::class, 'afficherInscription');
Router::post('/inscription', AuthController::class, 'traiterInscription');
Router::get('/deconnexion', AuthController::class, 'deconnexion');

// Profil
Router::get('/profil', ProfilController::class, 'index');

// Épreuves (public)
Router::get('/planning', EpreuveController::class, 'planning');
Router::get('/epreuves/{id}', EpreuveController::class, 'detail');

// Équipes (public)
Router::get('/equipes', EquipeController::class, 'liste');
Router::get('/equipes/{id}', EquipeController::class, 'detail');

// Classement
Router::get('/classement', ClassementController::class, 'index');

// ----------------------------------------------------------------------
// Espace manager de ville
// ----------------------------------------------------------------------
Router::get('/gestion', ManagerController::class, 'index');

// ----------------------------------------------------------------------
// Administration (super admin) — tableau de bord, villes, sports
// ----------------------------------------------------------------------
Router::get('/admin', AdminController::class, 'tableauDeBord');

Router::get('/admin/villes', AdminController::class, 'villes');
Router::post('/admin/villes', AdminController::class, 'creerVille');
Router::get('/admin/villes/{id}/modifier', AdminController::class, 'modifierVille');
Router::post('/admin/villes/{id}/modifier', AdminController::class, 'enregistrerVille');
Router::post('/admin/villes/{id}/supprimer', AdminController::class, 'supprimerVille');

Router::get('/admin/sports', AdminController::class, 'sports');
Router::post('/admin/sports', AdminController::class, 'creerSport');
Router::get('/admin/sports/{id}/modifier', AdminController::class, 'modifierSport');
Router::post('/admin/sports/{id}/modifier', AdminController::class, 'enregistrerSport');
Router::post('/admin/sports/{id}/supprimer', AdminController::class, 'supprimerSport');

// Utilisateurs (super admin) : joueurs, managers de ville, super admins
Router::get('/admin/utilisateurs', AdminUtilisateurController::class, 'liste');
Router::post('/admin/utilisateurs', AdminUtilisateurController::class, 'creer');
Router::get('/admin/utilisateurs/{id}/modifier', AdminUtilisateurController::class, 'modifier');
Router::post('/admin/utilisateurs/{id}/modifier', AdminUtilisateurController::class, 'enregistrer');
Router::post('/admin/utilisateurs/{id}/supprimer', AdminUtilisateurController::class, 'supprimer');

// Épreuves (super admin) + participations / résultats
Router::get('/admin/epreuves', AdminEpreuveController::class, 'liste');
Router::post('/admin/epreuves', AdminEpreuveController::class, 'creer');
Router::get('/admin/epreuves/{id}/modifier', AdminEpreuveController::class, 'modifier');
Router::post('/admin/epreuves/{id}/modifier', AdminEpreuveController::class, 'enregistrer');
Router::post('/admin/epreuves/{id}/statut', AdminEpreuveController::class, 'changerStatut');
Router::post('/admin/epreuves/{id}/supprimer', AdminEpreuveController::class, 'supprimer');
Router::get('/admin/epreuves/{id}/participations', AdminEpreuveController::class, 'participations');
Router::post('/admin/epreuves/{id}/participations', AdminEpreuveController::class, 'inscrire');
Router::post('/admin/epreuves/{id}/participations/resultat', AdminEpreuveController::class, 'resultat');
Router::post('/admin/epreuves/{id}/participations/valider', AdminEpreuveController::class, 'validerParticipation');
Router::post('/admin/epreuves/{id}/participations/retirer', AdminEpreuveController::class, 'retirerParticipation');

// Équipes et membres (super admin ET managers, chacun limité à sa ville)
Router::get('/admin/equipes', AdminEquipeController::class, 'liste');
Router::post('/admin/equipes', AdminEquipeController::class, 'creer');
Router::get('/admin/equipes/{id}', AdminEquipeController::class, 'membres');
Router::post('/admin/equipes/{id}/supprimer', AdminEquipeController::class, 'supprimer');
Router::post('/admin/equipes/{id}/membres', AdminEquipeController::class, 'ajouterMembre');
Router::post('/admin/equipes/{id}/membres/modifier', AdminEquipeController::class, 'modifierMembre');
Router::post('/admin/equipes/{id}/membres/retirer', AdminEquipeController::class, 'retirerMembre');
Router::post('/admin/equipes/{id}/inscriptions', AdminEquipeController::class, 'inscrireEpreuve');
Router::post('/admin/equipes/{id}/inscriptions/retirer', AdminEquipeController::class, 'retirerInscription');
