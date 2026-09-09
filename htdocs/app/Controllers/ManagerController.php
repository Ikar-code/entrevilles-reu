<?php
/**
 * Espace "Ma ville" du manager : vue d'ensemble des équipes de sa ville,
 * inscriptions en attente et prochaines épreuves. La gestion proprement dite
 * (créer une équipe, ajouter des membres, inscrire à une épreuve) se fait
 * dans AdminEquipeController, partagé avec le super admin.
 */
class ManagerController extends Controller
{
    public function __construct()
    {
        Auth::exigerManager();
    }

    public function index(): void
    {
        $villeId = Auth::villeGeree();
        $ville   = $villeId !== null ? Ville::trouver($villeId) : null;
        $equipes = $ville !== null ? Equipe::parVille($villeId) : [];

        // Inscriptions de la ville en attente de validation par un super admin
        $enAttente = [];
        foreach ($equipes as $equipe) {
            foreach (Participation::parEquipe((int) $equipe['id']) as $participation) {
                if ($participation['statut'] === 'en_attente') {
                    $participation['equipe_nom'] = $equipe['nom'];
                    $enAttente[] = $participation;
                }
            }
        }

        $this->afficher('gestion/index', [
            'titre'          => 'Ma ville',
            'ville'          => $ville,
            'equipes'        => $equipes,
            'enAttente'      => $enAttente,
            'epreuvesAVenir' => Epreuve::aVenir(),
        ]);
    }
}
