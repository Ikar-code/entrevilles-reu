<?php
/**
 * Protection CSRF (Cross-Site Request Forgery).
 *
 * Principe : chaque session reçoit un jeton secret aléatoire. Tous les
 * formulaires POST du site doivent renvoyer ce jeton (champ caché). Un site
 * malveillant qui ferait soumettre un formulaire à l'insu de l'utilisateur ne
 * connaît pas le jeton : la requête est refusée par le routeur (voir Router).
 */
class Csrf
{
    /** Renvoie le jeton de la session (le crée au premier appel) */
    public static function jeton(): string
    {
        if (empty($_SESSION['csrf_jeton'])) {
            $_SESSION['csrf_jeton'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_jeton'];
    }

    /** Champ caché à insérer dans chaque formulaire : <?= Csrf::champ() ?> */
    public static function champ(): string
    {
        return '<input type="hidden" name="csrf_jeton" value="' . htmlspecialchars(self::jeton()) . '">';
    }

    /** Vérifie que le jeton reçu en POST correspond à celui de la session */
    public static function verifier(): bool
    {
        $recu = $_POST['csrf_jeton'] ?? '';
        // hash_equals compare en temps constant (évite les attaques par mesure de temps)
        return is_string($recu) && $recu !== '' && hash_equals(self::jeton(), $recu);
    }
}
