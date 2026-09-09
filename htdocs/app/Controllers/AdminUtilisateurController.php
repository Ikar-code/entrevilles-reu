<?php
/**
 * Gestion des comptes par le super admin : création (joueurs, managers de
 * ville, autres super admins), modification du rôle / de la ville / du mot
 * de passe, suppression.
 */
class AdminUtilisateurController extends Controller
{
    public function __construct()
    {
        Auth::exigerSuperAdmin();
    }

    public function liste(): void
    {
        $this->afficherListe();
    }

    /** Page liste + formulaire de création (ré-affichée avec l'erreur et la saisie en cas de problème) */
    private function afficherListe(?string $erreur = null, array $saisie = []): void
    {
        $this->afficher('admin/utilisateurs/index', [
            'titre'        => 'Utilisateurs',
            'utilisateurs' => Utilisateur::tous(),
            'villes'       => Ville::toutes(),
            'roles'        => Auth::ROLES,
            'erreur'       => $erreur,
            'saisie'       => $saisie,
        ]);
    }

    public function creer(): void
    {
        $nomCompte  = $this->champ('nom_compte');
        $email      = $this->champ('email');
        $motDePasse = $_POST['mot_de_passe'] ?? '';
        $role       = $this->champ('role');
        $villeId    = $this->champEntier('ville_id');

        $erreur = $this->valider($nomCompte, $email, $role, $villeId, $motDePasse, true);
        if ($erreur === null && Utilisateur::trouverParEmail($email) !== null) {
            $erreur = 'Un compte existe déjà avec cet email.';
        }
        if ($erreur !== null) {
            $this->afficherListe($erreur, $_POST);
            return;
        }

        Utilisateur::creer($nomCompte, $email, $motDePasse, $role, $villeId);
        Flash::succes('Compte « ' . $nomCompte . ' » créé avec le rôle ' . Auth::ROLES[$role] . '.');
        $this->rediriger('/admin/utilisateurs');
    }

    public function modifier(string $id): void
    {
        $utilisateur = Utilisateur::trouver((int) $id);
        if ($utilisateur === null) {
            $this->introuvable();
        }

        $this->afficherFormulaireModification($utilisateur);
    }

    private function afficherFormulaireModification(array $utilisateur, ?string $erreur = null): void
    {
        $this->afficher('admin/utilisateurs/modifier', [
            'titre'       => 'Modifier le compte',
            'utilisateur' => $utilisateur,
            'villes'      => Ville::toutes(),
            'roles'       => Auth::ROLES,
            'erreur'      => $erreur,
            'estMoi'      => (int) $utilisateur['id'] === Auth::utilisateur()['id'],
        ]);
    }

    public function enregistrer(string $id): void
    {
        $utilisateur = Utilisateur::trouver((int) $id);
        if ($utilisateur === null) {
            $this->introuvable();
        }
        $estMoi = (int) $utilisateur['id'] === Auth::utilisateur()['id'];

        $nomCompte  = $this->champ('nom_compte');
        $email      = $this->champ('email');
        $motDePasse = $_POST['mot_de_passe'] ?? ''; // vide = inchangé
        // On ne peut pas changer son propre rôle : cela éviterait de se retirer soi-même tous les droits
        $role       = $estMoi ? $utilisateur['role'] : $this->champ('role');
        $villeId    = $this->champEntier('ville_id');

        $erreur = $this->valider($nomCompte, $email, $role, $villeId, $motDePasse, false);
        if ($erreur === null) {
            $existant = Utilisateur::trouverParEmail($email);
            if ($existant !== null && (int) $existant['id'] !== (int) $utilisateur['id']) {
                $erreur = 'Un autre compte utilise déjà cet email.';
            }
        }
        if ($erreur !== null) {
            $saisie = array_merge($utilisateur, [
                'nom_compte' => $nomCompte, 'email' => $email, 'role' => $role, 'ville_id' => $villeId,
            ]);
            $this->afficherFormulaireModification($saisie, $erreur);
            return;
        }

        Utilisateur::modifier((int) $id, [
            'nom_compte' => $nomCompte,
            'email'      => $email,
            'role'       => $role,
            'ville_id'   => $villeId,
        ]);
        if ($motDePasse !== '') {
            Utilisateur::changerMotDePasse((int) $id, $motDePasse);
        }

        Flash::succes('Compte « ' . $nomCompte . ' » modifié.');
        $this->rediriger('/admin/utilisateurs');
    }

    public function supprimer(string $id): void
    {
        $utilisateurId = (int) $id;

        if ($utilisateurId === Auth::utilisateur()['id']) {
            Flash::erreur('Tu ne peux pas supprimer ton propre compte.');
            $this->rediriger('/admin/utilisateurs');
        }

        try {
            MembreEquipe::retirerUtilisateurPartout($utilisateurId);
            Utilisateur::supprimer($utilisateurId);
            Flash::succes('Compte supprimé.');
        } catch (SupabaseException $e) {
            Flash::erreur('Suppression refusée par la base de données : ' . $e->getMessage());
        }
        $this->rediriger('/admin/utilisateurs');
    }

    /** Règles communes à la création et à la modification. Renvoie un message d'erreur, ou null si tout va bien. */
    private function valider(string $nomCompte, string $email, string $role, ?int $villeId, string $motDePasse, bool $motDePasseObligatoire): ?string
    {
        if ($nomCompte === '' || $email === '') {
            return 'Le nom et l\'email sont obligatoires.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'L\'adresse email n\'est pas valide.';
        }
        if (!isset(Auth::ROLES[$role]) && $role !== 'admin') {
            return 'Rôle inconnu.';
        }
        if ($role === Auth::ROLE_MANAGER && $villeId === null) {
            return 'Un manager doit être rattaché à une ville.';
        }
        if ($villeId !== null && Ville::trouver($villeId) === null) {
            return 'Ville inconnue.';
        }
        if ($motDePasseObligatoire && $motDePasse === '') {
            return 'Le mot de passe est obligatoire.';
        }
        if ($motDePasse !== '' && strlen($motDePasse) < 8) {
            return 'Le mot de passe doit faire au moins 8 caractères.';
        }
        return null;
    }
}
