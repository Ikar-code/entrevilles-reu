<?php
/**
 * Petites fonctions d'affichage partagées par les vues.
 */
class Format
{
    /** Date lisible ("20/09/2026 15:00"), ou "—" si la valeur est vide */
    public static function dateHeure(?string $valeur, string $format = 'd/m/Y H:i'): string
    {
        if (!$valeur) {
            return '—';
        }
        try {
            return (new DateTime($valeur))->format($format);
        } catch (Exception $e) {
            return $valeur;
        }
    }

    /** Valeur pour un champ <input type="datetime-local"> : "2026-09-20T15:00" (vide si pas de date) */
    public static function dateHeureLocal(?string $valeur): string
    {
        if (!$valeur) {
            return '';
        }
        try {
            return (new DateTime($valeur))->format('Y-m-d\TH:i');
        } catch (Exception $e) {
            return '';
        }
    }
}
