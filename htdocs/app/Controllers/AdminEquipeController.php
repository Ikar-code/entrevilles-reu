<?php
/**
 * Gestion des équipes et de leurs membres.
 * Accessible au super admin (toutes les équipes) ET aux managers de ville
 * (uniquement les équipes de leur ville : vérifié par chargerEquipe()).
 */
class AdminEquipeController extends Controller
{
    public function __construct()
    {
        Auth::exigerAdmin(); // super admin OU manager
    }

    public function liste(): void
    {
        $this->afficherListe();
    }

    private function afficherListe(?string $erreur = null, array $saisie = []): void
    {
        $villeGeree = Auth::villeGeree(); // null pour un super admin

        $this->afficher('admin/equipes/index', [
            'titre'      => 'Équipes',
            'equipes'    => $villeGeree !== null ? Equipe::parVille($villeGeree) : Equipe::toutes(),
            'sports'     => Sport::tous(),
            'villes'     => $villeGeree !== null ? [] : Ville::toutes(),
            'villeGeree' => $villeGeree !== null ? Ville::trouver($villeGeree) : null,
            'erreur'     => $erreur,
            'saisie'     => $saisie,
        ]);
    }

    public function creer(): void
    {
        $nom     = $this->champ('nom');
        $sportId = $this->champEntier('sport_id');
        // Un manager ne peut créer que dans sa ville ; le super admin choisit
        $villeId = Auth::estManager() ? Auth::villeGeree() : $this->champEntier('ville_id');

        $erreur = null;
        if ($nom === '') {
            $erreur = 'Le nom de l\'équipe est obligatoire.';
        } elseif ($sportId === null || Sport::trouver($sportId) === null) {
            $erreur = 'Choisis un sport.';
        } elseif ($villeId === null || Ville::trouver($villeId) === null) {
            $erreur = 'Choisis une ville.';
        }
        if ($erreur !== null) {
            $this->afficherListe($erreur, $_POST);
            return;
        }

        $id = Equipe::creer($nom, $villeId, $sportId);
        Flash::succes('Équipe « ' . $nom . ' » créée. Tu peux maintenant ajouter des membres.');
        $this->rediriger('/admin/equipes/' . $id);
    }

    public function supprimer(string $id): void
    {
        $equipe = $this->chargerEquipe($id);

        try {
            MembreEquipe::retirerTous((int) $equipe['id']);
            Participation::retirerParEquipe((int) $equipe['id']);
            Equipe::supprimer((int) $equipe['id']);
            Flash::succes('Équipe « ' . $equipe['nom'] . ' » supprimée.');
        } catch (SupabaseException $e) {
            Flash::erreur('Suppression refusée par la base de données : ' . $e->getMessage());
        }
        $this->rediriger('/admin/equipes');
    }

    // ------------------------------------------------------------------
    // Page de gestion d'une équipe : membres + inscriptions aux épreuves
    // ------------------------------------------------------------------

    public function membres(string $id): void
    {
        $equipe   = $this->chargerEquipe($id);
        $equipeId = (int) $equipe['id'];

        $membres    = MembreEquipe::parEquipe($equipeId);
        $idsMembres = array_map(fn(array $m) => (int) $m['utilisateur_id'], $membres);

        // Comptes que l'on peut ajouter : super admin → tous ; manager → ceux de sa ville (ou sans ville)
        $comptes = Auth::estManager()
            ? Utilisateur::parVille((int) Auth::villeGeree(), true)
            : Utilisateur::tous();
        $candidats = array_filter($comptes, fn(array $u) => !in_array((int) $u['id'], $idsMembres, true));

        $participations = Participation::parEquipe($equipeId);
        $idsEpreuves    = array_map(fn(array $p) => (int) $p['epreuve_id'], $participations);

        // Épreuves proposées : à venir, du même sport, pas encore inscrite
        $epreuvesDisponibles = array_filter(
            Epreuve::aVenir(),
            fn(array $e) => (int) $e['sport_id'] === (int) $equipe['sport_id'] && !in_array((int) $e['id'], $idsEpreuves, true)
        );

        $this->afficher('admin/equipes/membres', [
            'titre'                => 'Équipe ' . $equipe['nom'],
            'equipe'               => $equipe,
            'membres'              => $membres,
            'candidats'            => $candidats,
            'rolesInternes'        => MembreEquipe::ROLES,
            'participations'       => $participations,
            'epreuvesDisponibles'  => $epreuvesDisponibles,
            'statutsParticipation' => Participation::STATUTS,
            'statutsEpreuve'       => Epreuve::STATUTS,
        ]);
    }

