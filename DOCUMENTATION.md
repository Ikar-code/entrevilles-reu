# Documentation pédagogique — Entrevilles-Reu

> **Entrevilles-Reu** est un site web qui organise un championnat sportif entre
> les communes de La Réunion. Il permet d'afficher les épreuves, les équipes,
> leurs membres, les résultats et le classement général des villes.
>
> Ce document explique **tout** le projet, fichier par fichier, fonction par
> fonction, variable par variable. Il est écrit pour être compris **sans avoir
> jamais programmé**. Les notions techniques sont expliquées au moment où on en
> a besoin, et regroupées dans un glossaire à la fin.

---

## Comment lire ce document

Le document est en trois parties :

| Partie | Sections | Pour qui |
|---|---|---|
| **A. Les bases** | 1 à 3 | Toute personne, même sans connaissance en informatique. À lire en premier. |
| **B. Le code, fichier par fichier** | 4 à 9 | Pour comprendre chaque ligne du projet. |
| **C. Annexes** | 10 à 14 | Base de données, style, mise en ligne, points d'attention, glossaire. |

Conventions utilisées :

- Un mot écrit `comme ceci` est un **nom exact** tiré des fichiers (nom de fichier, de fonction, de variable).
- Chaque fonction est présentée avec quatre rubriques : **À quoi ça sert**, **Ce qu'elle reçoit**, **Ce qu'elle renvoie**, **Comment ça marche**.
- Les extraits de code sont reproduits **tels quels**, puis expliqués ligne par ligne.
- Le projet est écrit en français (noms de fonctions, commentaires), ce qui facilite la lecture.

---

# PARTIE A — LES BASES

## 1. Comment fonctionne un site web

### 1.1 Le navigateur et le serveur : une commande au restaurant

Quand tu ouvres un site, deux ordinateurs discutent :

- **Le navigateur** (Chrome, Firefox, Safari…) : c'est toi, le client au restaurant. Tu passes une commande.
- **Le serveur** : c'est la cuisine. Elle reçoit la commande, prépare le plat et te le renvoie.

La "commande" s'appelle une **requête HTTP** et le "plat" une **réponse HTTP**.

