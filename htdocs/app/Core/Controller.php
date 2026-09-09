<?php
/**
 * Contrôleur de base : fournit une méthode afficher() qui injecte des
 * variables dans une vue, elle-même enveloppée par le layout commun,
 * ainsi que quelques utilitaires partagés par tous les contrôleurs.
 */
class Controller
{
    protected function afficher(string $vue, array $donnees = []): void
    {
        extract($donnees); // transforme ['titre' => 'X'] en variable $titre

        $cheminVue = __DIR__ . '/../Views/' . $vue . '.php';

        // Le layout inclut la navbar/footer et charge le contenu de la vue
        require __DIR__ . '/../Views/partials/layout.php';
    }

    /** Redirige le navigateur vers une autre adresse et arrête le script (schéma Post/Redirect/Get) */
    protected function rediriger(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /** Affiche la page 404 et arrête le script */
    protected function introuvable(): void
    {
        http_response_code(404);
        require __DIR__ . '/../Views/erreur_404.php';
        exit;
    }

    /** Lit un champ texte du formulaire POST (chaîne vide s'il est absent), sans espaces autour */
    protected function champ(string $nom): string
    {
        return trim((string) ($_POST[$nom] ?? ''));
    }

    /** Lit un champ numérique du formulaire POST ; null si vide, absent ou non numérique */
    protected function champEntier(string $nom): ?int
    {
        $valeur = $this->champ($nom);
        return ($valeur === '' || !is_numeric($valeur)) ? null : (int) $valeur;
    }
}
