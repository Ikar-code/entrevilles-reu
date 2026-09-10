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

    /**
     * Adresse d'un fichier statique (CSS, JS) avec sa date de modification en
     * paramètre : "/assets/css/style.css?v=1757430000".
     *
     * Pourquoi ("cache busting") : l'hébergeur demande aux navigateurs de garder
     * les fichiers CSS/JS en cache pendant 30 jours. Sans cela, après une mise à
     * jour, les visiteurs continueraient d'utiliser l'ancienne version. Comme la
     * date change à chaque modification du fichier, l'adresse change aussi, et le
     * navigateur retélécharge le fichier.
     */
    public static function asset(string $chemin): string
    {
        $fichier = __DIR__ . '/../../' . ltrim($chemin, '/');
        $version = is_file($fichier) ? (string) filemtime($fichier) : '1';
        return $chemin . '?v=' . $version;
    }
}
