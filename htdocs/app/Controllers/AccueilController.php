<?php
class AccueilController extends Controller
{
    public function index(): void
    {
        $villes    = Ville::toutes();
        $sports    = Sport::tous();
        $equipes   = Equipe::toutes();
        $epreuves  = Epreuve::toutes();

        $epreuvesParStatut = ['a_venir' => 0, 'en_cours' => 0, 'terminee' => 0];
        foreach ($epreuves as $epreuve) {
            $statut = $epreuve['statut'] ?? null;
            if (isset($epreuvesParStatut[$statut])) {
                $epreuvesParStatut[$statut]++;
            }
        }

        $this->afficher('accueil/index', [
            'titre'             => 'Accueil',
            'nombreVilles'      => count($villes),
            'nombreSports'      => count($sports),
            'nombreEquipes'     => count($equipes),
            'nombreEpreuves'    => count($epreuves),
            'epreuvesParStatut' => $epreuvesParStatut,
            'epreuvesAVenir'    => Epreuve::aVenir(),
        ]);
    }
}