    public function ajouterMembre(string $id): void
    {
        $equipe   = $this->chargerEquipe($id);
        $equipeId = (int) $equipe['id'];

        $utilisateurId = $this->champEntier('utilisateur_id');
        $role          = $this->champ('role_interne') ?: 'joueur';
        $utilisateur   = $utilisateurId !== null ? Utilisateur::trouver($utilisateurId) : null;

        if ($utilisateur === null) {
            Flash::erreur('Choisis un compte.');
            $this->retourEquipe($equipeId);
        }
        if (!isset(MembreEquipe::ROLES[$role])) {
            Flash::erreur('Rôle inconnu.');
            $this->retourEquipe($equipeId);
        }
        // Un manager ne recrute que des comptes de sa ville (ou sans ville)
        if (Auth::estManager() && $utilisateur['ville_id'] !== null && (int) $utilisateur['ville_id'] !== Auth::villeGeree()) {
            Flash::erreur('Ce compte est rattaché à une autre ville.');
            $this->retourEquipe($equipeId);
        }
        if (MembreEquipe::estMembre($equipeId, $utilisateurId)) {
            Flash::erreur('Ce compte fait déjà partie de l\'équipe.');
            $this->retourEquipe($equipeId);
        }

        MembreEquipe::ajouter($equipeId, $utilisateurId, $role);
        Flash::succes($utilisateur['nom_compte'] . ' ajouté à l\'équipe.');
        $this->retourEquipe($equipeId);
    }

    public function modifierMembre(string $id): void
    {
        $equipe   = $this->chargerEquipe($id);
        $equipeId = (int) $equipe['id'];

        $utilisateurId = $this->champEntier('utilisateur_id');
        $role          = $this->champ('role_interne');

        if ($utilisateurId === null || !MembreEquipe::estMembre($equipeId, $utilisateurId)) {
            Flash::erreur('Membre introuvable.');
            $this->retourEquipe($equipeId);
        }
        if (!isset(MembreEquipe::ROLES[$role])) {
            Flash::erreur('Rôle inconnu.');
            $this->retourEquipe($equipeId);
        }

        MembreEquipe::modifier($equipeId, $utilisateurId, [
            'role_interne' => $role,
            'penalite'     => $this->champ('penalite') ?: null,
            'recompense'   => $this->champ('recompense') ?: null,
        ]);
        Flash::succes('Membre mis à jour.');
        $this->retourEquipe($equipeId);
    }

    public function retirerMembre(string $id): void
    {
        $equipe   = $this->chargerEquipe($id);
        $equipeId = (int) $equipe['id'];

        $utilisateurId = $this->champEntier('utilisateur_id');
        if ($utilisateurId === null || !MembreEquipe::estMembre($equipeId, $utilisateurId)) {
            Flash::erreur('Membre introuvable.');
            $this->retourEquipe($equipeId);
        }

        MembreEquipe::retirer($equipeId, $utilisateurId);
        Flash::succes('Membre retiré de l\'équipe.');
        $this->retourEquipe($equipeId);
    }

    public function inscrireEpreuve(string $id): void
    {
        $equipe   = $this->chargerEquipe($id);
        $equipeId = (int) $equipe['id'];

        $epreuveId = $this->champEntier('epreuve_id');
        $epreuve   = $epreuveId !== null ? Epreuve::trouver($epreuveId) : null;

        if ($epreuve === null) {
            Flash::erreur('Choisis une épreuve.');
            $this->retourEquipe($equipeId);
        }
        if ($epreuve['statut'] !== 'a_venir') {
            Flash::erreur('Les inscriptions sont closes pour cette épreuve.');
            $this->retourEquipe($equipeId);
        }
        if ((int) $epreuve['sport_id'] !== (int) $equipe['sport_id']) {
            Flash::erreur('Cette épreuve concerne un autre sport.');
            $this->retourEquipe($equipeId);
        }
        if (Participation::existe($epreuveId, $equipeId)) {
            Flash::erreur('L\'équipe est déjà inscrite à cette épreuve.');
            $this->retourEquipe($equipeId);
        }

        // Validée d'office par un super admin ; en attente de validation pour un manager
        $statut = Auth::estSuperAdmin() ? 'validee' : 'en_attente';
        Participation::inscrire($epreuveId, $equipeId, $statut);
        Flash::succes(
            'Équipe inscrite à l\'épreuve ' . $epreuve['sport_nom'] . ' à ' . $epreuve['ville_nom']
            . ($statut === 'en_attente' ? ' (en attente de validation par un administrateur).' : '.')
        );
        $this->retourEquipe($equipeId);
    }

    public function retirerInscription(string $id): void
    {
        $equipe   = $this->chargerEquipe($id);
        $equipeId = (int) $equipe['id'];

        $epreuveId = $this->champEntier('epreuve_id');
        if ($epreuveId === null || !Participation::existe($epreuveId, $equipeId)) {
            Flash::erreur('Inscription introuvable.');
            $this->retourEquipe($equipeId);
        }

        Participation::retirer($epreuveId, $equipeId);
        Flash::succes('Inscription retirée.');
        $this->retourEquipe($equipeId);
    }

    /** Charge l'équipe demandée : 404 si absente, 403 si le compte connecté ne gère pas sa ville */
    private function chargerEquipe(string $id): array
    {
        $equipe = Equipe::trouver((int) $id);
        if ($equipe === null) {
            $this->introuvable();
        }
        Auth::exigerGestionVille((int) $equipe['ville_id']);
        return $equipe;
    }

    private function retourEquipe(int $equipeId): void
    {
        $this->rediriger('/admin/equipes/' . $equipeId);
    }
}
