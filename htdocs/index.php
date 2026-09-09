<?php
/**
 * Point d'entrée unique de l'application (front controller).
 * Toutes les requêtes sont redirigées ici par .htaccess.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/autoload.php';
require_once __DIR__ . '/app/routes.php';

try {
    Router::traiter();
} catch (Throwable $e) {
    // Erreur non prévue (base de données injoignable, bug…) : page d'erreur propre
    http_response_code(500);
    $messageErreur = $e->getMessage();
    require __DIR__ . '/app/Views/erreur_500.php';
}
