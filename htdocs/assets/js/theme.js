/**
 * Bascule entre le thème sombre (par défaut) et le thème clair.
 *
 * - Le thème clair s'active en posant l'attribut data-theme="clair" sur <html> :
 *   la feuille de style (style.css) redéfinit alors ses variables de couleur.
 * - Le choix est mémorisé dans le navigateur (localStorage) sous la clé
 *   "entrevilles-theme", il est donc conservé d'une visite à l'autre.
 * - Ce script est chargé dans <head> pour appliquer le thème AVANT l'affichage
 *   de la page (sinon on verrait un flash sombre puis clair).
 * - Le bouton #basculeTheme (dans la barre de navigation) n'existe pas encore
 *   quand ce script s'exécute : on attend l'événement DOMContentLoaded.
 */
(function () {
    var CLE = 'entrevilles-theme';
    var racine = document.documentElement; // la balise <html>

    function themeMemorise() {
        try {
            return localStorage.getItem(CLE);
        } catch (e) {
            return null; // stockage indisponible (navigation privée stricte, etc.)
        }
    }

    function memoriser(theme) {
        try {
            localStorage.setItem(CLE, theme);
        } catch (e) {
            // Sans stockage, le choix ne survivra pas au changement de page : pas grave
        }
    }

    function appliquer(theme) {
        if (theme === 'clair') {
            racine.setAttribute('data-theme', 'clair');
        } else {
            racine.removeAttribute('data-theme');
        }
    }

    function themeActuel() {
        return racine.getAttribute('data-theme') === 'clair' ? 'clair' : 'sombre';
    }

    // Le bouton annonce l'action possible : en sombre il propose le clair, et inversement
    function mettreAJourBouton(bouton) {
        var clair = themeActuel() === 'clair';
        bouton.textContent = clair ? '🌙 Thème sombre' : '☀️ Thème clair';
        bouton.title = clair ? 'Passer au thème sombre' : 'Passer au thème clair';
        bouton.setAttribute('aria-pressed', clair ? 'true' : 'false');
    }

    // 1. Dès le chargement du script : appliquer le thème mémorisé
    appliquer(themeMemorise());

    // 2. Une fois la page construite : brancher le bouton
    document.addEventListener('DOMContentLoaded', function () {
        var bouton = document.getElementById('basculeTheme');
        if (!bouton) {
            return;
        }

        mettreAJourBouton(bouton);

        bouton.addEventListener('click', function () {
            var nouveau = themeActuel() === 'clair' ? 'sombre' : 'clair';
            appliquer(nouveau);
            memoriser(nouveau);
            mettreAJourBouton(bouton);
        });
    });
})();
