<?php
/**
 * Gestion des épreuves par le super admin : création, modification,
 * changement de statut (à venir / en cours / terminée), suppression,
 * et gestion des participations (inscriptions, validation, résultats).
 */
class AdminEpreuveController extends Controller
{
    public function __construct()
    {
        Auth::exigerSuperAdmin();
    }

    public function liste(): void
    {
        $this->afficherListe();
    }

    private function afficherListe(?string $erreur = null, array $saisie = []): void
    {
        $this->afficher('admin/epreuves/index', [
            'titre'    => 'Épreuves',
            'epreuves' => Epreuve::toutes(),
            'sports'   => Sport::tous(),
            'villes'   => Ville::toutes(),
            'statuts'  => Epreuve::STATUTS,
            'erreur'   => $erreur,
            'saisie'   => $saisie,
        ]);
    }

    public function creer(): void
    {
        [$sportId, $villeId, $dateHeure, $statut, $erreur] = $this->lireFormulaire();
        if ($erreur !== null) {
            $this->afficherListe($erreur, $_POST);
            return;
        }

        Epreuve::creer($sportId, $villeId, $dateHeure, $statut);
        Flash::succes('Épreuve créée.');
        $this->rediriger('/admin/epreuves');
    }

    public function modifier(string $id): void
    {
        $epreuve = Epreuve::trouver((int) $id);
        if ($epreuve === null) {
            $this->introuvable();
        }

        $this->afficherFormulaireModification($epreuve);
    }

    private function afficherFormulaireModification(array $epreuve, ?string $erreur = null): void
    {
        $this->afficher('admin/epreuves/modifier', [
            'titre'   => 'Modifier l\'épreuve',
            'epreuve' => $epreuve,
            'sports'  => Sport::tous(),
            'villes'  => Ville::toutes(),
            'statuts' => Epreuve::STATUTS,
            'erreur'  => $erreur,
        ]);
    }

    public function enregistrer(string $id): void
    {
        $epreuve = Epreuve::trouver((int) $id);
        if ($epreuve === null) {
            $this->introuvable();
        }

        [$sportId, $villeId, $dateHeure, $statut, $erreur] = $this->lireFormulaire();
        if ($erreur !== null) {
            $saisie = array_merge($epreuve, [
                'sport_id' => $sportId, 'ville_id' => $villeId, 'date_heure' => $dateHeure, 'statut' => $statut,
            ]);
            $this->afficherFormulaireModification($saisie, $erreur);
            return;
        }

        Epreuve::modifier((int) $id, $sportId, $villeId, $dateHeure, $statut);
        Flash::succes('Épreuve modifiée.');
        $this->rediriger('/admin/epreuves');
    }

    /** Changement rapide de statut depuis la liste (à venir → en cours → terminée) */
    public function changerStatut(string $id): void
    {
        $epreuve = Epreuve::trouver((int) $id);
        if ($epreuve === null) {
            $this->introuvable();
        }

        $statut = $this->champ('statut');
        if (!isset(Epreuve::STATUTS[$statut])) {
            Flash::erreur('Statut inconnu.');
            $this->rediriger('/admin/epreuves');
        }

        Epreuve::changerStatut((int) $id, $statut);
        Flash::succes('Épreuve passée au statut « ' . Epreuve::STATUTS[$statut] . ' ».');
        $this->rediriger('/admin/epreuves');
    }

    public function supprimer(string $id): void
    {
        try {
            Participation::retirerParEpreuve((int) $id);
            Epreuve::supprimer((int) $id);
            Flash::succes('Épreuve supprimée, ainsi que ses participations.');
        } catch (SupabaseException $e) {
            Flash::erreur('Suppression refusée par la base de données : ' . $e->getMessage());
        }
        $this->rediriger('/admin/epreuves');
    }

    // ------------------------------------------------------------------
    // Participations (équipes inscrites, validation, résultats)
    // ------------------------------------------------------------------

    public function participations(string $id): void
    {
        $epreuve = Epreuve::trouver((int) $id);
        if ($epreuve === null) {
            $this->introuvable();
        }

        $participations = Participation::parEpreuve((int) $id);
        $dejaInscrites = array_map(fn(array $p) => (int) $p['equipe_id'], $participations);

        // Équipes proposées à l'inscription : celles du même sport, pas encore inscrites
        $equipesDisponibles = array_filter(
            Equipe::toutes(),
            fn(array $e) => (int) $e['sport_id'] === (int) $epreuve['sport_id'] && !in_array((int) $e['id'], $dejaInscrites, true)
        );

        $this->afficher('admin/epreuves/participations', [
            'titre'              => 'Participations — ' . $epreuve['sport_nom'] . ' à ' . $epreuve['ville_nom'],
            'epreuve'            => $epreuve,
            'participations'     => $participations,
            'equipesDisponibles' => $equipesDisponibles,
            'statuts'            => Participation::STATUTS,
            'statutsEpreuve'     => Epreuve::STATUTS,
        ]);
    }

