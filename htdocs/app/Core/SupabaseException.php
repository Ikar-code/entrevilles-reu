<?php
/**
 * Erreur renvoyée par SupabaseClient quand la base refuse une requête
 * (clé invalide, table inconnue, contrainte violée…) ou que le réseau échoue.
 * Les contrôleurs peuvent l'attraper avec try/catch pour afficher un message
 * propre au lieu d'arrêter brutalement le script.
 */
class SupabaseException extends RuntimeException
{
    private int $codeHttp;

    public function __construct(string $message, int $codeHttp = 0)
    {
        parent::__construct($message);
        $this->codeHttp = $codeHttp;
    }

    public function codeHttp(): int
    {
        return $this->codeHttp;
    }

    /**
     * true si la base a refusé à cause d'une contrainte d'intégrité
     * (ex. suppression d'une ville encore référencée par des équipes,
     * ou email en doublon). PostgREST renvoie alors un code HTTP 409.
     */
    public function estConflit(): bool
    {
        return $this->codeHttp === 409;
    }
}
