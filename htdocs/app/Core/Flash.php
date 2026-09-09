<?php
/**
 * Messages "flash" : un message stocké en session, affiché UNE fois sur la
 * page suivante puis effacé. Utilisé après une redirection (schéma
 * Post/Redirect/Get) pour dire "Ville créée." ou "Suppression impossible."
 */
class Flash
{
    public static function succes(string $message): void
    {
        self::ajouter('succes', $message);
    }

    public static function erreur(string $message): void
    {
        self::ajouter('erreur', $message);
    }

    private static function ajouter(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    /** Renvoie les messages en attente et les efface de la session */
    public static function recuperer(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }
}
