<?php
class AuthController extends Controller
{
    public function afficherLogin(): void
    {
        if (Auth::estConnecte()) {
            $this->rediriger($this->pageApresConnexion());
        }
        $this->afficher('auth/login', ['titre' => 'Connexion', 'erreur' => null]);
    }

    public function traiterLogin(): void
    {
        $email = $this->champ('email');
        $motDePasse = $_POST['mot_de_passe'] ?? '';

        $utilisateur = Utilisateur::verifierIdentifiants($email, $motDePasse);

        if ($utilisateur === null) {
            $this->afficher('auth/login', [
                'titre'  => 'Connexion',
                'erreur' => 'Email ou mot de passe incorrect.',
            ]);
            return;
        }

        Auth::connecter($utilisateur);
        $this->rediriger($this->pageApresConnexion());
    }

    /** Chaque rôle arrive sur "sa" page d'accueil après connexion */
    private function pageApresConnexion(): string
    {
        if (Auth::estSuperAdmin()) {
            return '/admin';
        }
        if (Auth::estManager()) {
            return '/gestion';
        }
        return '/';
    }

    public function afficherInscription(): void
    {
        $this->afficher('auth/inscription', [
            'titre'  => 'Inscription',
            'erreur' => null,
            'villes' => Ville::toutes(),
        ]);
    }

    public function traiterInscription(): void
    {
        $nomCompte = $this->champ('nom_compte');
        $email = $this->champ('email');
        $motDePasse = $_POST['mot_de_passe'] ?? '';
        $motDePasseConfirmation = $_POST['mot_de_passe_confirmation'] ?? '';
        $villeId = $this->champEntier('ville_id');

        $erreur = null;
        if ($nomCompte === '' || $email === '' || $motDePasse === '') {
            $erreur = 'Tous les champs marqués * sont obligatoires.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = 'L\'adresse email n\'est pas valide.';
        } elseif ($motDePasse !== $motDePasseConfirmation) {
            $erreur = 'Les deux mots de passe ne correspondent pas.';
        } elseif (strlen($motDePasse) < 8) {
            $erreur = 'Le mot de passe doit faire au moins 8 caractères.';
        } elseif (Utilisateur::trouverParEmail($email) !== null) {
            $erreur = 'Un compte existe déjà avec cet email.';
        }

        if ($erreur !== null) {
            $this->afficher('auth/inscription', [
                'titre'  => 'Inscription',
                'erreur' => $erreur,
                'villes' => Ville::toutes(),
            ]);
            return;
        }

        // L'inscription publique crée toujours un simple joueur : les managers
        // et super admins sont créés par un super admin depuis /admin/utilisateurs
        $id = Utilisateur::creer($nomCompte, $email, $motDePasse, Auth::ROLE_JOUEUR, $villeId);
        $utilisateur = Utilisateur::trouver($id);

        Auth::connecter($utilisateur);
        $this->rediriger('/profil');
    }

    public function deconnexion(): void
    {
        Auth::deconnecter();
        $this->rediriger('/');
    }
}
