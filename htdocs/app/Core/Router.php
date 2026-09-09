<?php
/**
 * Routeur minimaliste : associe une URL à [Contrôleur, méthode].
 * Toutes les requêtes passent par index.php qui appelle Router::traiter().
 */
class Router
{
    private static array $routes = [];

    public static function ajouter(string $methode, string $chemin, string $controleur, string $action): void
    {
        self::$routes[] = [
            'methode'    => $methode,
            'chemin'     => $chemin,
            'controleur' => $controleur,
            'action'     => $action,
        ];
    }

    public static function get(string $chemin, string $controleur, string $action): void
    {
        self::ajouter('GET', $chemin, $controleur, $action);
    }

    public static function post(string $chemin, string $controleur, string $action): void
    {
        self::ajouter('POST', $chemin, $controleur, $action);
    }

    public static function traiter(): void
    {
        $methode = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Tout formulaire POST doit porter le jeton CSRF de la session (voir Core/Csrf.php)
        if ($methode === 'POST' && !Csrf::verifier()) {
            http_response_code(403);
            $motifRefus = 'Formulaire invalide ou expiré (jeton de sécurité manquant). Recharge la page et réessaie.';
            require __DIR__ . '/../Views/erreur_403.php';
            return;
        }

        foreach (self::$routes as $route) {
            if ($route['methode'] !== $methode) {
                continue;
            }

            // transforme /epreuves/{id} en expression régulière
            $motif = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $route['chemin']);
            $motif = '#^' . $motif . '$#';

            if (preg_match($motif, $uri, $correspondances)) {
                array_shift($correspondances); // enlève la correspondance complète
                $controleur = new $route['controleur']();
                call_user_func_array([$controleur, $route['action']], $correspondances);
                return;
            }
        }

        http_response_code(404);
        require __DIR__ . '/../Views/erreur_404.php';
    }
}