| Élément | Dans la vraie vie | Sur le web |
|---|---|---|
| Ce que tu demandes | "Une pizza margherita" | Une **URL** : `https://entrevilles-reu.freedev.app/equipes/3` |
| Comment tu le demandes | En parlant au serveur | Avec une **méthode HTTP** : `GET` (« donne-moi cette page ») ou `POST` (« voici des informations que je t'envoie », par exemple un formulaire) |
| Ce que tu reçois | Une assiette | Une page **HTML** (le texte et la structure) + du **CSS** (les couleurs, la mise en page) |
| Le ticket de caisse | "Plat servi" ou "Plus de pizza" | Un **code de réponse** : `200` tout va bien, `404` page introuvable, `403` accès interdit, `302` « va plutôt voir cette autre page » (redirection) |

### 1.2 Où intervient PHP ?

Le HTML est un simple texte que le navigateur sait afficher. Mais un site
comme Entrevilles-Reu ne peut pas avoir une page HTML écrite à la main pour
chaque équipe : les équipes changent tout le temps.

**PHP** est un langage de programmation qui s'exécute **sur le serveur**. Il lit
les données (les équipes, les épreuves…), **fabrique** la page HTML
correspondante, et l'envoie au navigateur. Le navigateur ne voit jamais le code
PHP, seulement le HTML produit.

```
Navigateur ──(requête : "GET /equipes/3")──▶ Serveur exécute du PHP
                                                   │  va chercher l'équipe n°3
                                                   │  fabrique le HTML
Navigateur ◀──(réponse : page HTML)──────────────── ┘
```

### 1.3 La base de données : un grand classeur

Les informations (villes, équipes, joueurs, scores…) doivent être conservées
quelque part : c'est le rôle de la **base de données**. Imagine un classeur
Excel :

- Chaque **table** est une feuille (`ville`, `equipe`, `epreuve`…).
- Chaque **colonne** est une information (`nom`, `date_heure`, `score`…).
- Chaque **ligne** est un enregistrement (une ville précise, une équipe précise).
- Chaque ligne a un numéro unique, l'**id** (identifiant). Pour dire « l'équipe de Saint-Denis », l'ordinateur dit « l'équipe dont `ville_id` vaut 4 » : c'est une **clé étrangère**, un renvoi vers une ligne d'une autre table.

Ce projet utilise **Supabase**, un service en ligne qui héberge une base de
données PostgreSQL. Particularité importante : le site ne parle pas à la base
directement, il lui envoie des **requêtes HTTP** (comme un navigateur !) à une
adresse du type `https://xxx.supabase.co/rest/v1/ville`. La base répond au
format **JSON**, un texte structuré facile à lire par un programme :

```json
[
  {"id": 1, "nom": "Saint-Denis"},
  {"id": 2, "nom": "Saint-Pierre"}
]
```

Ici : une liste (`[...]`) de deux objets (`{...}`), chacun avec un `id` et un `nom`.

### 1.4 Les fichiers du projet

Un site PHP est un ensemble de fichiers texte rangés dans des dossiers. Le
serveur lit ces fichiers. Il y a quatre sortes de fichiers dans ce projet :

| Extension | Contenu | Exemple |
|---|---|---|
| `.php` | Code PHP (et parfois HTML mélangé) | `Router.php`, `login.php` |
| `.css` | Mise en forme | `style.css` |
| `.sql` | Instructions pour la base de données | `vue_classement_general.sql` |
| `.htaccess`, `.yml`, `.env` | Configuration | voir sections 4 et 12 |

---

## 2. Le vocabulaire du code (à lire avant la partie B)

Cette section explique les briques de base de PHP. Tu peux la lire d'une
traite, ou y revenir quand un mot te bloque.

### 2.1 Les variables : des boîtes étiquetées

Une **variable** est une boîte avec une étiquette, dans laquelle on range une
valeur. En PHP, toutes les variables commencent par `$`.

```php
$nom = 'Saint-Denis';   // la boîte "nom" contient le texte Saint-Denis
$score = 12;            // la boîte "score" contient le nombre 12
```

Le signe `=` ne veut pas dire « est égal à » mais « **range dans** ». Tout ce
qui suit `//` est un **commentaire** : une note pour les humains, ignorée par
l'ordinateur. Les commentaires longs s'écrivent entre `/*` et `*/`.

Les valeurs ont un **type** :

| Type | Ce que c'est | Exemple |
|---|---|---|
| `string` (chaîne) | Du texte, entre guillemets | `'Football'` |
| `int` (entier) | Un nombre entier | `12` |
| `bool` (booléen) | Vrai ou faux | `true`, `false` |
| `array` (tableau) | Une collection de valeurs (voir 2.2) | `['a', 'b']` |
| `null` | « Rien », « pas de valeur » | `null` |

### 2.2 Les tableaux : des listes et des fiches

Un **tableau** (`array`) regroupe plusieurs valeurs. Il y en a deux formes :

```php
// Une liste : les cases sont numérotées à partir de 0
$villes = ['Saint-Denis', 'Saint-Pierre'];
echo $villes[0];   // affiche Saint-Denis

// Une fiche (tableau associatif) : les cases ont un nom (une "clé")
$ville = ['id' => 4, 'nom' => 'Saint-Denis'];
echo $ville['nom'];   // affiche Saint-Denis
```

Dans ce projet, **une ligne de la base de données = une fiche**, et **plusieurs
lignes = une liste de fiches**. Par exemple `Ville::toutes()` renvoie :

```php
[
  ['id' => 1, 'nom' => 'Saint-Denis'],
  ['id' => 2, 'nom' => 'Saint-Pierre'],
]
```

Une fiche peut contenir une autre fiche : `$equipe['ville']['nom']` veut dire
« dans la fiche équipe, prends la case `ville`, qui est elle-même une fiche, et
dans celle-ci prends la case `nom` ».

### 2.3 Les fonctions : des recettes nommées

Une **fonction** est une suite d'instructions à laquelle on a donné un nom, pour
pouvoir la réutiliser. Elle peut recevoir des ingrédients (les **paramètres**)
et rendre un résultat (la **valeur de retour**).

```php
function doubler(int $nombre): int
{
    return $nombre * 2;
}

$resultat = doubler(5);   // $resultat contient 10
```

Lecture de la première ligne, appelée la **signature** :
- `function doubler` : la fonction s'appelle `doubler`.
- `(int $nombre)` : elle reçoit un paramètre, un entier, rangé dans la boîte `$nombre`.
- `: int` : elle renvoie un entier.
- `return` : « voici le résultat, on arrête ici ».

Notations que tu croiseras dans les signatures du projet :

| Notation | Signification |
|---|---|
| `: void` | La fonction ne renvoie rien (elle fait quelque chose, c'est tout). |
| `: array` | Elle renvoie un tableau. |
| `: ?array` | Elle renvoie un tableau **ou** `null` (rien trouvé). Le `?` signifie « ou null ». |
| `string $nom = 'joueur'` | Paramètre avec **valeur par défaut** : si on ne le fournit pas, il vaut `'joueur'`. |
| `?int $villeId = null` | Paramètre facultatif qui peut être un entier ou `null`. |

### 2.4 Les classes : des boîtes à outils

Une **classe** regroupe des fonctions qui vont ensemble. Quand une fonction est
dans une classe, on l'appelle une **méthode**. Dans ce projet, la classe `Ville`
regroupe tout ce qu'on peut faire avec les villes : les lister, en trouver
une, en créer une, en supprimer une.

```php
class Ville
{
    public static function toutes(): array { ... }
}

$liste = Ville::toutes();   // "appelle la méthode toutes() de la classe Ville"
```

Vocabulaire :

| Mot | Signification |
|---|---|
| `Ville::toutes()` | Les deux-points doubles `::` se lisent « de la classe ». |
| `static` | La méthode s'utilise directement sur la classe, sans rien « fabriquer » avant. Presque toutes les méthodes du projet sont `static`. |
| `public` | Utilisable depuis n'importe où. |
| `private` | Utilisable uniquement à l'intérieur de la classe (outil interne). |
| `protected` | Utilisable dans la classe et dans ses classes « enfants ». |
| `new Ville()` | Fabrique un **objet** (une instance) de la classe. Utilisé pour les contrôleurs. |
| `$this` | Dans une méthode non statique, désigne « l'objet en cours ». |
| `extends` | « Hérite de » : `class AdminController extends Controller` signifie que `AdminController` possède automatiquement tout ce que `Controller` sait faire. |
| `self::` | « Cette classe-ci », pour appeler une autre méthode de la même classe. |
| `Ville::class` | Simplement le texte `'Ville'` (le nom de la classe). Utilisé dans les routes. |

### 2.5 Conditions et boucles

```php
if ($score > 10) {
    echo 'Bravo';
} elseif ($score > 5) {
    echo 'Pas mal';
} else {
    echo 'Dommage';
}
```

`if` = « si », `elseif` = « sinon si », `else` = « sinon ». Une seule branche est exécutée.

```php
foreach ($villes as $ville) {
    echo $ville['nom'];
}
```

`foreach` = « pour chaque élément de la liste `$villes`, range-le dans `$ville`
et exécute le bloc ». C'est ainsi qu'on affiche une liste.

Les comparaisons :

| Écriture | Signification |
|---|---|
| `===` | « est exactement égal à » (même valeur **et** même type). Toujours préféré à `==`. |
| `!==` | « est différent de ». |
| `!` | « non » : `!$connecte` = « pas connecté ». |
| `&&` | « et » : les deux conditions doivent être vraies. |
| `\|\|` | « ou ». |

### 2.6 Petits opérateurs très utilisés dans le projet

| Écriture | Nom | Signification, avec exemple |
|---|---|---|
| `'a' . 'b'` | Concaténation | Colle deux textes : donne `'ab'`. `'eq.' . $id` donne `'eq.5'`. |
| `$x ?? 'défaut'` | Null coalescing | « `$x` s'il existe et n'est pas null, sinon `'défaut'` ». Évite une erreur quand une case n'existe pas. |
| `$x ?: 'défaut'` | Elvis | « `$x` s'il est "vrai" (non vide, non zéro), sinon `'défaut'` ». |
| `$a <=> $b` | Spaceship | Compare : renvoie `-1` si a < b, `0` si égaux, `1` si a > b. Sert au tri. |
| `(int) $x` | Conversion (cast) | Transforme en entier : `(int) '3'` donne `3`. |
| `$c ? 'oui' : 'non'` | Ternaire | « si `$c` alors `'oui'` sinon `'non'` », sur une ligne. |
| `[$a, $b] = explode('=', $ligne, 2)` | Déstructuration | Découpe `$ligne` au premier `=` et range les deux morceaux dans `$a` et `$b`. |

### 2.7 `require` : coller un autre fichier ici

`require 'fichier.php';` signifie « **insère le contenu de ce fichier à cet
endroit** ». Cela permet de découper le projet en petits fichiers.
`require_once` fait la même chose mais refuse de l'insérer deux fois.

`__DIR__` est une valeur automatique qui contient **le dossier du fichier en
cours**. `__DIR__ . '/config/config.php'` construit donc un chemin fiable, quel
que soit l'endroit d'où le site est lancé.

### 2.8 Les variables « superglobales »

PHP fournit des tableaux spéciaux, accessibles partout, qui contiennent ce que
le navigateur a envoyé :

| Variable | Contenu |
|---|---|
| `$_POST` | Les champs d'un formulaire envoyé en `POST`. `$_POST['email']` = ce que l'utilisateur a tapé dans le champ nommé `email`. |
| `$_SERVER` | Des informations sur la requête : `$_SERVER['REQUEST_URI']` = l'adresse demandée, `$_SERVER['REQUEST_METHOD']` = `GET` ou `POST`. |
| `$_SESSION` | La **session** (voir 2.9). |
| `$GLOBALS` | Toutes les variables « globales » du programme. `$GLOBALS['env']` = la variable `$env`. |

### 2.9 La session : se souvenir de qui est connecté

HTTP a la mémoire courte : chaque requête est indépendante, le serveur ne sait
pas que c'est la même personne qui revient. Pour « rester connecté », PHP
utilise une **session** :

1. À la première visite, le serveur crée un petit dossier de mémoire, avec un numéro unique.
2. Il envoie ce numéro au navigateur dans un **cookie** (un petit fichier que le navigateur renvoie à chaque requête).
3. À chaque requête suivante, le serveur retrouve le dossier grâce au numéro et y lit `$_SESSION`.

Dans ce projet, `$_SESSION['utilisateur']` contient la fiche de la personne
connectée (ou n'existe pas si personne n'est connecté).

### 2.10 Le mot de passe « haché »

On ne stocke **jamais** un mot de passe en clair. On stocke son **hash** : une
empreinte calculée par une fonction à sens unique. À partir de l'empreinte, il
est impossible de retrouver le mot de passe. Pour vérifier une connexion, on
recalcule l'empreinte du mot de passe tapé et on compare. PHP fournit
`password_hash()` (créer l'empreinte) et `password_verify()` (vérifier).

---

## 3. Ce que fait l'application et comment elle est organisée

### 3.1 Les pages et les droits

Il y a trois sortes de visiteurs :

| Visiteur | Description |
|---|---|
| **Anonyme** | N'est pas connecté. Peut tout consulter mais rien modifier. |
| **Joueur** | A créé un compte et s'est connecté. Voit son profil et ses équipes. |
| **Admin** | Compte avec le rôle `admin`. Accède au tableau de bord d'administration. |

| Adresse (URL) | Page | Qui y a accès |
|---|---|---|
| `/` | Accueil : prochaines épreuves | Tous |
| `/planning` | Toutes les épreuves | Tous |
| `/epreuves/5` | Détail de l'épreuve n°5 et équipes participantes | Tous |
| `/equipes` | Liste des équipes | Tous |
| `/equipes/3` | Détail de l'équipe n°3 et ses membres | Tous |
| `/classement` | Classement général des villes | Tous |
| `/login`, `/inscription`, `/deconnexion` | Connexion, création de compte, déconnexion | Tous |
| `/profil` | Mon profil et mes équipes | Connecté |
| `/admin` | Tableau de bord (villes, sports) | Admin |
| `/admin/villes/nouvelle`, `/admin/villes`, `/admin/villes/4/supprimer` | Créer / supprimer une ville | Admin |

### 3.2 L'organisation MVC : un restaurant bien rangé

Le code est organisé selon le modèle **MVC** (Modèle – Vue – Contrôleur). Pour
comprendre, reprenons le restaurant :

| Rôle | Au restaurant | Dans le projet | Dossier |
|---|---|---|---|
| **Contrôleur** | Le serveur en salle : prend la commande, va chercher en cuisine, apporte l'assiette | Reçoit la requête, demande les données au modèle, choisit la vue | `app/Controllers/` |
| **Modèle** | La cuisine et le garde-manger : sait où sont les ingrédients | Lit et écrit dans la base de données | `app/Models/` |
| **Vue** | La présentation dans l'assiette | Le gabarit HTML rempli avec les données | `app/Views/` |
| **Noyau** | Le bâtiment, la porte d'entrée, le tableau des tables | Les outils techniques communs : routeur, authentification, client base de données | `app/Core/` |

Avantage : chaque fichier a un seul rôle. Pour changer l'apparence d'une page,
on touche seulement la vue. Pour changer une règle métier, seulement le modèle.

### 3.3 L'arborescence complète

```
entrevilles-reu-main/
├── .github/workflows/deploy.yml     Mise en ligne automatique (section 12)
└── htdocs/                          Racine du site (le seul dossier visible par le serveur)
    ├── index.php                    LA porte d'entrée : toutes les requêtes passent ici
    ├── .htaccess                    Règle Apache : « tout envoyer vers index.php »
    ├── .env.example                 Modèle de fichier de configuration
    ├── assets/css/style.css         La mise en forme (couleurs, polices…)
    ├── config/
    │   ├── .htaccess                Interdit de lire ce dossier depuis le navigateur
    │   └── config.php               Charge les clés Supabase, démarre la session
    ├── database/
    │   ├── .htaccess                Interdit de lire ce dossier depuis le navigateur
    │   ├── creer_admin.php          Script pour créer le premier admin (obsolète, voir 13)
    │   ├── migration_mot_de_passe.sql  Ajoute la colonne mot de passe
    │   └── vue_classement_general.sql  Calcule le classement par ville
    └── app/
        ├── .htaccess                Interdit de lire ce dossier depuis le navigateur
        ├── routes.php               La liste « telle adresse → tel contrôleur »
        ├── Core/                    Le noyau technique
        │   ├── autoload.php         Charge les classes automatiquement
        │   ├── Router.php           Aiguille chaque adresse vers la bonne fonction
        │   ├── Controller.php       Classe mère des contrôleurs (sait afficher une vue)
        │   ├── Auth.php             Connexion, déconnexion, vérification des droits
        │   └── SupabaseClient.php   Parle à la base de données
        ├── Models/                  Un fichier par table de la base
        │   ├── Ville.php  Sport.php  Utilisateur.php  Equipe.php
        │   └── MembreEquipe.php  Epreuve.php  Participation.php
        ├── Controllers/             Un fichier par « zone » du site
        │   ├── AccueilController.php   AuthController.php   ProfilController.php
        │   ├── EpreuveController.php   EquipeController.php
        │   └── ClassementController.php  AdminController.php
        └── Views/                   Les gabarits HTML
            ├── partials/ layout.php  navbar.php  footer.php   (morceaux communs)
            ├── erreur_404.php
            ├── accueil/index.php   auth/login.php   auth/inscription.php
            ├── profil/index.php    epreuves/planning.php  epreuves/detail.php
            ├── equipes/liste.php   equipes/detail.php    classement/index.php
            └── admin/tableau_de_bord.php   admin/villes/nouvelle.php
```

### 3.4 Le voyage d'une requête, pas à pas

Prenons l'exemple concret : un visiteur clique sur l'équipe n°3, son navigateur
demande `GET /equipes/3`.

1. **Apache** (le logiciel serveur) cherche un fichier nommé `equipes/3`. Il n'existe pas. La règle du `.htaccess` dit alors : « envoie tout à `index.php` ».
2. **`index.php`** charge la configuration (`config.php`), le chargeur de classes (`autoload.php`) et la liste des routes (`routes.php`). Puis il appelle `Router::traiter()`.
3. **`Router::traiter()`** compare l'adresse `/equipes/3` avec chaque route. La route `/equipes/{id}` correspond : `{id}` vaut `"3"`. Le routeur fabrique un `EquipeController` et appelle sa méthode `detail("3")`.
4. **`EquipeController::detail()`** demande au modèle : `Equipe::trouver(3)` puis `MembreEquipe::parEquipe(3)`.
5. **Les modèles** demandent à `SupabaseClient::select(...)`, qui envoie une requête HTTP à Supabase et transforme la réponse JSON en tableaux PHP.
6. Le contrôleur reçoit les tableaux et appelle `$this->afficher('equipes/detail', [...])` en lui passant l'équipe et ses membres.
7. **`Controller::afficher()`** transforme les données en variables (`$equipe`, `$membres`) et charge le gabarit commun `layout.php`.
8. **`layout.php`** écrit l'entête HTML, la barre de navigation, puis insère la vue `equipes/detail.php`, puis le pied de page.
9. Le HTML complet est envoyé au navigateur, qui l'affiche.

Deuxième exemple : l'utilisateur remplit le formulaire de connexion et clique
sur « Se connecter » → `POST /login`.

1. Même début : `.htaccess` → `index.php` → `Router::traiter()`.
2. Le routeur cherche une route `POST` pour `/login` : c'est `AuthController::traiterLogin()`.
3. La méthode lit `$_POST['email']` et `$_POST['mot_de_passe']`.
4. Elle appelle `Utilisateur::verifierIdentifiants(...)`, qui cherche l'email dans la base et compare le mot de passe avec l'empreinte stockée.
5. Si c'est bon : `Auth::connecter()` range la fiche dans `$_SESSION`, puis on **redirige** vers `/` (réponse `302`). Le navigateur redemande `/` tout seul.
6. Si c'est faux : on ré-affiche le formulaire avec un message d'erreur.

---

# PARTIE B — LE CODE, FICHIER PAR FICHIER

## 4. La porte d'entrée et la configuration

### 4.1 `htdocs/index.php` — la porte d'entrée (Front Controller)

```php
<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/autoload.php';
require_once __DIR__ . '/app/routes.php';

Router::traiter();
```

| Ligne | Ce qu'elle fait |
|---|---|
| `<?php` | Annonce « ici commence du code PHP ». |
| `require_once .../config/config.php` | Charge la configuration : clés Supabase + démarrage de la session. |
| `require_once .../app/Core/autoload.php` | Active le chargement automatique des classes (voir 5.1). Sans lui, PHP ne saurait pas où trouver `Router`. |
| `require_once .../app/routes.php` | Déclare toutes les adresses du site. |
| `Router::traiter();` | Lance l'aiguillage : trouve la bonne fonction et l'exécute. |

L'ordre est important : `routes.php` utilise la classe `Router`, donc
l'autoloader doit être actif avant.

### 4.2 `htdocs/.htaccess` — la règle « tout passe par index.php »

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

| Ligne | Ce qu'elle fait |
|---|---|
| `RewriteEngine On` | Active la réécriture d'adresses. |
| `RewriteCond ... !-f` | Condition : « si l'adresse ne correspond **pas** à un fichier existant » (`-f` = fichier). |
| `RewriteCond ... !-d` | Condition : « et ne correspond pas à un dossier existant » (`-d` = dossier). |
| `RewriteRule ^ index.php [L]` | Alors, quelle que soit l'adresse (`^` = n'importe quoi), exécuter `index.php`. `[L]` = « dernière règle, arrête-toi là ». |

Résultat : `/assets/css/style.css` existe vraiment → servi tel quel.
`/equipes/3` n'existe pas → `index.php` s'en occupe.

Les trois autres fichiers `.htaccess` (dans `app/`, `config/`, `database/`)
contiennent une seule ligne, `Require all denied` : « personne ne peut lire
ces dossiers depuis un navigateur ». PHP, lui, peut toujours les `require` en
interne. Cela protège la clé secrète de la base de données.

### 4.3 `htdocs/config/config.php` — configuration et session

```php
$envLocalPhp = __DIR__ . '/env.local.php';
$envPath = __DIR__ . '/../.env';

if (file_exists($envLocalPhp)) {
    require $envLocalPhp;
} elseif (file_exists($envPath)) {
    $lignes = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        if (str_starts_with(trim($ligne), '#')) {
            continue;
        }
        if (str_contains($ligne, '=')) {
            [$cle, $valeur] = explode('=', $ligne, 2);
            putenv(trim($cle) . '=' . trim($valeur));
        }
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', '1');
```

**À quoi sert ce fichier :** trouver l'adresse et la clé secrète de Supabase,
puis démarrer la session.

**Les variables :**

| Variable | Contenu | Rôle |
|---|---|---|
| `$envLocalPhp` | Le chemin `config/env.local.php` | Fichier de configuration **en PHP**, à créer sur le serveur (il n'est pas dans le projet, car il contient un secret). C'est la méthode recommandée en ligne. |
| `$envPath` | Le chemin `htdocs/.env` | Fichier de configuration **texte** (`CLE=VALEUR`), pour tester en local. |
| `$lignes` | Liste des lignes du fichier `.env` | `file()` lit un fichier ligne par ligne. Les deux options enlèvent les retours à la ligne et sautent les lignes vides. |
| `$ligne` | La ligne en cours dans la boucle | |
| `$cle`, `$valeur` | Les deux moitiés de la ligne, autour du `=` | `explode('=', $ligne, 2)` coupe au **premier** `=` seulement (le `2` = « deux morceaux maximum »), pour qu'une valeur puisse contenir un `=`. |

**Comment ça marche :**

1. Si `env.local.php` existe → on l'insère avec `require`. Ce fichier doit définir une variable `$env` comme ceci :
   ```php
   <?php
   $env = [
       'SUPABASE_URL' => 'https://xxxx.supabase.co',
       'SUPABASE_KEY' => 'la_clé_secrète',
   ];
   ```
   Comme `config.php` est chargé au tout début du programme, `$env` devient une variable **globale**, que `SupabaseClient` relit ensuite avec `$GLOBALS['env']`.
2. Sinon, si `.env` existe → on lit chaque ligne. `trim()` enlève les espaces au début et à la fin. Une ligne qui commence par `#` est un commentaire : `continue` = « passe à la ligne suivante ». Une ligne contenant `=` est découpée et enregistrée avec `putenv()` (variable d'environnement du système). **Attention** : voir le point 1 de la section 13, ce chemin ne fonctionne pas tel quel.
3. `session_status() === PHP_SESSION_NONE` = « si aucune session n'est encore ouverte » → `session_start()` l'ouvre. Sans cela, `$_SESSION` serait vide.
4. `error_reporting(E_ALL)` + `display_errors = 1` : affiche **toutes** les erreurs à l'écran. Pratique pour développer, à désactiver sur un site public.

### 4.4 `htdocs/.env.example` — le modèle de configuration

```
SUPABASE_URL=https://xxxxxxxxxxxx.supabase.co
SUPABASE_KEY=ta_service_role_key
```

À copier en `.env` et compléter. `SUPABASE_URL` est l'adresse du projet
Supabase. `SUPABASE_KEY` est la clé **service_role** : une clé « maître » qui
donne tous les droits sur la base. Elle ne doit **jamais** être visible dans le
navigateur ni publiée sur GitHub (c'est pourquoi `.env` n'est pas dans le dépôt,
seulement `.env.example`).

---

## 5. Le noyau — `app/Core`

Le noyau contient les outils techniques utilisés par tout le reste. Ces quatre
classes ne connaissent rien aux villes ou aux équipes : elles pourraient servir
telles quelles dans un autre projet.

### 5.1 `autoload.php` — trouver automatiquement le fichier d'une classe

```php
spl_autoload_register(function (string $classe) {
    $dossiers = ['Core', 'Models', 'Controllers'];

    foreach ($dossiers as $dossier) {
        $chemin = __DIR__ . '/../' . $dossier . '/' . $classe . '.php';
        if (file_exists($chemin)) {
            require $chemin;
            return;
        }
    }
});
```

**Le problème résolu :** quand le code écrit `Ville::toutes()`, PHP doit avoir
lu le fichier `Ville.php` avant. Sans autoloader, il faudrait un `require` par
classe au début de chaque fichier.

**Comment ça marche :**

- `spl_autoload_register(...)` dit à PHP : « quand tu rencontres une classe que tu ne connais pas, appelle cette fonction en lui donnant le nom de la classe ».
- La fonction n'a pas de nom (fonction **anonyme**). Elle reçoit `$classe`, par exemple `'Ville'`.
- `$dossiers` : les trois dossiers où chercher, dans cet ordre.
- Pour chaque dossier, `$chemin` devient par exemple `app/Models/Ville.php`.
- `file_exists($chemin)` : « ce fichier existe-t-il ? ». Si oui, `require` le charge et `return` arrête la recherche.

**Règle à retenir :** une classe = un fichier portant exactement le même nom
(`Ville` → `Ville.php`). C'est cette convention qui rend l'autoloader possible.

### 5.2 `Router.php` — l'aiguilleur

```php
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
```

#### La variable `$routes`

`private static array $routes = [];` : un tableau, vide au départ, qui contiendra
la liste de toutes les routes. `static` = il appartient à la classe (une seule
liste pour tout le programme). `private` = seul `Router` peut y toucher.

Chaque route est une fiche à quatre cases : `methode` (`GET`/`POST`), `chemin`
(`/equipes/{id}`), `controleur` (`'EquipeController'`), `action` (`'detail'`).

#### `ajouter(string $methode, string $chemin, string $controleur, string $action): void`

- **À quoi ça sert :** enregistrer une route.
- **Ce qu'elle reçoit :** la méthode HTTP, le chemin, le nom de la classe contrôleur, le nom de la méthode à appeler.
- **Ce qu'elle renvoie :** rien.
- **Comment ça marche :** `self::$routes[] = [...]` ajoute une nouvelle fiche à la fin de la liste (les crochets vides `[]` signifient « à la fin »).

#### `get(...)` et `post(...)`

- **À quoi ça sert :** raccourcis plus lisibles. `Router::get('/planning', ...)` équivaut à `Router::ajouter('GET', '/planning', ...)`.

#### `traiter(): void`

- **À quoi ça sert :** c'est le cœur du routeur. Trouver la route qui correspond à l'adresse demandée et exécuter la bonne méthode du bon contrôleur. Sinon, afficher la page 404.
- **Ce qu'elle reçoit :** rien en paramètre, mais elle lit `$_SERVER`.
- **Comment ça marche, ligne par ligne :**

| Ligne | Explication |
|---|---|
| `$methode = $_SERVER['REQUEST_METHOD'];` | `GET` ou `POST`. |
| `$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);` | L'adresse demandée, sans la partie après `?` (`/equipes/3?x=1` → `/equipes/3`). |
| `$uri = rtrim($uri, '/') ?: '/';` | `rtrim` enlève un éventuel `/` final (`/equipes/` → `/equipes`). Pour la page d'accueil `/`, il ne reste rien : l'opérateur `?:` remet alors `'/'`. |
| `foreach (self::$routes as $route)` | On examine chaque route l'une après l'autre. |
| `if ($route['methode'] !== $methode) continue;` | Mauvaise méthode (par exemple route `POST` mais requête `GET`) → on passe à la suivante. |
| `$motif = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $route['chemin']);` | Transforme le chemin en **motif de recherche** (expression régulière). Chaque `{quelquechose}` devient `([^/]+)`, qui signifie « une suite de caractères sans `/`, à capturer ». `/equipes/{id}` devient `/equipes/([^/]+)`. |
| `$motif = '#^' . $motif . '$#';` | Ajoute `^` (début) et `$` (fin) pour exiger une correspondance **exacte** de toute l'adresse. Les `#` délimitent le motif. |
| `if (preg_match($motif, $uri, $correspondances))` | Teste si l'adresse correspond au motif. Si oui, `$correspondances` reçoit la liste des morceaux capturés, avec en case 0 l'adresse entière. |
| `array_shift($correspondances);` | Retire la case 0 (l'adresse entière) : il ne reste que les paramètres, par exemple `["3"]`. |
| `$controleur = new $route['controleur']();` | Fabrique un objet à partir du **nom** de la classe stocké en texte (`new EquipeController()`). Cela déclenche l'autoloader et le **constructeur** de la classe s'il existe (voir `AdminController`). |
| `call_user_func_array([$controleur, $route['action']], $correspondances);` | Appelle la méthode dont le nom est dans `$route['action']`, en lui passant les paramètres capturés. Équivaut à `$controleur->detail("3")`. |
| `return;` | Route trouvée et exécutée : on s'arrête. |
| `http_response_code(404); require .../erreur_404.php;` | Aucune route ne correspond : code « introuvable » et affichage de la page d'erreur. |

**Détail à retenir :** les paramètres capturés dans l'adresse sont toujours du
**texte** (`"3"` et non `3`). C'est pourquoi les méthodes des contrôleurs les
reçoivent en `string $id` et les convertissent avec `(int) $id`.

### 5.3 `Controller.php` — la classe mère des contrôleurs

```php
class Controller
{
    protected function afficher(string $vue, array $donnees = []): void
    {
        extract($donnees); // transforme ['titre' => 'X'] en variable $titre

        $cheminVue = __DIR__ . '/../Views/' . $vue . '.php';

        // Le layout inclut la navbar/footer et charge le contenu de la vue
        require __DIR__ . '/../Views/partials/layout.php';
    }
}
```

Tous les contrôleurs héritent de cette classe (`extends Controller`) et
disposent donc de `afficher()`.

#### `afficher(string $vue, array $donnees = []): void`

- **À quoi ça sert :** afficher une page complète (barre de navigation + contenu + pied de page) en y injectant des données.
- **Ce qu'elle reçoit :** `$vue` = le nom de la vue sans extension (`'equipes/liste'`) ; `$donnees` = une fiche de données (`['titre' => 'Équipes', 'equipes' => [...]]`), vide par défaut.
- **Ce qu'elle renvoie :** rien, elle écrit directement le HTML.
- **Comment ça marche :**
  1. `extract($donnees)` : pour chaque case de la fiche, crée une variable du même nom. `['titre' => 'Équipes']` donne `$titre = 'Équipes'`. C'est le pont entre le contrôleur et la vue.
  2. `$cheminVue` : le chemin complet du fichier de la vue, par exemple `app/Views/equipes/liste.php`.
  3. `require layout.php` : insère le gabarit commun. Comme ce `require` est **à l'intérieur** de la méthode, le gabarit « voit » `$titre`, `$cheminVue` et toutes les variables extraites. Le gabarit fera lui-même `require $cheminVue` pour insérer la vue, qui verra aussi ces variables.

`protected` : seule la classe et ses enfants (les contrôleurs) peuvent
l'appeler ; on n'appelle jamais `afficher()` depuis l'extérieur.

### 5.4 `Auth.php` — connexion et droits d'accès

```php
class Auth
{
    public static function connecter(array $utilisateur): void
    {
        // On ne stocke JAMAIS le mot de passe (même hashé) en session
        $_SESSION['utilisateur'] = [
            'id'         => $utilisateur['id'],
            'nom_compte' => $utilisateur['nom_compte'],
            'email'      => $utilisateur['email'],
            'role'       => $utilisateur['role'],
            'ville_id'   => $utilisateur['ville_id'],
        ];
    }

    public static function deconnecter(): void
    {
        unset($_SESSION['utilisateur']);
        session_destroy();
    }

    public static function estConnecte(): bool
    {
        return isset($_SESSION['utilisateur']);
    }

    public static function estAdmin(): bool
    {
        return self::estConnecte() && $_SESSION['utilisateur']['role'] === 'admin';
    }

    public static function utilisateur(): ?array
    {
        return $_SESSION['utilisateur'] ?? null;
    }

    public static function exigerConnexion(): void
    {
        if (!self::estConnecte()) {
            header('Location: /login');
            exit;
        }
    }

    public static function exigerAdmin(): void
    {
        self::exigerConnexion();
        if (!self::estAdmin()) {
            http_response_code(403);
            die('Accès refusé : réservé aux administrateurs.');
        }
    }
}
```

Toute la « mémoire » de la connexion tient dans `$_SESSION['utilisateur']`.

#### `connecter(array $utilisateur): void`

- **À quoi ça sert :** marquer la personne comme connectée.
- **Ce qu'elle reçoit :** la fiche complète de l'utilisateur, telle que lue en base (avec son mot de passe haché).
- **Comment ça marche :** recopie en session **seulement** cinq informations : `id`, `nom_compte`, `email`, `role`, `ville_id`. Le mot de passe (même haché) n'est jamais mis en session, par principe de sécurité.

#### `deconnecter(): void`

- **Comment ça marche :** `unset(...)` supprime la case `utilisateur` de la session, puis `session_destroy()` efface complètement la session côté serveur.

#### `estConnecte(): bool`

- **Ce qu'elle renvoie :** `true` si la case `utilisateur` existe en session, `false` sinon. `isset()` veut dire « existe et n'est pas null ».

#### `estAdmin(): bool`

- **Ce qu'elle renvoie :** `true` si connecté **et** si le rôle est exactement `'admin'`.
- **Subtilité :** avec `&&`, PHP évalue la seconde condition seulement si la première est vraie. Si personne n'est connecté, on ne tente pas de lire `$_SESSION['utilisateur']['role']` (ce qui provoquerait une erreur).

#### `utilisateur(): ?array`

- **Ce qu'elle renvoie :** la fiche en session, ou `null` si personne n'est connecté (grâce à `??`).

#### `exigerConnexion(): void`

- **À quoi ça sert :** un « videur » à placer au début d'une page réservée aux connectés.
- **Comment ça marche :** si non connecté, `header('Location: /login')` envoie au navigateur l'ordre d'aller sur `/login` (redirection), puis `exit` **arrête le programme**. Sans `exit`, PHP continuerait à produire la page protégée après l'entête de redirection.

#### `exigerAdmin(): void`

- **À quoi ça sert :** videur pour les pages d'administration.
- **Comment ça marche :** exige d'abord d'être connecté, puis, si le rôle n'est pas admin, envoie le code `403` (interdit) et `die()` (affiche le message et arrête tout).

### 5.5 `SupabaseClient.php` — parler à la base de données

C'est la classe la plus technique. Elle envoie des requêtes HTTP à Supabase et
traduit les réponses JSON en tableaux PHP. Elle remplace une ancienne connexion
directe (PDO), impossible sur l'hébergeur InfinityFree.

```php
class SupabaseClient
{
    private static ?string $url = null;
    private static ?string $key = null;

    private static function init(): void
    {
        if (self::$url === null) {
            self::$url = rtrim((string) ($GLOBALS['env']['SUPABASE_URL'] ?? ''), '/');
            self::$key = (string) ($GLOBALS['env']['SUPABASE_KEY'] ?? '');

            if (self::$url === '' || self::$key === '') {
                die('Erreur de connexion à la base de données : variables SUPABASE_URL / SUPABASE_KEY manquantes.');
            }
        }
    }
```

#### Les variables `$url` et `$key`

Deux boîtes de la classe (`static`), vides au départ (`null`). Elles
contiendront l'adresse Supabase et la clé secrète après le premier appel à
`init()`. Le `?string` signifie « un texte ou null ».

#### `init(): void` (privée)

- **À quoi ça sert :** lire la configuration **une seule fois**, au premier besoin (on parle de chargement « paresseux »).
- **Comment ça marche :** si `$url` est encore `null`, on lit `$GLOBALS['env']['SUPABASE_URL']` et `['SUPABASE_KEY']` (définis par `env.local.php`). `?? ''` donne une chaîne vide si absent ; `(string)` force le type texte ; `rtrim(..., '/')` enlève un `/` final éventuel. Si l'une des deux valeurs est vide, le programme s'arrête avec un message clair.

```php
    private static function requete(string $method, string $path, ?array $body = null, string $prefer = ''): array
    {
        self::init();

        $ch = curl_init(self::$url . '/rest/v1/' . $path);

        $entetes = [
            'apikey: ' . self::$key,
            'Authorization: Bearer ' . self::$key,
            'Content-Type: application/json',
        ];
        if ($prefer !== '') {
            $entetes[] = 'Prefer: ' . $prefer;
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $entetes,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $reponse = curl_exec($ch);

        if ($reponse === false) {
            $erreur = curl_error($ch);
            curl_close($ch);
            die('Erreur de connexion à la base de données : ' . $erreur);
        }

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 400) {
            die('Erreur base de données (HTTP ' . $code . ') : ' . $reponse);
        }

        if ($reponse === '' || $reponse === null) {
            return [];
        }

        $donnees = json_decode($reponse, true);
        return is_array($donnees) ? $donnees : [];
    }
```

#### `requete(string $method, string $path, ?array $body = null, string $prefer = ''): array` (privée)

- **À quoi ça sert :** envoyer **une** requête HTTP à Supabase et renvoyer la réponse sous forme de tableau. Toutes les autres méthodes passent par elle.
- **Ce qu'elle reçoit :**
  - `$method` : `GET` (lire), `POST` (créer), `PATCH` (modifier), `DELETE` (supprimer).
  - `$path` : la fin de l'adresse, par exemple `ville?select=*&order=nom.asc`.
  - `$body` : les données à envoyer (pour `POST`/`PATCH`), ou `null`.
  - `$prefer` : une option spéciale de Supabase, par exemple `return=representation` (« renvoie-moi la ligne créée »).
- **Ce qu'elle renvoie :** un tableau PHP (liste de fiches), vide si la réponse est vide.
- **Comment ça marche :**

| Ligne | Explication |
|---|---|
| `self::init();` | S'assure que l'adresse et la clé sont chargées. |
| `$ch = curl_init(...)` | **cURL** est l'outil de PHP pour faire des requêtes HTTP. `$ch` est le « dossier » de la requête en préparation. L'adresse complète est `https://xxx.supabase.co/rest/v1/` + `$path`. |
| `$entetes = [...]` | Les **en-têtes** HTTP : la carte d'identité de la requête. `apikey` et `Authorization: Bearer` transmettent tous deux la clé (Supabase exige les deux). `Content-Type: application/json` annonce que les données envoyées sont en JSON. |
| `if ($prefer !== '') $entetes[] = ...` | Ajoute l'en-tête `Prefer` seulement si demandé. |
| `curl_setopt_array($ch, [...])` | Règle plusieurs options d'un coup : la méthode HTTP, les en-têtes, `RETURNTRANSFER => true` (« rends-moi la réponse au lieu de l'afficher »), `TIMEOUT => 15` (abandonner après 15 secondes). |
| `if ($body !== null) ... json_encode($body)` | S'il y a des données à envoyer, on les convertit en texte JSON et on les met dans le corps de la requête. |
| `$reponse = curl_exec($ch);` | **Envoie** la requête et attend la réponse. |
| `if ($reponse === false) { ... die(...) }` | Échec réseau (pas d'internet, adresse fausse…) : on affiche l'erreur et on arrête. |
| `$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);` | Récupère le code de réponse (200, 404, 401…). |
| `curl_close($ch);` | Ferme le dossier. |
| `if ($code >= 400) die(...)` | Les codes 400 et plus sont des erreurs (clé invalide, table inexistante…) : on affiche le message de Supabase et on arrête. |
| `if ($reponse === '' ...) return [];` | Réponse vide (par exemple après un `DELETE`) → tableau vide. |
| `$donnees = json_decode($reponse, true);` | Transforme le texte JSON en tableau PHP. Le `true` demande des tableaux (fiches) plutôt que des objets. |
| `return is_array($donnees) ? $donnees : [];` | Sécurité : si le décodage a échoué, on renvoie un tableau vide. |

```php
    public static function select(string $table, string $select = '*', array $filtres = [], string $ordre = ''): array
    {
        $params = ['select' => $select];
        foreach ($filtres as $colonne => $valeur) {
            $params[$colonne] = $valeur;
        }
        if ($ordre !== '') {
            $params['order'] = $ordre;
        }

        return self::requete('GET', $table . '?' . http_build_query($params));
    }
```

#### `select(string $table, string $select = '*', array $filtres = [], string $ordre = ''): array`

- **À quoi ça sert :** **lire** des lignes d'une table.
- **Ce qu'elle reçoit :**
  - `$table` : le nom de la table (`'ville'`).
  - `$select` : les colonnes voulues. `'*'` = toutes. Peut contenir des « embeds » (voir encadré ci-dessous).
  - `$filtres` : une fiche « colonne → condition », par exemple `['id' => 'eq.5']` (« id égal à 5 »).
  - `$ordre` : le tri, par exemple `'nom.asc'` (croissant) ou `'date_heure.desc'` (décroissant).
- **Ce qu'elle renvoie :** la liste des lignes trouvées.
- **Comment ça marche :** construit une fiche `$params` avec `select`, chaque filtre, et `order` si fourni. `http_build_query($params)` la transforme en texte d'adresse (`select=*&id=eq.5&order=nom.asc`) en encodant les caractères spéciaux. Puis appelle `requete('GET', 'ville?select=...')`.

> **Encadré : le langage de Supabase (PostgREST)**
>
> Supabase expose chaque table sous forme d'adresse et comprend un petit langage dans l'URL :
>
> | Écriture | Signification |
> |---|---|
> | `select=*` | Toutes les colonnes. |
> | `select=*,ville:ville_id(nom)` | Toutes les colonnes, **plus** : suis la clé étrangère `ville_id`, va chercher la ville correspondante et renvoie son `nom` dans une sous-fiche appelée `ville`. C'est un **embed** (une jointure). Résultat : `{"id": 1, "nom": "Les Dodos", "ville": {"nom": "Saint-Denis"}}`. |
> | `id=eq.5` | Filtre : `id` **égal** (`eq`) à 5. |
> | `statut=eq.a_venir` | Filtre : `statut` égal à `a_venir`. |
> | `order=nom.asc` | Tri croissant sur `nom`. `desc` = décroissant. |
> | `order=classement.asc.nullslast` | Tri croissant, en mettant les valeurs vides (`NULL`) à la fin. |
> | En-tête `Prefer: return=representation` | Après une création, renvoie la ligne créée (avec son nouvel `id`). |

```php
    public static function insert(string $table, array $donnees): array
    {
        $resultat = self::requete('POST', $table, $donnees, 'return=representation');
        return $resultat[0] ?? [];
    }
```

#### `insert(string $table, array $donnees): array`

- **À quoi ça sert :** **créer** une ligne.
- **Ce qu'elle reçoit :** la table et une fiche « colonne → valeur » (`['nom' => 'Saint-Paul']`).
- **Ce qu'elle renvoie :** la ligne créée, avec son `id` attribué par la base. Supabase renvoie une liste d'une seule ligne, d'où `$resultat[0]` (« la première »). `?? []` = fiche vide si rien.

```php
    public static function update(string $table, array $filtres, array $donnees): void
    {
        $params = [];
        foreach ($filtres as $colonne => $valeur) {
            $params[$colonne] = $valeur;
        }
        self::requete('PATCH', $table . '?' . http_build_query($params), $donnees);
    }
```

#### `update(string $table, array $filtres, array $donnees): void`

- **À quoi ça sert :** **modifier** les lignes qui correspondent aux filtres.
- **Ce qu'elle reçoit :** la table, les filtres (quelles lignes), les nouvelles valeurs (quoi changer).
- **Comment ça marche :** même construction d'adresse que `select`, mais avec la méthode `PATCH` et les nouvelles valeurs dans le corps. **Important :** sans filtre, toutes les lignes seraient modifiées ; le projet fournit toujours un filtre.

```php
    public static function delete(string $table, array $filtres): void
    {
        $params = [];
        foreach ($filtres as $colonne => $valeur) {
            $params[$colonne] = $valeur;
        }
        self::requete('DELETE', $table . '?' . http_build_query($params));
    }
```

#### `delete(string $table, array $filtres): void`

- **À quoi ça sert :** **supprimer** les lignes qui correspondent aux filtres.

```php
    public static function rpc(string $fonction, array $params = []): array
    {
        return self::requete('POST', 'rpc/' . $fonction, $params);
    }
}
```

#### `rpc(string $fonction, array $params = []): array`

- **À quoi ça sert :** appeler une fonction écrite directement dans la base de données (procédure stockée). Prévu pour l'avenir, **pas utilisé** actuellement.

---

## 6. Les routes — `app/routes.php`

Ce fichier est un simple **annuaire** : « telle adresse, avec telle méthode →
telle classe, telle fonction ».

```php
Router::get('/', AccueilController::class, 'index');

Router::get('/login', AuthController::class, 'afficherLogin');
Router::post('/login', AuthController::class, 'traiterLogin');
Router::get('/inscription', AuthController::class, 'afficherInscription');
Router::post('/inscription', AuthController::class, 'traiterInscription');
Router::get('/deconnexion', AuthController::class, 'deconnexion');

Router::get('/profil', ProfilController::class, 'index');

Router::get('/planning', EpreuveController::class, 'planning');
Router::get('/epreuves/{id}', EpreuveController::class, 'detail');

Router::get('/equipes', EquipeController::class, 'liste');
Router::get('/equipes/{id}', EquipeController::class, 'detail');

Router::get('/classement', ClassementController::class, 'index');

Router::get('/admin', AdminController::class, 'tableauDeBord');
Router::get('/admin/villes/nouvelle', AdminController::class, 'nouvelleVille');
Router::post('/admin/villes', AdminController::class, 'creerVille');
Router::post('/admin/villes/{id}/supprimer', AdminController::class, 'supprimerVille');
```

Lecture d'une ligne : `Router::get('/equipes/{id}', EquipeController::class, 'detail');`
= « quand quelqu'un demande en `GET` une adresse de la forme `/equipes/quelque-chose`,
appelle la méthode `detail` de la classe `EquipeController` en lui donnant ce
quelque-chose ».

| Méthode | Adresse | Classe | Méthode appelée | Accès |
|---|---|---|---|---|
| GET | `/` | `AccueilController` | `index` | Tous |
| GET | `/login` | `AuthController` | `afficherLogin` | Tous |
| POST | `/login` | `AuthController` | `traiterLogin` | Tous |
| GET | `/inscription` | `AuthController` | `afficherInscription` | Tous |
| POST | `/inscription` | `AuthController` | `traiterInscription` | Tous |
| GET | `/deconnexion` | `AuthController` | `deconnexion` | Tous |
| GET | `/profil` | `ProfilController` | `index` | Connecté |
| GET | `/planning` | `EpreuveController` | `planning` | Tous |
| GET | `/epreuves/{id}` | `EpreuveController` | `detail` | Tous |
| GET | `/equipes` | `EquipeController` | `liste` | Tous |
| GET | `/equipes/{id}` | `EquipeController` | `detail` | Tous |
| GET | `/classement` | `ClassementController` | `index` | Tous |
| GET | `/admin` | `AdminController` | `tableauDeBord` | Admin |
| GET | `/admin/villes/nouvelle` | `AdminController` | `nouvelleVille` | Admin |
| POST | `/admin/villes` | `AdminController` | `creerVille` | Admin |
| POST | `/admin/villes/{id}/supprimer` | `AdminController` | `supprimerVille` | Admin |

**Pourquoi deux routes `/login` ?** La version `GET` **affiche** le formulaire ;
la version `POST` **traite** ce que l'utilisateur a saisi. C'est un schéma
classique : afficher en GET, traiter en POST, puis rediriger (voir « PRG » dans
le glossaire).

---

## 7. Les modèles — `app/Models`

Un modèle = une table de la base. Chaque modèle sait lire, créer, modifier ou
supprimer dans sa table. Il ne s'occupe **jamais** d'affichage.

**Conventions communes à tous les modèles :**

- Toutes les méthodes sont `public static` : on écrit `Ville::toutes()` directement.
- Une méthode renvoie soit **une fiche** (`array`), soit **une liste de fiches** (`array`), soit `null` (rien trouvé).
- `trouver(int $id): ?array` : cherche par identifiant ; renvoie `null` si absent.
- `creer(...): int` : crée une ligne et renvoie son nouvel `id`.
- Les filtres sont écrits en langage Supabase : `'eq.' . $id` donne `'eq.5'`, c'est-à-dire « égal à 5 ».
- Le motif `$resultats[0] ?? null` signifie : « la première ligne trouvée, ou null s'il n'y en a pas ».
- Certains modèles ont une méthode `aplatir()` : elle transforme les sous-fiches renvoyées par les embeds (`['ville' => ['nom' => 'X']]`) en simples colonnes (`['ville_nom' => 'X']`) pour que les vues soient plus simples à écrire.

### 7.1 `Ville.php` — table `ville` (colonnes : `id`, `nom`)

```php
class Ville
{
    public static function toutes(): array
    {
        return SupabaseClient::select('ville', '*', [], 'nom.asc');
    }

    public static function trouver(int $id): ?array
    {
        $resultats = SupabaseClient::select('ville', '*', ['id' => 'eq.' . $id]);
        return $resultats[0] ?? null;
    }

    public static function creer(string $nom): int
    {
        $ligne = SupabaseClient::insert('ville', ['nom' => $nom]);
        return (int) $ligne['id'];
    }

    public static function modifier(int $id, string $nom): void
    {
        SupabaseClient::update('ville', ['id' => 'eq.' . $id], ['nom' => $nom]);
    }

    public static function supprimer(int $id): void
    {
        SupabaseClient::delete('ville', ['id' => 'eq.' . $id]);
    }
}
```

| Méthode | À quoi ça sert | Reçoit | Renvoie | Comment |
|---|---|---|---|---|
| `toutes()` | Lister toutes les villes | rien | liste de fiches `['id', 'nom']` | Lit la table `ville`, toutes colonnes, sans filtre, triée par nom croissant. |
| `trouver($id)` | Trouver une ville précise | l'identifiant | la fiche ou `null` | Filtre `id = $id` puis prend la première ligne. |
| `creer($nom)` | Ajouter une ville | le nom | le nouvel `id` (entier) | Insère `['nom' => $nom]`. Supabase renvoie la ligne créée ; on en extrait l'`id` et on le convertit en entier avec `(int)`. |
| `modifier($id, $nom)` | Renommer | l'identifiant, le nouveau nom | rien | Modifie la ligne `id = $id`. (Prévue mais pas encore utilisée par une page.) |
| `supprimer($id)` | Supprimer | l'identifiant | rien | Supprime la ligne `id = $id`. |

### 7.2 `Sport.php` — table `sport` (colonnes : `id`, `nom`, `description`)

```php
class Sport
{
    public static function tous(): array
    {
        return SupabaseClient::select('sport', '*', [], 'nom.asc');
    }

    public static function trouver(int $id): ?array
    {
        $resultats = SupabaseClient::select('sport', '*', ['id' => 'eq.' . $id]);
        return $resultats[0] ?? null;
    }

    public static function creer(string $nom, ?string $description): int
    {
        $ligne = SupabaseClient::insert('sport', ['nom' => $nom, 'description' => $description]);
        return (int) $ligne['id'];
    }

    public static function supprimer(int $id): void
    {
        SupabaseClient::delete('sport', ['id' => 'eq.' . $id]);
    }
}
```

| Méthode | À quoi ça sert | Reçoit | Renvoie | Comment |
|---|---|---|---|---|
| `tous()` | Lister les sports | rien | liste de fiches | Comme `Ville::toutes()`, sur la table `sport`. |
| `trouver($id)` | Trouver un sport | l'identifiant | fiche ou `null` | Idem `Ville::trouver`. |
| `creer($nom, $description)` | Ajouter un sport | nom, description (peut être `null`) | nouvel `id` | Insère les deux colonnes. |
| `supprimer($id)` | Supprimer | l'identifiant | rien | |

### 7.3 `Utilisateur.php` — table `utilisateur` (colonnes : `id`, `email`, `nom_compte`, `role`, `ville_id`, `mot_de_passe`)

```php
class Utilisateur
{
    public static function trouverParEmail(string $email): ?array
    {
        $resultats = SupabaseClient::select('utilisateur', '*', ['email' => 'eq.' . $email]);
        return $resultats[0] ?? null;
    }

    public static function trouver(int $id): ?array
    {
        $resultats = SupabaseClient::select('utilisateur', '*', ['id' => 'eq.' . $id]);
        return $resultats[0] ?? null;
    }

    public static function creer(string $nomCompte, string $email, string $motDePasseClair, string $role = 'joueur', ?int $villeId = null): int
    {
        $hash = password_hash($motDePasseClair, PASSWORD_DEFAULT);
        $ligne = SupabaseClient::insert('utilisateur', [
            'email' => $email,
            'nom_compte' => $nomCompte,
            'role' => $role,
            'ville_id' => $villeId,
            'mot_de_passe' => $hash,
        ]);
        return (int) $ligne['id'];
    }

    public static function verifierIdentifiants(string $email, string $motDePasseClair): ?array
    {
        $utilisateur = self::trouverParEmail($email);
        if ($utilisateur && $utilisateur['mot_de_passe'] && password_verify($motDePasseClair, $utilisateur['mot_de_passe'])) {
            return $utilisateur;
        }
        return null;
    }
}
```

#### `trouverParEmail(string $email): ?array`

- **À quoi ça sert :** retrouver un compte à partir de son email (unique dans la base).
- **Renvoie :** la fiche complète (y compris le mot de passe haché) ou `null`.

#### `trouver(int $id): ?array`

- Même chose par identifiant.

#### `creer(string $nomCompte, string $email, string $motDePasseClair, string $role = 'joueur', ?int $villeId = null): int`

- **À quoi ça sert :** créer un compte.
- **Ce qu'elle reçoit :** le pseudo, l'email, le mot de passe **en clair** (tel que tapé), le rôle (par défaut `'joueur'`), la ville (facultative, `null` par défaut).
- **Ce qu'elle renvoie :** le nouvel `id`.
- **Comment ça marche :**
  1. `$hash = password_hash($motDePasseClair, PASSWORD_DEFAULT)` : calcule l'empreinte du mot de passe avec l'algorithme recommandé par PHP (bcrypt). Le résultat est un texte d'environ 60 caractères, différent à chaque appel (un « sel » aléatoire est mélangé), mais toujours vérifiable.
  2. Insère la fiche avec l'empreinte **à la place** du mot de passe.

#### `verifierIdentifiants(string $email, string $motDePasseClair): ?array`

- **À quoi ça sert :** vérifier une tentative de connexion.
- **Ce qu'elle renvoie :** la fiche de l'utilisateur si l'email existe **et** que le mot de passe est bon ; sinon `null`.
- **Comment ça marche :** la condition a trois parties reliées par `&&` :
  1. `$utilisateur` : un compte a été trouvé ;
  2. `$utilisateur['mot_de_passe']` : ce compte a bien un mot de passe enregistré (les comptes créés avant la migration n'en avaient pas) ;
  3. `password_verify(...)` : l'empreinte du mot de passe tapé correspond à celle stockée.
  Si l'une échoue, on renvoie `null` sans préciser laquelle (on ne doit pas révéler si un email existe).

### 7.4 `Equipe.php` — table `equipe` (colonnes : `id`, `nom`, `ville_id`, `sport_id`)

```php
class Equipe
{
    private static function aplatir(array $ligne): array
    {
        $ligne['ville_nom'] = $ligne['ville']['nom'] ?? null;
        $ligne['sport_nom'] = $ligne['sport']['nom'] ?? null;
        unset($ligne['ville'], $ligne['sport']);
        return $ligne;
    }

    public static function toutes(): array
    {
        $lignes = SupabaseClient::select(
            'equipe',
            '*,ville:ville_id(nom),sport:sport_id(nom)',
            [],
            'nom.asc'
        );
        return array_map([self::class, 'aplatir'], $lignes);
    }

    public static function trouver(int $id): ?array
    {
        $resultats = SupabaseClient::select(
            'equipe',
            '*,ville:ville_id(nom),sport:sport_id(nom)',
            ['id' => 'eq.' . $id]
        );
        return isset($resultats[0]) ? self::aplatir($resultats[0]) : null;
    }

    public static function parVille(int $villeId): array
    {
        return SupabaseClient::select('equipe', '*', ['ville_id' => 'eq.' . $villeId], 'nom.asc');
    }

    public static function creer(string $nom, int $villeId, int $sportId): int
    {
        $ligne = SupabaseClient::insert('equipe', [
            'nom' => $nom, 'ville_id' => $villeId, 'sport_id' => $sportId,
        ]);
        return (int) $ligne['id'];
    }

    public static function supprimer(int $id): void
    {
        SupabaseClient::delete('equipe', ['id' => 'eq.' . $id]);
    }
}
```

#### `aplatir(array $ligne): array` (privée)

- **À quoi ça sert :** simplifier une ligne reçue avec des embeds.
- **Avant :** `['id' => 1, 'nom' => 'Les Dodos', 'ville' => ['nom' => 'Saint-Denis'], 'sport' => ['nom' => 'Football']]`
- **Après :** `['id' => 1, 'nom' => 'Les Dodos', 'ville_nom' => 'Saint-Denis', 'sport_nom' => 'Football']`
- **Comment ça marche :** crée deux nouvelles cases `ville_nom` et `sport_nom` (avec `?? null` si la ville ou le sport manque), puis `unset` supprime les sous-fiches devenues inutiles.

#### `toutes(): array`

- **À quoi ça sert :** lister toutes les équipes avec le nom de leur ville et de leur sport.
- **Comment ça marche :** `select` avec l'embed `'*,ville:ville_id(nom),sport:sport_id(nom)'`, tri par nom. Puis `array_map([self::class, 'aplatir'], $lignes)` = « applique `aplatir` à chaque ligne de la liste et renvoie la nouvelle liste ».

#### `trouver(int $id): ?array`

- Même embed, filtre sur l'`id`. `isset($resultats[0]) ? self::aplatir(...) : null` : si une ligne existe, on l'aplatit, sinon `null`.

#### `parVille(int $villeId): array`

- **À quoi ça sert :** les équipes d'une ville donnée (sans embed). Prévue, pas encore utilisée.

#### `creer(string $nom, int $villeId, int $sportId): int` et `supprimer(int $id): void`

- Création et suppression, sur le même modèle que `Ville`.

### 7.5 `Epreuve.php` — table `epreuve` (colonnes : `id`, `sport_id`, `ville_id`, `date_heure`, `statut`)

Une épreuve est un match ou une compétition d'un sport, dans une ville, à une
date. Son `statut` vaut `a_venir`, `en_cours` ou `terminee`.

```php
class Epreuve
{
    private static function aplatir(array $ligne): array
    {
        $ligne['sport_nom'] = $ligne['sport']['nom'] ?? null;
        $ligne['ville_nom'] = $ligne['ville']['nom'] ?? null;
        unset($ligne['sport'], $ligne['ville']);
        return $ligne;
    }

    public static function toutes(): array
    {
        $lignes = SupabaseClient::select(
            'epreuve',
            '*,sport:sport_id(nom),ville:ville_id(nom)',
            [],
            'date_heure.desc'
        );
        return array_map([self::class, 'aplatir'], $lignes);
    }

    public static function trouver(int $id): ?array
    {
        $resultats = SupabaseClient::select(
            'epreuve',
            '*,sport:sport_id(nom),ville:ville_id(nom)',
            ['id' => 'eq.' . $id]
        );
        return isset($resultats[0]) ? self::aplatir($resultats[0]) : null;
    }

    public static function aVenir(): array
    {
        $lignes = SupabaseClient::select(
            'epreuve',
            '*,sport:sport_id(nom),ville:ville_id(nom)',
            ['statut' => 'eq.a_venir'],
            'date_heure.asc'
        );
        return array_map([self::class, 'aplatir'], $lignes);
    }

    public static function creer(int $sportId, int $villeId, ?string $dateHeure, string $statut = 'a_venir'): int
    {
        $ligne = SupabaseClient::insert('epreuve', [
            'sport_id' => $sportId, 'ville_id' => $villeId,
            'date_heure' => $dateHeure, 'statut' => $statut,
        ]);
        return (int) $ligne['id'];
    }

    public static function changerStatut(int $id, string $statut): void
    {
        SupabaseClient::update('epreuve', ['id' => 'eq.' . $id], ['statut' => $statut]);
    }

    public static function supprimer(int $id): void
    {
        SupabaseClient::delete('epreuve', ['id' => 'eq.' . $id]);
    }
}
```

| Méthode | À quoi ça sert | Reçoit | Renvoie | Comment |
|---|---|---|---|---|
| `aplatir($ligne)` | Simplifier une ligne | une ligne avec sous-fiches | la ligne aplatie | Identique à `Equipe::aplatir`. |
| `toutes()` | Toutes les épreuves pour le planning | rien | liste aplatie | Embed sport + ville, tri par date **décroissante** (les plus récentes en premier). |
| `trouver($id)` | Une épreuve | l'identifiant | fiche ou `null` | |
| `aVenir()` | Les épreuves à venir pour l'accueil | rien | liste aplatie | Filtre `statut = a_venir`, tri par date **croissante** (la prochaine en premier). |
| `creer($sportId, $villeId, $dateHeure, $statut)` | Créer une épreuve | ids du sport et de la ville, date (peut être `null`), statut (`a_venir` par défaut) | nouvel `id` | Pas encore utilisée par une page. |
| `changerStatut($id, $statut)` | Passer une épreuve « en cours » ou « terminée » | id, nouveau statut | rien | Pas encore utilisée. |
| `supprimer($id)` | Supprimer | id | rien | Pas encore utilisée. |

### 7.6 `MembreEquipe.php` — table `membre_equipe` (colonnes : `equipe_id`, `utilisateur_id`, `role_interne`, `penalite`, `recompense`)

Cette table relie les équipes et les utilisateurs : « telle personne fait partie
de telle équipe ». On appelle cela une **table d'association**. Une ligne est
identifiée par le **couple** `equipe_id` + `utilisateur_id`. `role_interne` vaut
`joueur` ou `capitaine`.

```php
class MembreEquipe
{
    public static function parEquipe(int $equipeId): array
    {
        $lignes = SupabaseClient::select(
            'membre_equipe',
            '*,utilisateur:utilisateur_id(nom_compte,email)',
            ['equipe_id' => 'eq.' . $equipeId]
        );
        $lignes = array_map(function (array $ligne) {
            $ligne['nom_compte'] = $ligne['utilisateur']['nom_compte'] ?? null;
            $ligne['email']      = $ligne['utilisateur']['email'] ?? null;
            unset($ligne['utilisateur']);
            return $ligne;
        }, $lignes);

        usort($lignes, function (array $a, array $b) {
            $capitaineA = $a['role_interne'] === 'capitaine' ? 0 : 1;
            $capitaineB = $b['role_interne'] === 'capitaine' ? 0 : 1;
            return $capitaineA <=> $capitaineB ?: strcmp((string) $a['nom_compte'], (string) $b['nom_compte']);
        });

        return $lignes;
    }
```

#### `parEquipe(int $equipeId): array`

- **À quoi ça sert :** la liste des membres d'une équipe, capitaine en premier puis par ordre alphabétique.
- **Ce qu'elle reçoit :** l'`id` de l'équipe.
- **Ce qu'elle renvoie :** une liste de fiches avec `nom_compte`, `email`, `role_interne`, `penalite`, `recompense`.
- **Comment ça marche :**
  1. `select` avec l'embed `utilisateur:utilisateur_id(nom_compte,email)` : pour chaque membre, va chercher son pseudo et son email dans la table `utilisateur`.
  2. `array_map(function ...)` : aplatit chaque ligne (`$ligne['utilisateur']['nom_compte']` → `$ligne['nom_compte']`), comme les `aplatir()` précédents mais écrit sur place.
  3. `usort($lignes, function ($a, $b) {...})` : trie la liste avec une règle personnalisée. La fonction reçoit deux membres `$a` et `$b` et doit dire lequel passe devant :
     - `$capitaineA` vaut `0` si `$a` est capitaine, sinon `1` (idem `$capitaineB`).
     - `$capitaineA <=> $capitaineB` : compare ces deux nombres. Le capitaine (0) passe devant le joueur (1).
     - `?: strcmp(...)` : si la comparaison donne `0` (même rôle), on départage par ordre alphabétique des pseudos. `strcmp` compare deux textes ; `(string)` garantit qu'on lui donne bien du texte.
  Ce tri est fait en PHP parce que Supabase ne sait pas trier sur la condition « est capitaine ».

```php
    public static function parUtilisateur(int $utilisateurId): array
    {
        $lignes = SupabaseClient::select(
            'membre_equipe',
            '*,equipe:equipe_id(nom,ville:ville_id(nom),sport:sport_id(nom))',
            ['utilisateur_id' => 'eq.' . $utilisateurId]
        );
        return array_map(function (array $ligne) {
            $ligne['equipe_nom'] = $ligne['equipe']['nom'] ?? null;
            $ligne['ville_nom']  = $ligne['equipe']['ville']['nom'] ?? null;
            $ligne['sport_nom']  = $ligne['equipe']['sport']['nom'] ?? null;
            $ligne['equipe_id']  = $ligne['equipe_id'] ?? null;
            unset($ligne['equipe']);
            return $ligne;
        }, $lignes);
    }
```

#### `parUtilisateur(int $utilisateurId): array`

- **À quoi ça sert :** les équipes dont une personne fait partie (page « Mon profil »).
- **Comment ça marche :** l'embed est **imbriqué** sur deux niveaux : `equipe:equipe_id(nom, ville:ville_id(nom), sport:sport_id(nom))` = « va chercher l'équipe, et dans l'équipe va chercher sa ville et son sport ». La réponse ressemble à `['equipe' => ['nom' => 'Les Dodos', 'ville' => ['nom' => 'Saint-Denis'], 'sport' => ['nom' => 'Football']]]`. La fonction anonyme remonte tout au premier niveau (`equipe_nom`, `ville_nom`, `sport_nom`). La ligne `$ligne['equipe_id'] = $ligne['equipe_id'] ?? null;` ne change rien (voir section 13).

```php
    public static function ajouter(int $equipeId, int $utilisateurId, string $roleInterne = 'joueur'): void
    {
        SupabaseClient::insert('membre_equipe', [
            'equipe_id' => $equipeId, 'utilisateur_id' => $utilisateurId, 'role_interne' => $roleInterne,
        ]);
    }

    public static function definirPenalite(int $equipeId, int $utilisateurId, ?string $penalite): void
    {
        SupabaseClient::update(
            'membre_equipe',
            ['equipe_id' => 'eq.' . $equipeId, 'utilisateur_id' => 'eq.' . $utilisateurId],
            ['penalite' => $penalite]
        );
    }

    public static function definirRecompense(int $equipeId, int $utilisateurId, ?string $recompense): void
    {
        SupabaseClient::update(
            'membre_equipe',
            ['equipe_id' => 'eq.' . $equipeId, 'utilisateur_id' => 'eq.' . $utilisateurId],
            ['recompense' => $recompense]
        );
    }

    public static function retirer(int $equipeId, int $utilisateurId): void
    {
        SupabaseClient::delete('membre_equipe', [
            'equipe_id' => 'eq.' . $equipeId, 'utilisateur_id' => 'eq.' . $utilisateurId,
        ]);
    }
}
```

| Méthode | À quoi ça sert | Reçoit | Comment |
|---|---|---|---|
| `ajouter($equipeId, $utilisateurId, $roleInterne)` | Mettre une personne dans une équipe | ids de l'équipe et de la personne, rôle (`joueur` par défaut) | Insère une ligne d'association. |
| `definirPenalite(...)` | Noter une pénalité pour un membre | ids + texte de la pénalité (ou `null` pour effacer) | Modifie la ligne identifiée par les **deux** ids (il faut les deux filtres pour viser une seule ligne). |
| `definirRecompense(...)` | Noter une récompense | idem | Idem sur la colonne `recompense`. |
| `retirer($equipeId, $utilisateurId)` | Enlever une personne d'une équipe | les deux ids | Supprime la ligne d'association. |

Ces quatre méthodes sont prévues pour la future administration des équipes ;
aucune page ne les appelle encore.

### 7.7 `Participation.php` — table `participation` (colonnes : `epreuve_id`, `equipe_id`, `statut`, `score`, `classement`)

Cette table relie les équipes et les épreuves. Une ligne signifie « telle
équipe est inscrite à telle épreuve » et, une fois l'épreuve jouée, contient
son `score` et son `classement` (rang). `statut` vaut `en_attente` ou `validee`.

```php
class Participation
{
    public static function parEpreuve(int $epreuveId): array
    {
        $lignes = SupabaseClient::select(
            'participation',
            '*,equipe:equipe_id(nom,ville:ville_id(nom))',
            ['epreuve_id' => 'eq.' . $epreuveId],
            'classement.asc.nullslast'
        );
        return array_map(function (array $ligne) {
            $ligne['equipe_nom'] = $ligne['equipe']['nom'] ?? null;
            $ligne['ville_nom']  = $ligne['equipe']['ville']['nom'] ?? null;
            unset($ligne['equipe']);
            return $ligne;
        }, $lignes);
    }

    public static function inscrire(int $epreuveId, int $equipeId, string $statut = 'en_attente'): void
    {
        SupabaseClient::insert('participation', [
            'epreuve_id' => $epreuveId, 'equipe_id' => $equipeId, 'statut' => $statut,
        ]);
    }

    public static function saisirResultat(int $epreuveId, int $equipeId, int $score, int $classement): void
    {
        SupabaseClient::update(
            'participation',
            ['epreuve_id' => 'eq.' . $epreuveId, 'equipe_id' => 'eq.' . $equipeId],
            ['score' => $score, 'classement' => $classement]
        );
    }

    public static function validerInscription(int $epreuveId, int $equipeId): void
    {
        SupabaseClient::update(
            'participation',
            ['epreuve_id' => 'eq.' . $epreuveId, 'equipe_id' => 'eq.' . $equipeId],
            ['statut' => 'validee']
        );
    }

    public static function classementGeneral(): array
    {
        return SupabaseClient::select('vue_classement_general', '*', [], 'total_points.desc');
    }
}
```

| Méthode | À quoi ça sert | Reçoit | Renvoie | Comment |
|---|---|---|---|---|
| `parEpreuve($epreuveId)` | Les équipes inscrites à une épreuve, avec leur résultat | id de l'épreuve | liste avec `equipe_nom`, `ville_nom`, `score`, `classement`, `statut` | Embed imbriqué équipe → ville. Tri par `classement` croissant, les équipes sans classement (`NULL`) à la fin. Aplatissement sur place. |
| `inscrire($epreuveId, $equipeId, $statut)` | Inscrire une équipe | les deux ids, statut (`en_attente` par défaut) | rien | Insère. Pas encore utilisée. |
| `saisirResultat($epreuveId, $equipeId, $score, $classement)` | Enregistrer le résultat | les deux ids, score, rang | rien | Modifie la ligne visée par les deux ids. Pas encore utilisée. |
| `validerInscription($epreuveId, $equipeId)` | Accepter une inscription | les deux ids | rien | Passe `statut` à `validee`. Pas encore utilisée. |
| `classementGeneral()` | Le classement des villes | rien | liste de `ville_id`, `ville_nom`, `total_points` | Lit la **vue** `vue_classement_general` (un calcul enregistré dans la base, voir 10.2), triée par points décroissants. |

---

## 8. Les contrôleurs — `app/Controllers`

Un contrôleur reçoit la requête, demande les données aux modèles, puis choisit
la vue. Tous héritent de `Controller` (donc de `afficher()`).

Rappel : les paramètres pris dans l'adresse arrivent en texte (`string $id`).

### 8.1 `AccueilController.php`

```php
class AccueilController extends Controller
{
    public function index(): void
    {
        $epreuvesAVenir = Epreuve::aVenir();

        $this->afficher('accueil/index', [
            'titre'          => 'Accueil',
            'epreuvesAVenir' => $epreuvesAVenir,
        ]);
    }
}
```

#### `index(): void`

- **Page :** `/`.
- **Comment ça marche :** demande au modèle les épreuves à venir, puis affiche la vue `accueil/index` en lui passant `titre` (utilisé dans l'onglet du navigateur) et `epreuvesAVenir`.

### 8.2 `AuthController.php` — connexion, inscription, déconnexion

```php
class AuthController extends Controller
{
    public function afficherLogin(): void
    {
        $this->afficher('auth/login', ['titre' => 'Connexion', 'erreur' => null]);
    }
```

#### `afficherLogin(): void`

- **Page :** `GET /login`. Affiche le formulaire, sans message d'erreur (`null`).

```php
    public function traiterLogin(): void
    {
        $email = trim($_POST['email'] ?? '');
        $motDePasse = $_POST['mot_de_passe'] ?? '';

        $utilisateur = Utilisateur::verifierIdentifiants($email, $motDePasse);

        if ($utilisateur === null) {
            $this->afficher('auth/login', [
                'titre'  => 'Connexion',
                'erreur' => 'Email ou mot de passe incorrect.',
            ]);
            return;
        }

        Auth::connecter($utilisateur);
        header('Location: /');
        exit;
    }
```

#### `traiterLogin(): void`

- **Page :** `POST /login`.
- **Variables :**

| Variable | Contenu |
|---|---|
| `$email` | Le champ `email` du formulaire, avec `trim()` pour enlever les espaces autour. `?? ''` = chaîne vide si le champ manque. |
| `$motDePasse` | Le champ `mot_de_passe`, sans `trim` (un espace peut faire partie d'un mot de passe). |
| `$utilisateur` | La fiche renvoyée par `verifierIdentifiants`, ou `null`. |

- **Comment ça marche :** si `null` → ré-affiche le formulaire avec un message volontairement vague (« Email ou mot de passe incorrect ») et `return` (fin). Sinon → `Auth::connecter()` puis redirection vers l'accueil et `exit`.

```php
    public function afficherInscription(): void
    {
        $this->afficher('auth/inscription', [
            'titre'  => 'Inscription',
            'erreur' => null,
            'villes' => Ville::toutes(),
        ]);
    }
```

#### `afficherInscription(): void`

- **Page :** `GET /inscription`. Affiche le formulaire avec la liste des villes (pour le menu déroulant).

```php
    public function traiterInscription(): void
    {
        $nomCompte = trim($_POST['nom_compte'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $motDePasse = $_POST['mot_de_passe'] ?? '';
        $motDePasseConfirmation = $_POST['mot_de_passe_confirmation'] ?? '';
        $villeId = $_POST['ville_id'] !== '' ? (int) $_POST['ville_id'] : null;

        $erreur = null;
        if ($nomCompte === '' || $email === '' || $motDePasse === '') {
            $erreur = 'Tous les champs marqués * sont obligatoires.';
        } elseif ($motDePasse !== $motDePasseConfirmation) {
            $erreur = 'Les deux mots de passe ne correspondent pas.';
        } elseif (strlen($motDePasse) < 8) {
            $erreur = 'Le mot de passe doit faire au moins 8 caractères.';
        } elseif (Utilisateur::trouverParEmail($email) !== null) {
            $erreur = 'Un compte existe déjà avec cet email.';
        }

        if ($erreur !== null) {
            $this->afficher('auth/inscription', [
                'titre'  => 'Inscription',
                'erreur' => $erreur,
                'villes' => Ville::toutes(),
            ]);
            return;
        }

        $id = Utilisateur::creer($nomCompte, $email, $motDePasse, 'joueur', $villeId);
        $utilisateur = Utilisateur::trouver($id);

        Auth::connecter($utilisateur);
        header('Location: /profil');
        exit;
    }
```

#### `traiterInscription(): void`

- **Page :** `POST /inscription`.
- **Variables :**

| Variable | D'où elle vient | Règle vérifiée |
|---|---|---|
| `$nomCompte` | champ `nom_compte`, nettoyé par `trim` | obligatoire |
| `$email` | champ `email`, nettoyé | obligatoire, et aucun compte ne doit déjà l'utiliser |
| `$motDePasse` | champ `mot_de_passe` | obligatoire, au moins 8 caractères (`strlen` = longueur) |
| `$motDePasseConfirmation` | champ `mot_de_passe_confirmation` | doit être identique au mot de passe (`!==` = différent) |
| `$villeId` | champ `ville_id` (menu déroulant) | si vide (`''`, choix « Aucune ») → `null` ; sinon converti en entier |
| `$erreur` | calculée | `null` tant que tout va bien ; sinon le **premier** message d'erreur rencontré (les `elseif` s'arrêtent à la première règle qui échoue) |
| `$id` | `Utilisateur::creer(...)` | identifiant du nouveau compte, créé avec le rôle `joueur` (on ne peut pas s'auto-déclarer admin) |
| `$utilisateur` | `Utilisateur::trouver($id)` | la fiche complète rechargée depuis la base |

- **Comment ça marche :** si `$erreur` n'est pas `null` → ré-affiche le formulaire avec le message (la vue pré-remplit les champs déjà saisis). Sinon → création du compte, connexion automatique et redirection vers `/profil`.

```php
    public function deconnexion(): void
    {
        Auth::deconnecter();
        header('Location: /');
        exit;
    }
}
```

#### `deconnexion(): void`

- **Page :** `GET /deconnexion`. Efface la session et renvoie à l'accueil.

### 8.3 `ProfilController.php`

```php
class ProfilController extends Controller
{
    public function index(): void
    {
        Auth::exigerConnexion();

        $utilisateur = Utilisateur::trouver(Auth::utilisateur()['id']);
        $equipes = MembreEquipe::parUtilisateur(Auth::utilisateur()['id']);

        $this->afficher('profil/index', [
            'titre'       => 'Mon profil',
            'utilisateur' => $utilisateur,
            'equipes'     => $equipes,
        ]);
    }
}
```

#### `index(): void`

- **Page :** `/profil`, réservée aux connectés.
- **Comment ça marche :**
  1. `Auth::exigerConnexion()` : le videur. Si non connecté, on est redirigé vers `/login` et la suite ne s'exécute pas.
  2. `Auth::utilisateur()['id']` : l'`id` de la personne connectée, lu en session.
  3. `$utilisateur` : sa fiche complète, **rechargée depuis la base** (pour avoir des données à jour plutôt que la copie en session).
  4. `$equipes` : la liste de ses équipes.
  5. Affiche la vue `profil/index`.

### 8.4 `EpreuveController.php`

```php
class EpreuveController extends Controller
{
    public function planning(): void
    {
        $epreuves = Epreuve::toutes();

        $this->afficher('epreuves/planning', [
            'titre'    => 'Planning',
            'epreuves' => $epreuves,
        ]);
    }

    public function detail(string $id): void
    {
        $epreuve = Epreuve::trouver((int) $id);

        if ($epreuve === null) {
            http_response_code(404);
            require __DIR__ . '/../Views/erreur_404.php';
            return;
        }

        $participations = Participation::parEpreuve((int) $id);

        $this->afficher('epreuves/detail', [
            'titre'          => $epreuve['sport_nom'] . ' — ' . $epreuve['ville_nom'],
            'epreuve'        => $epreuve,
            'participations' => $participations,
        ]);
    }
}
```

#### `planning(): void`

- **Page :** `/planning`. Toutes les épreuves dans un tableau.

#### `detail(string $id): void`

- **Page :** `/epreuves/5` par exemple (`$id` vaut `"5"`).
- **Comment ça marche :**
  1. `Epreuve::trouver((int) $id)` : convertit `"5"` en `5` et cherche l'épreuve.
  2. Si `null` (numéro inexistant) : code `404`, page d'erreur, `return`.
  3. Sinon, charge les participations et affiche la vue. Le `titre` est fabriqué en collant le sport et la ville : « Football — Saint-Denis ».

### 8.5 `EquipeController.php`

```php
class EquipeController extends Controller
{
    public function liste(): void
    {
        $equipes = Equipe::toutes();

        $this->afficher('equipes/liste', [
            'titre'   => 'Équipes',
            'equipes' => $equipes,
        ]);
    }

    public function detail(string $id): void
    {
        $equipe = Equipe::trouver((int) $id);

        if ($equipe === null) {
            http_response_code(404);
            require __DIR__ . '/../Views/erreur_404.php';
            return;
        }

        $membres = MembreEquipe::parEquipe((int) $id);

        $this->afficher('equipes/detail', [
            'titre'   => $equipe['nom'],
            'equipe'  => $equipe,
            'membres' => $membres,
        ]);
    }
}
```

#### `liste(): void`

- **Page :** `/equipes`. Grille de cartes.

#### `detail(string $id): void`

- **Page :** `/equipes/3`. Même logique que l'épreuve : chercher, 404 si absent, sinon charger les membres et afficher.

### 8.6 `ClassementController.php`

```php
class ClassementController extends Controller
{
    public function index(): void
    {
        $classement = Participation::classementGeneral();

        $this->afficher('classement/index', [
            'titre'      => 'Classement général',
            'classement' => $classement,
        ]);
    }
}
```

#### `index(): void`

- **Page :** `/classement`. Lit la vue SQL du classement et l'affiche.

### 8.7 `AdminController.php`

```php
class AdminController extends Controller
{
    public function __construct()
    {
        // Toutes les actions de ce contrôleur exigent d'être admin
        Auth::exigerAdmin();
    }

    public function tableauDeBord(): void
    {
        $this->afficher('admin/tableau_de_bord', [
            'titre'  => 'Administration',
            'villes' => Ville::toutes(),
            'sports' => Sport::tous(),
        ]);
    }

    public function nouvelleVille(): void
    {
        $this->afficher('admin/villes/nouvelle', ['titre' => 'Nouvelle ville']);
    }

    public function creerVille(): void
    {
        $nom = trim($_POST['nom'] ?? '');

        if ($nom === '') {
            $this->afficher('admin/villes/nouvelle', [
                'titre'  => 'Nouvelle ville',
                'erreur' => 'Le nom est obligatoire.',
            ]);
            return;
        }

        Ville::creer($nom);
        header('Location: /admin');
        exit;
    }

    public function supprimerVille(string $id): void
    {
        Ville::supprimer((int) $id);
        header('Location: /admin');
        exit;
    }
}
```

#### `__construct()`

- **Ce que c'est :** le **constructeur**, une méthode spéciale exécutée automatiquement au moment du `new AdminController()` fait par le routeur, **avant** l'action demandée.
- **À quoi ça sert :** placer le videur `Auth::exigerAdmin()` une seule fois pour **toutes** les pages d'administration. Impossible d'oublier de protéger une page.

#### `tableauDeBord(): void`

- **Page :** `/admin`. Liste des villes (avec bouton supprimer) et des sports.

#### `nouvelleVille(): void`

- **Page :** `GET /admin/villes/nouvelle`. Formulaire de création.

#### `creerVille(): void`

- **Page :** `POST /admin/villes`.
- **Variable `$nom` :** le champ `nom`, nettoyé. S'il est vide → formulaire ré-affiché avec `erreur`. Sinon → `Ville::creer($nom)` et redirection vers `/admin`.

#### `supprimerVille(string $id): void`

- **Page :** `POST /admin/villes/4/supprimer`. Supprime la ville puis redirige. Le formulaire côté vue demande une confirmation avant d'envoyer.

Le commentaire du fichier indique que ce CRUD (Créer, Lire, Modifier,
Supprimer) des villes est le **modèle à recopier** pour les sports, équipes et
épreuves.

---

## 9. Les vues — `app/Views`

Une vue est un fichier HTML dans lequel on glisse de petits morceaux de PHP pour
afficher les données. Deux écritures reviennent partout :

| Écriture | Signification |
|---|---|
| `<?= $x ?>` | « Affiche la valeur de `$x` ici ». Raccourci de `<?php echo $x; ?>`. |
| `<?php if (...): ?> ... <?php endif; ?>` | Bloc conditionnel écrit avec `:` et `endif` au lieu d'accolades, plus lisible au milieu du HTML. Même chose avec `foreach ... endforeach`. |

**Règle de sécurité appliquée partout :** toute donnée affichée passe par
`htmlspecialchars()`. Cette fonction transforme les caractères spéciaux du HTML
(`<`, `>`, `&`, `"`) en leur équivalent inoffensif. Sans elle, un utilisateur
pourrait taper du code dans son pseudo et le faire exécuter chez les autres
visiteurs (attaque **XSS**).

### 9.1 Les morceaux communs — `partials/`

#### `layout.php` — le squelette de toutes les pages

```php
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titre) ? htmlspecialchars($titre) . ' — ' : '' ?>Entrevilles-Reu</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="conteneur">
        <?php require $cheminVue; ?>
    </main>

    <?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
```

| Ligne | Explication |
|---|---|
| `<!DOCTYPE html>`, `<html lang="fr">` | Début standard d'une page HTML en français. |
| `<meta charset="UTF-8">` | Encodage des caractères (accents). |
| `<meta name="viewport" ...>` | Adaptation aux écrans de téléphone. |
| `<title>...` | Le titre de l'onglet : « Équipes — Entrevilles-Reu » si `$titre` existe, sinon juste « Entrevilles-Reu ». |
| `<link rel="stylesheet" ...>` | Charge la feuille de style. |
| `require navbar.php` | Insère la barre de navigation. |
| `<main class="conteneur">` + `require $cheminVue` | Insère **la vue demandée** par le contrôleur (`$cheminVue` vient de `Controller::afficher`). |
| `require footer.php` | Insère le pied de page. |

**Variables attendues :** `$titre` (facultative), `$cheminVue` (obligatoire).

#### `navbar.php` — la barre de navigation

```php
<nav class="navbar">
    <a href="/" class="navbar-logo">Entrevilles-Reu 🏝️</a>
    <div class="navbar-liens">
        <a href="/">Accueil</a>
        <a href="/planning">Planning</a>
        <a href="/equipes">Équipes</a>
        <a href="/classement">Classement</a>

        <?php if (Auth::estConnecte()): ?>
            <?php if (Auth::estAdmin()): ?>
                <a href="/admin">Administration</a>
            <?php endif; ?>
            <a href="/profil">Profil (<?= htmlspecialchars(Auth::utilisateur()['nom_compte']) ?>)</a>
            <a href="/deconnexion">Déconnexion</a>
        <?php else: ?>
            <a href="/login">Connexion</a>
            <a href="/inscription">Créer un compte</a>
        <?php endif; ?>
    </div>
</nav>
```

- Les quatre premiers liens sont toujours visibles.
- Si connecté : lien « Administration » (seulement pour un admin), « Profil (pseudo) », « Déconnexion ».
- Sinon : « Connexion », « Créer un compte ».
- La barre ne reçoit aucune variable : elle interroge directement `Auth`.

#### `footer.php`

Une seule ligne de texte dans une balise `<footer class="pied-page">`.

#### `erreur_404.php`

Page complète et autonome (avec son propre `<html>`), affichée par le routeur
ou par un contrôleur quand une ressource n'existe pas. Contient un titre « 404
— Page introuvable » et un lien vers l'accueil.

### 9.2 Les vues de pages

#### `accueil/index.php` — variable : `$epreuvesAVenir`

```php
<div class="hero">
    <div class="hero-contenu">
        <h1>Le défi sportif des communes de La Réunion</h1>
        <p>Épreuves, équipes et classement inter-villes, en direct.</p>
    </div>
</div>

<h2>Prochaines épreuves</h2>

<?php if (empty($epreuvesAVenir)): ?>
    <p>Aucune épreuve à venir pour le moment.</p>
<?php else: ?>
    <div class="grille-cartes">
        <?php foreach ($epreuvesAVenir as $epreuve): ?>
            <div class="carte">
                <span class="badge badge-<?= htmlspecialchars($epreuve['statut']) ?>">
                    <?= htmlspecialchars($epreuve['statut']) ?>
                </span>
                <h3><?= htmlspecialchars($epreuve['sport_nom']) ?></h3>
                <p>À <?= htmlspecialchars($epreuve['ville_nom']) ?></p>
                <?php if ($epreuve['date_heure']): ?>
                    <p><?= (new DateTime($epreuve['date_heure']))->format('d/m/Y à H:i') ?></p>
                <?php endif; ?>
                <a href="/epreuves/<?= $epreuve['id'] ?>" class="bouton">Voir le détail</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
```

- `empty($epreuvesAVenir)` : « la liste est-elle vide ? » → message, sinon grille.
- `foreach` : une carte par épreuve.
- `class="badge badge-<?= statut ?>"` : la classe CSS est construite à partir du statut (`badge-a_venir`), ce qui donne sa couleur au badge.
- `new DateTime($epreuve['date_heure'])` : transforme la date brute de la base (`2026-09-20T15:00:00`) en objet date ; `->format('d/m/Y à H:i')` l'écrit « 20/09/2026 à 15:00 ». Le `if` évite une erreur si la date est vide.
- Le lien « Voir le détail » pointe vers `/epreuves/<id>`.

#### `auth/login.php` — variable : `$erreur`

```php
<h1>Connexion</h1>

<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="POST" action="/login">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>

    <label for="mot_de_passe">Mot de passe</label>
    <input type="password" id="mot_de_passe" name="mot_de_passe" required>

    <button type="submit">Se connecter</button>
</form>
```

- Affiche le message d'erreur s'il y en a un.
- `<form method="POST" action="/login">` : à l'envoi, le navigateur fait un `POST /login` avec les champs.
- `name="email"` : c'est ce nom que PHP retrouve dans `$_POST['email']`.
- `required` : le navigateur refuse d'envoyer un champ vide (première barrière ; le serveur revérifie).
- `type="password"` : masque la saisie.

#### `auth/inscription.php` — variables : `$erreur`, `$villes`

Même structure avec cinq champs : `nom_compte`, `email`, `ville_id` (menu
déroulant), `mot_de_passe`, `mot_de_passe_confirmation`. Points particuliers :

- `value="<?= htmlspecialchars($_POST['nom_compte'] ?? '') ?>"` : après une erreur, le champ est **pré-rempli** avec ce que l'utilisateur avait tapé.
- Le menu déroulant commence par `<option value="">— Aucune —</option>` (valeur vide = pas de ville), puis une option par ville de `$villes`.
- `minlength="8"` : le navigateur exige 8 caractères ; le serveur revérifie avec `strlen`.
- Un lien « Déjà un compte ? Connecte-toi » vers `/login`.

#### `profil/index.php` — variables : `$utilisateur`, `$equipes`

- Une carte avec Nom, Email, Rôle.
- Puis « Mes équipes » : message si vide, sinon une carte par équipe avec `equipe_nom`, `ville_nom — sport_nom`, un badge « Capitaine » si `role_interne === 'capitaine'`, et un lien vers `/equipes/<equipe_id>`.

#### `epreuves/planning.php` — variable : `$epreuves`

Un tableau HTML (`<table>`) avec une ligne (`<tr>`) par épreuve : Sport (lien
vers le détail), Ville, Date (`—` si vide), Statut (badge coloré).

#### `epreuves/detail.php` — variables : `$epreuve`, `$participations`

- Badge de statut, titre = sport, « À ville », date.
- « Équipes participantes » : message si vide, sinon tableau Classement / Équipe / Ville / Score / Statut inscription.
- `htmlspecialchars((string) ($participation['classement'] ?? '—'))` : `??` met un tiret si pas de classement ; `(string)` convertit le nombre en texte car `htmlspecialchars` exige du texte.

#### `equipes/liste.php` — variable : `$equipes`

Grille de cartes : nom, ville, sport, bouton « Voir l'équipe ».

#### `equipes/detail.php` — variables : `$equipe`, `$membres`

- Titre = nom de l'équipe, ligne « Ville : … — Sport : … ».
- Tableau des membres : Nom, Rôle (badge « Capitaine » ou texte du rôle), Pénalité et Récompense (`—` si vides).

#### `classement/index.php` — variable : `$classement`

```php
<?php foreach ($classement as $i => $ligne): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= htmlspecialchars($ligne['ville_nom']) ?></td>
        <td><strong><?= (int) $ligne['total_points'] ?></strong></td>
    </tr>
<?php endforeach; ?>
```

- `foreach ($classement as $i => $ligne)` : `$i` est la position dans la liste (0, 1, 2…), `$ligne` la fiche. Le rang affiché est `$i + 1` (1, 2, 3…) : comme la liste est déjà triée par points, la position **est** le rang.

#### `admin/tableau_de_bord.php` — variables : `$villes`, `$sports`

- Bouton « + Ajouter une ville » vers `/admin/villes/nouvelle`.
- Tableau des villes ; chaque ligne contient un mini-formulaire :
  ```php
  <form method="POST" action="/admin/villes/<?= $ville['id'] ?>/supprimer" onsubmit="return confirm('Supprimer cette ville ?');" style="display:inline;">
      <button type="submit">Supprimer</button>
  </form>
  ```
  `onsubmit="return confirm(...)"` est la **seule ligne de JavaScript** du projet : elle ouvre une boîte « OK / Annuler » ; si l'on annule, le formulaire n'est pas envoyé. On utilise `POST` (et non un simple lien) pour une suppression, car une action qui modifie des données ne doit jamais se déclencher par un simple clic sur un lien `GET`.
- Liste des sports.

#### `admin/villes/nouvelle.php` — variable : `$erreur`

Formulaire `POST /admin/villes` avec un champ `nom`.

### 9.3 Récapitulatif : qui fournit quoi à quelle vue

| Vue | Contrôleur → méthode | Variables reçues |
|---|---|---|
| `accueil/index` | `AccueilController::index` | `titre`, `epreuvesAVenir` |
| `auth/login` | `AuthController::afficherLogin`, `traiterLogin` | `titre`, `erreur` |
| `auth/inscription` | `AuthController::afficherInscription`, `traiterInscription` | `titre`, `erreur`, `villes` |
| `profil/index` | `ProfilController::index` | `titre`, `utilisateur`, `equipes` |
| `epreuves/planning` | `EpreuveController::planning` | `titre`, `epreuves` |
| `epreuves/detail` | `EpreuveController::detail` | `titre`, `epreuve`, `participations` |
| `equipes/liste` | `EquipeController::liste` | `titre`, `equipes` |
| `equipes/detail` | `EquipeController::detail` | `titre`, `equipe`, `membres` |
| `classement/index` | `ClassementController::index` | `titre`, `classement` |
| `admin/tableau_de_bord` | `AdminController::tableauDeBord` | `titre`, `villes`, `sports` |
| `admin/villes/nouvelle` | `AdminController::nouvelleVille`, `creerVille` | `titre`, `erreur` (facultatif) |

---

# PARTIE C — ANNEXES

## 10. La base de données — `database/`

### 10.1 Les tables (schéma reconstitué)

Le fichier qui crée les tables (`schema.sql` / `BDDEntreVilles_supabase.sql`,
cité dans les commentaires) **n'est pas dans le projet**. Voici les tables
telles que le code les utilise :

| Table | Colonnes | Explication |
|---|---|---|
| `ville` | `id`, `nom` | Une commune. |
| `sport` | `id`, `nom`, `description` | Une discipline. |
| `utilisateur` | `id`, `email` (unique), `nom_compte`, `role`, `ville_id` → `ville`, `mot_de_passe` | Un compte. `role` = `joueur` ou `admin`. `ville_id` peut être vide. |
| `equipe` | `id`, `nom`, `ville_id` → `ville`, `sport_id` → `sport` | Une équipe appartient à une ville et pratique un sport. |
| `membre_equipe` | `equipe_id` → `equipe`, `utilisateur_id` → `utilisateur`, `role_interne`, `penalite`, `recompense` | Qui est dans quelle équipe. `role_interne` = `joueur` ou `capitaine`. |
| `epreuve` | `id`, `sport_id` → `sport`, `ville_id` → `ville`, `date_heure`, `statut` | Un événement sportif. `statut` = `a_venir`, `en_cours` ou `terminee`. |
| `participation` | `epreuve_id` → `epreuve`, `equipe_id` → `equipe`, `statut`, `score`, `classement` | Quelle équipe participe à quelle épreuve, et avec quel résultat. `statut` = `en_attente` ou `validee`. |
| `vue_classement_general` | `ville_id`, `ville_nom`, `total_points` | Pas une vraie table : un **calcul enregistré** (voir 10.2). |

La flèche `→` indique une **clé étrangère** : la colonne contient l'`id` d'une
ligne d'une autre table.

Schéma des liens :

```
ville ──< equipe >── sport          (une ville a plusieurs équipes ; une équipe a un sport)
ville ──< utilisateur               (un utilisateur peut être rattaché à une ville)
equipe ──< membre_equipe >── utilisateur    (plusieurs personnes par équipe, plusieurs équipes par personne)
epreuve ──< participation >── equipe        (plusieurs équipes par épreuve, plusieurs épreuves par équipe)
epreuve ── sport, ville             (une épreuve a un sport et un lieu)
```

### 10.2 `vue_classement_general.sql` — le classement calculé par la base

```sql
CREATE OR REPLACE VIEW vue_classement_general AS
SELECT
    v.id  AS ville_id,
    v.nom AS ville_nom,
    COALESCE(SUM(p.score), 0) AS total_points
FROM ville v
LEFT JOIN equipe e        ON e.ville_id = v.id
LEFT JOIN participation p ON p.equipe_id = e.id AND p.score IS NOT NULL
GROUP BY v.id, v.nom;
```

**Ce que c'est :** une **vue** est une requête enregistrée dans la base qui se
comporte ensuite comme une table en lecture seule. Supabase l'expose comme les
autres tables.

**Ce qu'elle calcule :** pour chaque ville, la somme des scores de toutes ses
équipes dans toutes les épreuves.

| Ligne SQL | En français |
|---|---|
| `SELECT v.id, v.nom, ...` | Pour chaque ville, renvoie son id, son nom… |
| `COALESCE(SUM(p.score), 0)` | …et la somme des scores. `COALESCE(x, 0)` = « si la somme est vide (aucun score), mets 0 ». |
| `FROM ville v` | En partant de la table des villes (surnommée `v`). |
| `LEFT JOIN equipe e ON e.ville_id = v.id` | Rattache les équipes de chaque ville. `LEFT` = garde aussi les villes **sans** équipe. |
| `LEFT JOIN participation p ON ... AND p.score IS NOT NULL` | Rattache les participations de ces équipes, seulement celles qui ont un score. |
| `GROUP BY v.id, v.nom` | Regroupe par ville (une ligne de résultat par ville). |

**Pourquoi une vue plutôt qu'un calcul en PHP ?** Supabase ne permet pas de
faire ce type de regroupement (`GROUP BY`) dans une simple adresse REST. La vue
déplace le calcul dans la base, et `Participation::classementGeneral()` n'a plus
qu'à la lire.

### 10.3 `migration_mot_de_passe.sql`

```sql
ALTER TABLE utilisateur ADD COLUMN IF NOT EXISTS mot_de_passe VARCHAR(255);
ALTER TABLE utilisateur ADD CONSTRAINT utilisateur_email_unique UNIQUE (email);
```

- Première ligne : ajoute la colonne `mot_de_passe` (texte jusqu'à 255 caractères) si elle n'existe pas encore. Elle stockera l'empreinte bcrypt.
- Deuxième ligne : impose que deux comptes ne puissent pas avoir le même email. C'est une double sécurité : le PHP vérifie déjà, mais la base refuse aussi.

### 10.4 `creer_admin.php` — créer le premier administrateur (obsolète)

```php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$nomCompte = 'Administrateur';
$email = 'admin@entrevilles-reu.re';
$motDePasseClair = 'changez-moi123';

$hash = password_hash($motDePasseClair, PASSWORD_DEFAULT);

$pdo = Database::connexion();
$stmt = $pdo->prepare(
    "INSERT INTO utilisateur (email, nom_compte, role, mot_de_passe)
     VALUES (:email, :nom_compte, 'admin', :hash)
     ON CONFLICT (email) DO NOTHING"
);
$stmt->execute(['email' => $email, 'nom_compte' => $nomCompte, 'hash' => $hash]);
```

**À quoi ça sert :** comme l'inscription publique crée toujours des `joueur`, il
faut un moyen de créer le premier `admin`. Ce script se lance une fois en ligne
de commande (`php database/creer_admin.php`).

| Variable | Rôle |
|---|---|
| `$nomCompte`, `$email`, `$motDePasseClair` | Les identifiants de l'admin à créer (mot de passe à changer ensuite). |
| `$hash` | L'empreinte du mot de passe. |
| `$pdo` | Une connexion directe à la base (ancienne méthode PDO). |
| `$stmt` | La requête d'insertion préparée. `ON CONFLICT (email) DO NOTHING` = « si l'email existe déjà, ne fais rien » (on peut relancer sans créer de doublon). |

**Problème :** ce script utilise `Database.php`, un fichier qui **n'existe plus**
(remplacé par `SupabaseClient`). Il ne fonctionne donc plus (voir section 13).

---

## 11. La feuille de style — `assets/css/style.css`

Le CSS décide de l'apparence : couleurs, polices, espacements. Le thème est
« tableau de bord esport » : fond sombre, accents lumineux cyan et violet.

### 11.1 Les variables de couleur

En haut du fichier, un bloc `:root { ... }` définit des **variables CSS** : on
donne un nom à chaque couleur, puis on l'utilise partout avec `var(--nom)`.
Changer une valeur ici change tout le site.

| Variable | Valeur | Utilisée pour |
|---|---|---|
| `--fond` | `#0B0E14` (presque noir) | Le fond de la page |
| `--panneau` | `#12161F` | Le fond des cartes, tableaux, formulaires |
| `--panneau-alt` | `#171C27` | Fond au survol, en-têtes de tableau |
| `--bordure` | blanc à 8 % d'opacité | Bordures discrètes |
| `--bordure-vive` | blanc à 16 % | Bordures au survol |
| `--texte` | `#E7E9F0` (blanc cassé) | Texte principal |
| `--texte-doux` | `#8B93A7` (gris) | Texte secondaire, étiquettes |
| `--cyan`, `--cyan-vif` | `#2DE1C2`, `#4FF5D8` | Liens, accent principal, badge « à venir » |
| `--violet` | `#8B5CF6` | Accent secondaire |
| `--or` | `#F5B942` | Badge « terminée » |
| `--rose` | `#FF4D6D` | Badge « en cours », badge « Capitaine » |

Les couleurs `#RRGGBB` sont des codes hexadécimaux : deux chiffres pour le
rouge, deux pour le vert, deux pour le bleu.

### 11.2 Les principales classes

Une **classe CSS** est une étiquette posée sur une balise HTML
(`<div class="carte">`) pour lui appliquer un style.

| Classe | Rôle |
|---|---|
| `.conteneur` | Colonne centrée avec des marges, autour du contenu principal. |
| `.navbar`, `.navbar-logo`, `.navbar-liens` | La barre de navigation. Sous 640 px de large (téléphone), elle passe en colonne. |
| `.hero`, `.hero-contenu` | La grande bannière de l'accueil. |
| `.grille-cartes`, `.carte` | Une grille de cartes qui s'adapte à la largeur ; la carte se soulève légèrement au survol. |
| `.badge` + `.badge-a_venir` / `.badge-en_cours` / `.badge-terminee` | Les pastilles de statut. Le suffixe correspond **exactement** à la valeur en base, d'où `class="badge badge-<?= $statut ?>"` dans les vues. |
| `button`, `.bouton` | Les boutons et liens-boutons. |
| `.erreur` | Le message d'erreur des formulaires (rouge). |
| `.etat-vide` | Bloc « aucune donnée » (défini mais pas utilisé par les vues). |
| `.pied-page` | Le pied de page. |
| `@media (max-width: 640px)` | Règles spéciales pour les petits écrans. |

Les polices `Rajdhani` (titres) et `Inter` (texte) sont chargées depuis Google
Fonts par la ligne `@import` du début.

---

## 12. La mise en ligne automatique — `.github/workflows/deploy.yml`

```yaml
name: Déployer sur InfinityFree

on:
  push:
    branches:
      - main
  workflow_dispatch:

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Récupérer le code du repo
        uses: actions/checkout@v4

      - name: Déployer vers htdocs/
        uses: SamKirkland/FTP-Deploy-Action@v4.3.5
        with:
          server: ${{ secrets.FTP_HOST }}
          username: ${{ secrets.FTP_USERNAME }}
          password: ${{ secrets.FTP_PASSWORD }}
          local-dir: ./htdocs/
          server-dir: ./htdocs/
```

**Ce que c'est :** le code est stocké sur GitHub. **GitHub Actions** est un robot
qui peut exécuter des tâches automatiquement. Ce fichier lui dit : « à chaque
fois que du nouveau code arrive sur la branche `main`, copie le dossier
`htdocs/` sur le serveur d'hébergement par FTP ».

| Ligne | Explication |
|---|---|
| `on: push: branches: [main]` | Déclencheur : un envoi de code (`push`) sur `main`. |
| `workflow_dispatch` | Permet aussi de lancer à la main depuis le site GitHub. |
| `runs-on: ubuntu-latest` | Le robot travaille sur une machine Linux temporaire. |
| `actions/checkout@v4` | Étape 1 : récupérer le code. |
| `SamKirkland/FTP-Deploy-Action` | Étape 2 : envoyer par **FTP** (protocole de transfert de fichiers). |
| `${{ secrets.FTP_HOST }}` etc. | L'adresse, le nom et le mot de passe FTP sont des **secrets** stockés dans GitHub, jamais écrits dans le code. |
| `local-dir` / `server-dir` | On copie `htdocs/` du dépôt vers `htdocs/` du serveur. |

**Pourquoi tout est dans `htdocs/` ?** L'hébergeur InfinityFree n'autorise PHP
à lire que ce dossier. Les sous-dossiers sensibles sont donc protégés par
`.htaccess` plutôt que placés à l'extérieur.

---

## 13. Points d'attention : bugs et améliorations possibles

Ces observations viennent de la lecture du code. Elles sont classées par
importance et expliquées simplement.

1. **La configuration par `.env` ne fonctionne pas.** `config.php` lit le
   fichier `.env` avec `putenv()`, mais `SupabaseClient::init()` cherche les
   valeurs dans `$GLOBALS['env']`, que `putenv()` ne remplit pas. Avec un `.env`
   seul, le site s'arrête sur « variables manquantes ». Seul `env.local.php`
   fonctionne. Correction possible dans `config.php` : ajouter
   `$env[trim($cle)] = trim($valeur);` dans la boucle.
2. **`database/creer_admin.php` ne fonctionne plus** : il charge
   `app/Core/Database.php`, qui a été supprimé. À réécrire avec
   `Utilisateur::creer('Administrateur', $email, $motDePasse, 'admin')`.
3. **Petit avertissement possible à l'inscription** : la ligne
   `$_POST['ville_id'] !== ''` provoque un avertissement si le champ est absent
   du formulaire. Écrire `($_POST['ville_id'] ?? '') !== ''`.
4. **Les erreurs sont affichées à l'écran** (`display_errors = 1`). Sur un site
   public, cela révèle des chemins internes. À désactiver en ligne.
5. **Pas de protection CSRF.** Un site malveillant pourrait faire envoyer, à
   l'insu d'un admin connecté, le formulaire de suppression d'une ville. La
   parade classique est un **jeton** secret placé dans chaque formulaire et
   vérifié à la réception.
6. **La session n'est pas renouvelée à la connexion.** Ajouter
   `session_regenerate_id(true)` dans `Auth::connecter()` protège contre la
   « fixation de session ».
7. **Code en double :** `Epreuve::aplatir()` et `Equipe::aplatir()` font la
   même chose. On pourrait n'en garder qu'une.
8. **Ligne sans effet** dans `MembreEquipe::parUtilisateur` :
   `$ligne['equipe_id'] = $ligne['equipe_id'] ?? null;`.
9. **Méthodes prêtes mais pas encore branchées :** `Ville::modifier`,
   `Equipe::parVille`, `Epreuve::creer/changerStatut/supprimer`,
   `Sport::creer/supprimer`, toutes les méthodes d'écriture de `MembreEquipe`
   et `Participation`, `SupabaseClient::rpc`. Elles attendent les pages
   d'administration correspondantes.
10. **La clé `service_role`** donne tous les droits sur la base. Elle ne doit
    exister que sur le serveur, dans `env.local.php`, protégé par `.htaccess`.
11. **Arrêts brutaux par `die()`** dans `SupabaseClient` : simple, mais
    l'utilisateur voit un message technique. Une gestion d'erreur avec une page
    propre serait plus agréable.
12. **Le routeur ne protège pas les caractères spéciaux** des chemins
    (`preg_quote`). Sans conséquence avec les routes actuelles.

---

## 14. Glossaire

| Terme | Définition |
|---|---|
| **Apache** | Le logiciel serveur web qui reçoit les requêtes et exécute PHP. |
| **API REST** | Une façon d'exposer des données via des adresses web et les méthodes GET/POST/PATCH/DELETE. Supabase en fournit une pour chaque table. |
| **Autoloader** | Mécanisme qui charge automatiquement le fichier d'une classe quand on l'utilise. |
| **bcrypt** | L'algorithme de hachage de mots de passe utilisé par `password_hash`. |
| **Booléen** | Une valeur vrai/faux. |
| **Cast** | Conversion de type, ex. `(int) "3"` → `3`. |
| **Classe / méthode** | Une classe regroupe des fonctions (méthodes) apparentées. |
| **Clé étrangère** | Colonne qui contient l'`id` d'une ligne d'une autre table. |
| **Constructeur** | Méthode `__construct()` exécutée automatiquement à la création d'un objet. |
| **Contrôleur** | Dans MVC, la partie qui reçoit la requête et coordonne modèle et vue. |
| **Cookie** | Petit fichier que le navigateur renvoie au serveur à chaque requête ; sert à retrouver la session. |
| **CRUD** | Create, Read, Update, Delete : les quatre opérations de base sur des données. |
| **CSRF** | Attaque qui fait envoyer un formulaire à l'insu de l'utilisateur ; contrée par un jeton. |
| **CSS** | Le langage de mise en forme des pages. |
| **cURL** | L'outil de PHP pour envoyer des requêtes HTTP. |
| **Embed (PostgREST)** | Syntaxe `alias:cle_etrangere(colonnes)` pour récupérer les données d'une table liée dans la même requête. |
| **Expression régulière** | Un motif de recherche dans du texte, ex. `([^/]+)` = « une suite de caractères sans `/` ». |
| **Fonction anonyme** | Une fonction sans nom, écrite directement là où on l'utilise (`function ($x) { ... }`). |
| **Front Controller** | Un fichier unique (`index.php`) par lequel passent toutes les requêtes. |
| **FTP** | Protocole de transfert de fichiers vers un serveur. |
| **GitHub Actions** | Robot d'automatisation intégré à GitHub. |
| **Hash / empreinte** | Résultat d'une fonction à sens unique appliquée à un mot de passe. |
| **HTML** | Le langage de structure des pages web. |
| **HTTP** | Le protocole d'échange entre navigateur et serveur (requêtes et réponses). |
| **JSON** | Format texte pour échanger des données structurées (`{"id": 1, "nom": "X"}`). |
| **Méthode statique** | Méthode appelée sur la classe (`Ville::toutes()`) sans créer d'objet. |
| **Modèle** | Dans MVC, la partie qui lit et écrit les données. |
| **MVC** | Modèle – Vue – Contrôleur : façon d'organiser le code en trois rôles. |
| **Null** | « Pas de valeur ». |
| **PostgreSQL / PostgREST** | La base de données utilisée par Supabase, et le serveur qui l'expose en REST. |
| **PRG** | Post/Redirect/Get : après un formulaire réussi, rediriger vers une page GET pour éviter un double envoi au rafraîchissement. |
| **Redirection** | Réponse qui dit au navigateur « va plutôt à cette adresse » (`header('Location: ...')`). |
| **Requête préparée** | Requête SQL dans laquelle les valeurs sont injectées de façon sûre (`:email`). |
| **RLS** | Row Level Security : règles d'accès par ligne dans Supabase ; contournées par la clé `service_role`. |
| **Route** | Association entre une adresse et une méthode de contrôleur. |
| **Session** | Mémoire côté serveur associée à un visiteur, accessible via `$_SESSION`. |
| **Signature** | La première ligne d'une fonction : nom, paramètres, type de retour. |
| **Superglobale** | Variable PHP accessible partout : `$_POST`, `$_SESSION`, `$_SERVER`, `$GLOBALS`. |
| **Supabase** | Service en ligne qui héberge une base PostgreSQL et l'expose en API. |
| **Table d'association** | Table qui relie deux autres tables (plusieurs-à-plusieurs), ex. `membre_equipe`. |
| **Tableau (array)** | Une liste ou une fiche de valeurs. |
| **Tableau associatif** | Un tableau dont les cases ont un nom (`['nom' => 'X']`). |
| **Variable** | Une boîte nommée contenant une valeur, précédée de `$` en PHP. |
| **Vue (MVC)** | Le gabarit HTML qui affiche les données. |
| **Vue (SQL)** | Une requête enregistrée dans la base, lisible comme une table. |
| **XSS** | Injection de code via des données affichées ; contrée par `htmlspecialchars`. |