    public function inscrire(string $id): void
    {
        $epreuve = Epreuve::trouver((int) $id);
        if ($epreuve === null) {
            $this->introuvable();
        }
        $retour = '/admin/epreuves/' . (int) $id . '/participations';

        $equipeId = $this->champEntier('equipe_id');
        $equipe = $equipeId !== null ? Equipe::trouver($equipeId) : null;
        if ($equipe === null) {
            Flash::erreur('Choisis une équipe.');
            $this->rediriger($retour);
        }
        if ((int) $equipe['sport_id'] !== (int) $epreuve['sport_id']) {
            Flash::erreur('Cette équipe pratique un autre sport que celui de l\'épreuve.');
            $this->rediriger($retour);
        }
        if (Participation::existe((int) $id, $equipeId)) {
            Flash::erreur('Cette équipe est déjà inscrite.');
            $this->rediriger($retour);
        }

        // Inscrite par le super admin : validée d'office
        Participation::inscrire((int) $id, $equipeId, 'validee');
        Flash::succes('Équipe « ' . $equipe['nom'] . ' » inscrite.');
        $this->rediriger($retour);
    }

    public function resultat(string $id): void
    {
        $retour = '/admin/epreuves/' . (int) $id . '/participations';
        $equipeId   = $this->champEntier('equipe_id');
        $score      = $this->champEntier('score');
        $classement = $this->champEntier('classement');

        if ($equipeId === null || !Participation::existe((int) $id, $equipeId)) {
            Flash::erreur('Participation introuvable.');
            $this->rediriger($retour);
        }
        if (($score !== null && $score < 0) || ($classement !== null && $classement < 1)) {
            Flash::erreur('Le score doit être ≥ 0 et le classement ≥ 1.');
            $this->rediriger($retour);
        }

        Participation::saisirResultat((int) $id, $equipeId, $score, $classement);
        Flash::succes('Résultat enregistré.');
        $this->rediriger($retour);
    }

    public function validerParticipation(string $id): void
    {
        $retour = '/admin/epreuves/' . (int) $id . '/participations';
        $equipeId = $this->champEntier('equipe_id');

        if ($equipeId === null || !Participation::existe((int) $id, $equipeId)) {
            Flash::erreur('Participation introuvable.');
            $this->rediriger($retour);
        }

        Participation::validerInscription((int) $id, $equipeId);
        Flash::succes('Inscription validée.');
        $this->rediriger($retour);
    }

    public function retirerParticipation(string $id): void
    {
        $retour = '/admin/epreuves/' . (int) $id . '/participations';
        $equipeId = $this->champEntier('equipe_id');

        if ($equipeId === null || !Participation::existe((int) $id, $equipeId)) {
            Flash::erreur('Participation introuvable.');
            $this->rediriger($retour);
        }

        Participation::retirer((int) $id, $equipeId);
        Flash::succes('Équipe retirée de l\'épreuve.');
        $this->rediriger($retour);
    }

    /**
     * Lit et valide les champs du formulaire d'épreuve.
     * Renvoie [sportId, villeId, dateHeure (format base ou null), statut, erreur (ou null)].
     */
    private function lireFormulaire(): array
    {
        $sportId    = $this->champEntier('sport_id');
        $villeId    = $this->champEntier('ville_id');
        $statut     = $this->champ('statut') ?: 'a_venir';
        $dateSaisie = $this->champ('date_heure');
        $dateHeure  = null;
        $erreur     = null;

        if ($sportId === null || Sport::trouver($sportId) === null) {
            $erreur = 'Choisis un sport.';
        } elseif ($villeId === null || Ville::trouver($villeId) === null) {
            $erreur = 'Choisis une ville.';
        } elseif (!isset(Epreuve::STATUTS[$statut])) {
            $erreur = 'Statut inconnu.';
        } elseif ($dateSaisie !== '') {
            try {
                // Le champ <input type="datetime-local"> envoie "2026-09-20T15:00"
                $dateHeure = (new DateTime($dateSaisie))->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $erreur = 'La date n\'est pas valide.';
            }
        }

        return [$sportId, $villeId, $dateHeure, $statut, $erreur];
    }
}
