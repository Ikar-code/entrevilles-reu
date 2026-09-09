(function () {
    const cle = 'entrevilles-theme';
    const racine = document.documentElement;

    function appliquer(theme) {
        if (theme === 'clair') {
            racine.setAttribute('data-theme', 'clair');
        } else {
            racine.removeAttribute('data-theme');
        }
    }

    appliquer(localStorage.getItem(cle));

    document.addEventListener('DOMContentLoaded', function () {
        const bouton = document.getElementById('basculeTheme');
        if (!bouton) return;

        bouton.addEventListener('click', function () {
            const actuel = localStorage.getItem(cle) === 'clair' ? 'sombre' : 'clair';
            localStorage.setItem(cle, actuel);
            appliquer(actuel);
        });
    });
})();
