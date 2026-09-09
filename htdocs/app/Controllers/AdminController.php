<?php
/**
 * Espace super administrateur : tableau de bord, villes et sports.
 * Les utilisateurs, épreuves et équipes ont leur propre contrôleur
 * (AdminUtilisateurController, AdminEpreuveController, AdminEquipeController).
 */
class AdminController extends Controller
{
    public function __construct()
    {
        // Toutes les actions de ce contrôleur exigent d'être super admin
        Auth::exigerSuperAdmin();
    }

    public function tableauDeBord(): void
    {
        $this->afficher('admin/tableau_de_bord', [
            'titre' => 'Administration',
            'stats' => [
                'villes'       => count(Ville::toutes()),
                'sports'       => count(Sport::tous()),
                'utilisateurs' => count(Utilisateur::tous()),
                'equipes'      => count(Equipe::toutes()),
                'epreuves'     => count(Epreuve::toutes()),
            ],
            'epreuvesEnCours' => Epreuve::parStatut('en_cours'),
            'managers'        => Utilisateur::parRole(Auth::ROLE_MANAGER),
        ]);
    }

    // ------------------------------------------------------------------
    // Villes
    // ------------------------------------------------------------------

    public function villes(): void
    {
        $this->afficher('admin/villes/index', [
            'titre'  => 'Villes',
            'villes' => Ville::toutes(),
        ]);
    }

    public function creerVille(): void
    {
        $nom = $this->champ('nom');

        if ($nom === '') {
            Flash::erreur('Le nom de la ville est obligatoire.');
            $this->rediriger('/admin/villes');
        }

        Ville::creer($nom);
        Flash::succes('Ville « ' . $nom . ' » créée.');
        $this->rediriger('/admin/villes');
    }

    public function modifierVille(string $id): void
    {
        $ville = Ville::trouver((int) $id);
        if ($ville === null) {
            $this->introuvable();
        }

        $this->afficher('admin/villes/modifier', ['titre' => 'Modifier la ville', 'ville' => $ville]);
    }

    public function enregistrerVille(string $id): void
    {
        $ville = Ville::trouver((int) $id);
        if ($ville === null) {
            $this->introuvable();
        }

        $nom = $this->champ('nom');
        if ($nom === '') {
            $this->afficher('admin/villes/modifier', [
                'titre'  => 'Modifier la ville',
                'ville'  => $ville,
                'erreur' => 'Le nom est obligatoire.',
            ]);
            return;
        }

        Ville::modifier((int) $id, $nom);
        Flash::succes('Ville renommée en « ' . $nom . ' ».');
        $this->rediriger('/admin/villes');
    }

    public function supprimerVille(string $id): void
    {
        $villeId = (int) $id;

        // On refuse tant que des données y sont rattachées (plutôt que de tout supprimer en cascade)
        if (Equipe::parVille($villeId) !== []) {
            Flash::erreur('Impossible de supprimer cette ville : des équipes y sont rattachées.');
            $this->rediriger('/admin/villes');
        }
        if (Epreuve::parVille($villeId) !== []) {
            Flash::erreur('Impossible de supprimer cette ville : des épreuves y sont programmées.');
            $this->rediriger('/admin/villes');
        }
        if (Utilisateur::parVille($villeId) !== []) {
            Flash::erreur('Impossible de supprimer cette ville : des comptes (joueurs ou manager) y sont rattachés.');
            $this->rediriger('/admin/villes');
        }

        try {
            Ville::supprimer($villeId);
            Flash::succes('Ville supprimée.');
        } catch (SupabaseException $e) {
            Flash::erreur('Suppression refusée par la base de données : ' . $e->getMessage());
        }
        $this->rediriger('/admin/villes');
    }

    // ------------------------------------------------------------------
    // Sports
    // ------------------------------------------------------------------

    public function sports(): void
    {
        $this->afficher('admin/sports/index', [
            'titre'  => 'Sports',
            'sports' => Sport::tous(),
        ]);
    }

    public function creerSport(): void
    {
        $nom = $this->champ('nom');
        $description = $this->champ('description') ?: null;

        if ($nom === '') {
            Flash::erreur('Le nom du sport est obligatoire.');
            $this->rediriger('/admin/sports');
        }

        Sport::creer($nom, $description);
        Flash::succes('Sport « ' . $nom . ' » créé.');
        $this->rediriger('/admin/sports');
    }

    public function modifierSport(string $id): void
    {
        $sport = Sport::trouver((int) $id);
        if ($sport === null) {
            $this->introuvable();
        }

        $this->afficher('admin/sports/modifier', ['titre' => 'Modifier le sport', 'sport' => $sport]);
    }

    public function enregistrerSport(string $id): void
    {
        $sport = Sport::trouver((int) $id);
        if ($sport === null) {
            $this->introuvable();
        }

        $nom = $this->champ('nom');
        $description = $this->champ('description') ?: null;

        if ($nom === '') {
            $this->afficher('admin/sports/modifier', [
                'titre'  => 'Modifier le sport',
                'sport'  => $sport,
                'erreur' => 'Le nom est obligatoire.',
            ]);
            return;
        }

        Sport::modifier((int) $id, $nom, $description);
        Flash::succes('Sport modifié.');
        $this->rediriger('/admin/sports');
    }

    public function supprimerSport(string $id): void
    {
        $sportId = (int) $id;

        if (Equipe::parSport($sportId) !== []) {
            Flash::erreur('Impossible de supprimer ce sport : des équipes le pratiquent.');
            $this->rediriger('/admin/sports');
        }
        if (Epreuve::parSport($sportId) !== []) {
            Flash::erreur('Impossible de supprimer ce sport : des épreuves lui sont associées.');
            $this->rediriger('/admin/sports');
        }

        try {
            Sport::supprimer($sportId);
            Flash::succes('Sport supprimé.');
        } catch (SupabaseException $e) {
            Flash::erreur('Suppression refusée par la base de données : ' . $e->getMessage());
        }
        $this->rediriger('/admin/sports');
    }
}
