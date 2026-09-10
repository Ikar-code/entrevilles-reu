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

Il y a quatre sortes de visiteurs. Le rôle est stocké dans la colonne `role`
de la table `utilisateur` :

| Visiteur | Rôle en base | Description |
|---|---|---|
| **Anonyme** | (pas de compte) | N'est pas connecté. Peut tout consulter mais rien modifier. |
| **Joueur** | `joueur` | A créé un compte via « Créer un compte ». Voit son profil et ses équipes. |
| **Manager de ville** | `manager` | Compte créé par le super admin, rattaché à **une** ville (`ville_id`). Gère les équipes et les membres de sa ville, et inscrit ses équipes aux épreuves (inscription « en attente » jusqu'à validation). |
| **Super administrateur** | `super_admin` | Gère tout : villes, sports, comptes (dont les managers), épreuves, participations et résultats, équipes. L'ancien rôle `admin` est accepté comme synonyme. |

Le premier super admin se crée avec le script `database/creer_admin.php`
(section 10.4). Les managers sont créés par le super admin dans « Utilisateurs ».

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
| `/gestion` | « Ma ville » : mes équipes, inscriptions en attente, prochaines épreuves | Manager |
| `/admin/equipes`, `/admin/equipes/3` | Équipes, membres et inscriptions aux épreuves (le manager ne voit que sa ville) | Manager et super admin |
| `/admin` | Tableau de bord d'administration | Super admin |
| `/admin/villes`, `/admin/sports` | Créer, renommer, supprimer villes et sports | Super admin |
| `/admin/utilisateurs` | Créer et modifier les comptes, dont les managers de ville | Super admin |
| `/admin/epreuves`, `/admin/epreuves/5/participations` | Créer les épreuves, changer leur statut, inscrire des équipes, valider, saisir les résultats | Super admin |

Le détail de toutes ces adresses est en section 6.

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
├── README.md                        Cette documentation
└── htdocs/                          Racine du site (le seul dossier visible par le serveur)
    ├── index.php                    LA porte d'entrée : toutes les requêtes passent ici
    ├── .htaccess                    Règle Apache : « tout envoyer vers index.php »
    ├── .env.example                 Modèle de fichier de configuration
    ├── assets/css/style.css         La mise en forme (couleurs, polices, thème clair/sombre)
    ├── assets/js/theme.js           Le bouton clair / sombre (seul script JavaScript)
    ├── config/
    │   ├── .htaccess                Interdit de lire ce dossier depuis le navigateur
    │   └── config.php               Charge les clés Supabase, démarre la session
    ├── database/
    │   ├── .htaccess                Interdit de lire ce dossier depuis le navigateur
    │   ├── creer_admin.php          Script (ligne de commande) pour créer le premier super admin
    │   ├── migration_mot_de_passe.sql  Ajoute la colonne mot de passe
    │   ├── migration_roles.sql      Autorise les rôles manager / super_admin
    │   └── vue_classement_general.sql  Calcule le classement par ville
    └── app/
        ├── .htaccess                Interdit de lire ce dossier depuis le navigateur
        ├── routes.php               La liste « telle adresse → tel contrôleur »
        ├── Core/                    Le noyau technique
        │   ├── autoload.php         Charge les classes automatiquement
        │   ├── Router.php           Aiguille chaque adresse vers la bonne fonction (+ contrôle CSRF)
        │   ├── Controller.php       Classe mère des contrôleurs (afficher, rediriger, lire un champ…)
        │   ├── Auth.php             Connexion, déconnexion, rôles et droits
        │   ├── Csrf.php             Jeton de sécurité des formulaires
        │   ├── Flash.php            Messages « Ville créée. » affichés après une redirection
        │   ├── Format.php           Mise en forme des dates pour les vues
        │   ├── SupabaseClient.php   Parle à la base de données
        │   └── SupabaseException.php  Erreur levée quand la base refuse une requête
        ├── Models/                  Un fichier par table de la base
        │   ├── Ville.php  Sport.php  Utilisateur.php  Equipe.php
        │   └── MembreEquipe.php  Epreuve.php  Participation.php
        ├── Controllers/             Un fichier par « zone » du site
        │   ├── AccueilController.php   AuthController.php   ProfilController.php
        │   ├── EpreuveController.php   EquipeController.php  ClassementController.php
        │   ├── AdminController.php            (super admin : tableau de bord, villes, sports)
        │   ├── AdminUtilisateurController.php (super admin : comptes et managers)
        │   ├── AdminEpreuveController.php     (super admin : épreuves, participations, résultats)
        │   ├── AdminEquipeController.php      (super admin + manager : équipes et membres)
        │   └── ManagerController.php          (manager : page « Ma ville »)
        └── Views/                   Les gabarits HTML
            ├── partials/ layout.php  navbar.php  footer.php  menu_admin.php
            ├── erreur_403.php  erreur_404.php  erreur_500.php
            ├── accueil/index.php   auth/login.php   auth/inscription.php
            ├── profil/index.php    epreuves/planning.php  epreuves/detail.php
            ├── equipes/liste.php   equipes/detail.php    classement/index.php
            ├── gestion/index.php                        (« Ma ville » du manager)
            └── admin/
                ├── tableau_de_bord.php
                ├── villes/index.php  villes/modifier.php
                ├── sports/index.php  sports/modifier.php
                ├── utilisateurs/index.php  utilisateurs/modifier.php
                ├── epreuves/index.php  epreuves/modifier.php  epreuves/participations.php
                └── equipes/index.php  equipes/membres.php
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
5. Si c'est bon : `Auth::connecter()` range la fiche dans `$_SESSION`, puis on **redirige** (réponse `302`) vers la page d'accueil du rôle : `/admin` pour un super admin, `/gestion` pour un manager, `/` pour un joueur. Le navigateur redemande cette page tout seul.
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

try {
    Router::traiter();
} catch (Throwable $e) {
    // Erreur non prévue (base de données injoignable, bug…) : page d'erreur propre
    http_response_code(500);
    $messageErreur = $e->getMessage();
    require __DIR__ . '/app/Views/erreur_500.php';
}
```

| Ligne | Ce qu'elle fait |
|---|---|
| `<?php` | Annonce « ici commence du code PHP ». |
| `require_once .../config/config.php` | Charge la configuration : clés Supabase + démarrage de la session. |
| `require_once .../app/Core/autoload.php` | Active le chargement automatique des classes (voir 5.1). Sans lui, PHP ne saurait pas où trouver `Router`. |
| `require_once .../app/routes.php` | Déclare toutes les adresses du site. |
| `try { Router::traiter(); }` | Lance l'aiguillage : trouve la bonne fonction et l'exécute. `try` = « essaie, et si une erreur (**exception**) survient… ». |
| `catch (Throwable $e) { ... }` | « …attrape-la ici » : au lieu d'une page blanche, on renvoie le code `500` (erreur serveur) et la page `erreur_500.php` avec le message. C'est là qu'arrivent les `SupabaseException` non traitées (voir 5.8). |

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

if (!isset($env) || !is_array($env)) {
    $env = [];
}

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
            $env[trim($cle)] = trim($valeur);
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
2. Sinon, si `.env` existe → on lit chaque ligne. `trim()` enlève les espaces au début et à la fin. Une ligne qui commence par `#` est un commentaire : `continue` = « passe à la ligne suivante ». Une ligne contenant `=` est découpée puis enregistrée **deux fois** : dans le tableau `$env` (lu par `SupabaseClient`) et avec `putenv()` (variable d'environnement du système, lisible par `getenv()`). Le `$env = []` du début garantit que le tableau existe même si aucun fichier n'est trouvé.
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
| `if ($methode === 'POST' && !Csrf::verifier())` | Pour tout envoi de formulaire, on vérifie le **jeton CSRF** (voir 5.6). S'il manque ou est faux : page 403 et on s'arrête. Aucun contrôleur n'a donc à y penser. |
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

    /** Redirige le navigateur vers une autre adresse et arrête le script (schéma Post/Redirect/Get) */
    protected function rediriger(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /** Affiche la page 404 et arrête le script */
    protected function introuvable(): void
    {
        http_response_code(404);
        require __DIR__ . '/../Views/erreur_404.php';
        exit;
    }

    /** Lit un champ texte du formulaire POST (chaîne vide s'il est absent), sans espaces autour */
    protected function champ(string $nom): string
    {
        return trim((string) ($_POST[$nom] ?? ''));
    }

    /** Lit un champ numérique du formulaire POST ; null si vide, absent ou non numérique */
    protected function champEntier(string $nom): ?int
    {
        $valeur = $this->champ($nom);
        return ($valeur === '' || !is_numeric($valeur)) ? null : (int) $valeur;
    }
}
```

Tous les contrôleurs héritent de cette classe (`extends Controller`) et
disposent donc de ces cinq méthodes :

| Méthode | À quoi ça sert |
|---|---|
| `afficher($vue, $donnees)` | Afficher une page complète (détail ci-dessous). |
| `rediriger($url)` | Envoyer le navigateur ailleurs après un formulaire réussi. `exit` arrête le script : rien ne s'exécute après. |
| `introuvable()` | Afficher la page 404 quand l'`id` demandé n'existe pas. |
| `champ($nom)` | Lire un champ de formulaire proprement : jamais d'erreur si absent, espaces retirés. |
| `champEntier($nom)` | Idem pour un nombre (un `id` de menu déroulant, un score) : `null` si vide ou non numérique. |

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

### 5.4 `Auth.php` — connexion, rôles et droits d'accès

```php
class Auth
{
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_MANAGER     = 'manager';
    public const ROLE_JOUEUR      = 'joueur';

    /** Rôles proposés dans les formulaires d'administration : valeur => libellé */
    public const ROLES = [
        self::ROLE_JOUEUR      => 'Joueur',
        self::ROLE_MANAGER     => 'Manager de ville',
        self::ROLE_SUPER_ADMIN => 'Super administrateur',
    ];

    public static function connecter(array $utilisateur): void
    {
        // Nouvel identifiant de session à chaque connexion (protection contre la "fixation de session")
        session_regenerate_id(true);

        // On ne stocke JAMAIS le mot de passe (même hashé) en session
        $_SESSION['utilisateur'] = [
            'id'         => (int) $utilisateur['id'],
            'nom_compte' => $utilisateur['nom_compte'],
            'email'      => $utilisateur['email'],
            'role'       => $utilisateur['role'],
            'ville_id'   => isset($utilisateur['ville_id']) ? (int) $utilisateur['ville_id'] : null,
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

    public static function utilisateur(): ?array
    {
        return $_SESSION['utilisateur'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['utilisateur']['role'] ?? null;
    }

    public static function estSuperAdmin(): bool
    {
        return in_array(self::role(), [self::ROLE_SUPER_ADMIN, 'admin'], true);
    }

    public static function estManager(): bool
    {
        return self::role() === self::ROLE_MANAGER;
    }

    /** A accès à l'espace d'administration (super admin OU manager) */
    public static function estAdmin(): bool
    {
        return self::estSuperAdmin() || self::estManager();
    }

    /** Ville gérée par le manager connecté ; null pour un super admin (toutes) ou un joueur */
    public static function villeGeree(): ?int
    {
        if (!self::estManager()) {
            return null;
        }
        return $_SESSION['utilisateur']['ville_id'] ?? null;
    }

    /** Le compte connecté peut-il agir sur les équipes de cette ville ? */
    public static function peutGererVille(?int $villeId): bool
    {
        if (self::estSuperAdmin()) {
            return true;
        }
        if (self::estManager()) {
            return $villeId !== null && self::villeGeree() === $villeId;
        }
        return false;
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
            self::refuser('Cette page est réservée aux administrateurs et aux managers de ville.');
        }
    }

    public static function exigerSuperAdmin(): void
    {
        self::exigerConnexion();
        if (!self::estSuperAdmin()) {
            self::refuser('Cette page est réservée au super administrateur.');
        }
    }

    public static function exigerManager(): void
    {
        self::exigerConnexion();
        if (!self::estManager()) {
            self::refuser('Cette page est réservée aux managers de ville.');
        }
    }

    public static function exigerGestionVille(?int $villeId): void
    {
        self::exigerConnexion();
        if (!self::peutGererVille($villeId)) {
            self::refuser('Tu ne gères pas la ville de cette équipe.');
        }
    }

    /** Affiche la page 403 et arrête le script */
    private static function refuser(string $motif): void
    {
        http_response_code(403);
        $motifRefus = $motif;
        require __DIR__ . '/../Views/erreur_403.php';
        exit;
    }
}
```

Toute la « mémoire » de la connexion tient dans `$_SESSION['utilisateur']`.

#### Les constantes de rôle

`public const ROLE_SUPER_ADMIN = 'super_admin';` : une **constante de classe**
est une valeur fixe à laquelle on donne un nom. Écrire `Auth::ROLE_MANAGER`
plutôt que `'manager'` évite les fautes de frappe : si on se trompe dans le nom
de la constante, PHP signale une erreur, alors qu'une chaîne mal tapée passe
inaperçue. `ROLES` associe chaque valeur en base à son libellé affiché dans les
menus déroulants.

#### `connecter(array $utilisateur): void`

- **À quoi ça sert :** marquer la personne comme connectée.
- **Ce qu'elle reçoit :** la fiche complète de l'utilisateur, telle que lue en base (avec son mot de passe haché).
- **Comment ça marche :**
  1. `session_regenerate_id(true)` : la session reçoit un **nouveau numéro** et l'ancien est détruit. Cela empêche l'attaque dite de « fixation de session », où un pirate impose à sa victime un numéro de session qu'il connaît.
  2. Recopie en session **seulement** cinq informations : `id`, `nom_compte`, `email`, `role`, `ville_id`, converties en entiers quand il le faut (`(int)`). Le mot de passe (même haché) n'est jamais mis en session.

#### `deconnecter(): void`

- **Comment ça marche :** `unset(...)` supprime la case `utilisateur` de la session, puis `session_destroy()` efface complètement la session côté serveur.

#### `estConnecte(): bool`

- **Ce qu'elle renvoie :** `true` si la case `utilisateur` existe en session, `false` sinon. `isset()` veut dire « existe et n'est pas null ».

#### `utilisateur(): ?array` et `role(): ?string`

- **Ce qu'elles renvoient :** la fiche en session (ou `null` si personne n'est connecté, grâce à `??`), et le rôle seul.

#### `estSuperAdmin()`, `estManager()`, `estAdmin()` : bool

| Méthode | Vrai quand… |
|---|---|
| `estSuperAdmin()` | le rôle est `super_admin` **ou** l'ancien `admin` (`in_array` = « est dans cette liste » ; le `true` final exige une comparaison stricte). |
| `estManager()` | le rôle est exactement `manager`. |
| `estAdmin()` | l'un des deux : c'est le droit d'entrer dans l'espace d'administration (les pages Équipes sont partagées). |

#### `villeGeree(): ?int` et `peutGererVille(?int $villeId): bool`

- `villeGeree()` : l'`id` de la ville du manager connecté, `null` sinon.
- `peutGererVille($villeId)` : la règle centrale des droits sur une équipe. Un super admin peut tout ; un manager seulement si `$villeId` est **sa** ville ; un joueur jamais.

#### `exigerConnexion(): void`

- **À quoi ça sert :** un « videur » à placer au début d'une page réservée aux connectés.
- **Comment ça marche :** si non connecté, `header('Location: /login')` envoie au navigateur l'ordre d'aller sur `/login` (redirection), puis `exit` **arrête le programme**. Sans `exit`, PHP continuerait à produire la page protégée après l'entête de redirection.

#### `exigerAdmin()`, `exigerSuperAdmin()`, `exigerManager()`, `exigerGestionVille($villeId)`

- **À quoi ça sert :** quatre videurs, un par niveau de droit. Chacun exige d'abord d'être connecté, puis vérifie le droit correspondant et, sinon, appelle `refuser()`.
- `exigerGestionVille($villeId)` est utilisé par les pages d'équipe : on lit d'abord l'équipe, puis on vérifie que le compte connecté a le droit d'agir sur sa ville.

#### `refuser(string $motif): void` (privée)

- **Comment ça marche :** code HTTP `403` (interdit), affichage de la page `erreur_403.php` avec le motif, puis `exit`. Remplace l'ancien `die()` brut par une vraie page.

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
            // Source 1 : $env défini par config/env.local.php ; source 2 : variables d'environnement (.env)
            $url = ($GLOBALS['env']['SUPABASE_URL'] ?? '') ?: (getenv('SUPABASE_URL') ?: '');
            $key = ($GLOBALS['env']['SUPABASE_KEY'] ?? '') ?: (getenv('SUPABASE_KEY') ?: '');

            self::$url = rtrim((string) $url, '/');
            self::$key = (string) $key;

            if (self::$url === '' || self::$key === '') {
                throw new SupabaseException('Variables SUPABASE_URL / SUPABASE_KEY manquantes (voir config/config.php).');
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
- **Comment ça marche :** si `$url` est encore `null`, on lit `$GLOBALS['env']['SUPABASE_URL']` (défini par `env.local.php` ou `.env`) et, s'il est vide, on retombe sur `getenv('SUPABASE_URL')` (variable d'environnement). L'opérateur `?:` enchaîne ces sources : « la première valeur non vide ». `rtrim(..., '/')` enlève un `/` final éventuel. Si l'une des deux valeurs manque, on **lève une exception** `SupabaseException` (voir 5.8) : `index.php` l'attrape et affiche la page d'erreur.

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
            throw new SupabaseException('Connexion à la base de données impossible : ' . $erreur);
        }

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 400) {
            throw new SupabaseException(self::messageErreur($reponse, $code), $code);
        }

        if ($reponse === '' || $reponse === null) {
            return [];
        }

        $donnees = json_decode($reponse, true);
        return is_array($donnees) ? $donnees : [];
    }

    /** Extrait un message lisible de la réponse d'erreur JSON de PostgREST */
    private static function messageErreur(string $reponse, int $code): string
    {
        $json = json_decode($reponse, true);
        $message = is_array($json) ? ($json['message'] ?? $json['details'] ?? $reponse) : $reponse;
        return 'Erreur base de données (HTTP ' . $code . ') : ' . $message;
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
| `if ($reponse === false) { ... throw ... }` | Échec réseau (pas d'internet, adresse fausse…) : on **lève une exception** (voir 5.8) avec le message de cURL. |
| `$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);` | Récupère le code de réponse (200, 404, 401, 409…). |
| `curl_close($ch);` | Ferme le dossier. |
| `if ($code >= 400) throw ...` | Les codes 400 et plus sont des erreurs (clé invalide, table inexistante, contrainte violée…) : on lève une `SupabaseException` portant le message de Supabase et le code HTTP. `messageErreur()` extrait le texte lisible de la réponse JSON. |
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

### 5.6 `Csrf.php` — le jeton de sécurité des formulaires

```php
class Csrf
{
    public static function jeton(): string
    {
        if (empty($_SESSION['csrf_jeton'])) {
            $_SESSION['csrf_jeton'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_jeton'];
    }

    public static function champ(): string
    {
        return '<input type="hidden" name="csrf_jeton" value="' . htmlspecialchars(self::jeton()) . '">';
    }

    public static function verifier(): bool
    {
        $recu = $_POST['csrf_jeton'] ?? '';
        return is_string($recu) && $recu !== '' && hash_equals(self::jeton(), $recu);
    }
}
```

**Le problème résolu (attaque CSRF) :** imagine qu'un admin soit connecté au
site, puis visite une page piégée ailleurs sur le web. Cette page pourrait
contenir un formulaire caché qui envoie `POST /admin/villes/4/supprimer` à
notre site ; le navigateur joindrait le cookie de session, et la ville serait
supprimée à l'insu de l'admin.

**La parade :** chaque session possède un **jeton** secret (une longue chaîne
aléatoire). Tous nos formulaires l'incluent dans un champ caché. La page piégée
ne peut pas connaître ce jeton, donc sa requête est refusée.

| Méthode | Rôle |
|---|---|
| `jeton()` | Renvoie le jeton de la session, en le créant au premier appel. `random_bytes(32)` = 32 octets aléatoires sûrs ; `bin2hex` les écrit en 64 caractères hexadécimaux. |
| `champ()` | Le champ caché à insérer dans chaque formulaire : `<?= Csrf::champ() ?>`. |
| `verifier()` | Compare le jeton reçu en POST avec celui de la session. `hash_equals` compare en temps constant (un attaquant ne peut pas deviner le jeton lettre par lettre en mesurant le temps de réponse). |

La vérification est faite **une seule fois pour tout le site**, dans
`Router::traiter()` (voir 5.2).

### 5.7 `Flash.php` — les messages d'une page à l'autre

```php
class Flash
{
    public static function succes(string $message): void { self::ajouter('succes', $message); }
    public static function erreur(string $message): void { self::ajouter('erreur', $message); }

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
```

**Le problème résolu :** après un formulaire réussi, on **redirige** (schéma
PRG). Mais la page suivante est une nouvelle requête : comment lui dire
« Ville créée. » ? En laissant le message en session.

- `succes()` / `erreur()` : rangent un message (avec son type) dans la liste `$_SESSION['flash']`.
- `recuperer()` : renvoie la liste **et la vide** aussitôt. Le message n'apparaît donc qu'une fois. Appelée par `layout.php`, qui affiche chaque message dans un bandeau coloré (`.flash-succes` vert, `.flash-erreur` rouge).

### 5.8 `SupabaseException.php` — l'erreur de base de données

```php
class SupabaseException extends RuntimeException
{
    private int $codeHttp;

    public function __construct(string $message, int $codeHttp = 0)
    {
        parent::__construct($message);
        $this->codeHttp = $codeHttp;
    }

    public function codeHttp(): int { return $this->codeHttp; }

    /** true si la base a refusé à cause d'une contrainte (clé étrangère, unicité…) */
    public function estConflit(): bool { return $this->codeHttp === 409; }
}
```

**Ce qu'est une exception :** un signal d'erreur que l'on **lève** (`throw`) à
l'endroit du problème, et que l'on **attrape** (`try { ... } catch (...) { ... }`)
là où l'on sait quoi faire. Entre les deux, le programme remonte la pile des
appels sans exécuter la suite. Avant, `SupabaseClient` faisait `die()` : page
blanche avec un message brut. Maintenant :

- un contrôleur d'administration peut attraper l'exception et afficher « Suppression refusée par la base de données » via `Flash::erreur()` ;
- sinon, l'exception remonte jusqu'à `index.php` qui affiche `erreur_500.php`.

`extends RuntimeException` : notre classe hérite d'une exception standard de
PHP. `parent::__construct($message)` appelle le constructeur du parent pour
enregistrer le message. `codeHttp` garde le code renvoyé par Supabase (`409` =
conflit, typiquement une clé étrangère ou un email en doublon).

### 5.9 `Format.php` — mettre en forme les dates

```php
class Format
{
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
```

- `dateHeure()` : transforme la date brute de la base (`2026-09-20T15:00:00`) en « 20/09/2026 15:00 », ou renvoie un tiret si elle est vide. Le `try/catch` évite qu'une date mal formée ne fasse planter la page.
- `dateHeureLocal()` : format exigé par un champ `<input type="datetime-local">` (`2026-09-20T15:00`), utilisé par le formulaire de modification d'une épreuve.
- `asset(string $chemin)` : renvoie l'adresse d'un fichier statique **avec sa date de modification** en paramètre, par exemple `/assets/css/style.css?v=1757430000`.

```php
    public static function asset(string $chemin): string
    {
        $fichier = __DIR__ . '/../../' . ltrim($chemin, '/');
        $version = is_file($fichier) ? (string) filemtime($fichier) : '1';
        return $chemin . '?v=' . $version;
    }
```

**Le problème résolu par `asset()` (« cache busting ») :** pour aller plus vite,
un navigateur garde en mémoire (en **cache**) les fichiers CSS et JS déjà
téléchargés, et l'hébergeur InfinityFree lui demande de les garder **30 jours**.
Après une mise à jour du site, un visiteur revoyait donc l'ancienne feuille de
style pendant des semaines : c'est exactement ce qui faisait croire que le
bouton de thème « ne marchait pas ». `filemtime()` lit la date de dernière
modification du fichier ; comme elle change à chaque mise en ligne, l'adresse
change (`?v=…`), et le navigateur considère qu'il s'agit d'un nouveau fichier
à télécharger. `ltrim($chemin, '/')` enlève le `/` de début pour construire le
chemin sur le disque à partir de `htdocs/`.

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

// Espace manager de ville
Router::get('/gestion', ManagerController::class, 'index');

// Administration (super admin) — tableau de bord, villes, sports
Router::get('/admin', AdminController::class, 'tableauDeBord');
Router::get('/admin/villes', AdminController::class, 'villes');
Router::post('/admin/villes', AdminController::class, 'creerVille');
Router::get('/admin/villes/{id}/modifier', AdminController::class, 'modifierVille');
Router::post('/admin/villes/{id}/modifier', AdminController::class, 'enregistrerVille');
Router::post('/admin/villes/{id}/supprimer', AdminController::class, 'supprimerVille');
// … même schéma pour /admin/sports, /admin/utilisateurs, /admin/epreuves, /admin/equipes
```

Lecture d'une ligne : `Router::get('/equipes/{id}', EquipeController::class, 'detail');`
= « quand quelqu'un demande en `GET` une adresse de la forme `/equipes/quelque-chose`,
appelle la méthode `detail` de la classe `EquipeController` en lui donnant ce
quelque-chose ».

### 6.1 Routes publiques et compte

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
| GET | `/gestion` | `ManagerController` | `index` | Manager |

### 6.2 Routes d'administration (super admin)

| Méthode | Adresse | Classe | Méthode appelée | Action |
|---|---|---|---|---|
| GET | `/admin` | `AdminController` | `tableauDeBord` | Tableau de bord |
| GET | `/admin/villes` | `AdminController` | `villes` | Liste + formulaire de création |
| POST | `/admin/villes` | `AdminController` | `creerVille` | Créer |
| GET | `/admin/villes/{id}/modifier` | `AdminController` | `modifierVille` | Formulaire de renommage |
| POST | `/admin/villes/{id}/modifier` | `AdminController` | `enregistrerVille` | Enregistrer le renommage |
| POST | `/admin/villes/{id}/supprimer` | `AdminController` | `supprimerVille` | Supprimer |
| GET | `/admin/sports` | `AdminController` | `sports` | Liste + création |
| POST | `/admin/sports` | `AdminController` | `creerSport` | Créer |
| GET | `/admin/sports/{id}/modifier` | `AdminController` | `modifierSport` | Formulaire |
| POST | `/admin/sports/{id}/modifier` | `AdminController` | `enregistrerSport` | Enregistrer |
| POST | `/admin/sports/{id}/supprimer` | `AdminController` | `supprimerSport` | Supprimer |
| GET | `/admin/utilisateurs` | `AdminUtilisateurController` | `liste` | Liste + création (joueur, manager, super admin) |
| POST | `/admin/utilisateurs` | `AdminUtilisateurController` | `creer` | Créer un compte |
| GET | `/admin/utilisateurs/{id}/modifier` | `AdminUtilisateurController` | `modifier` | Formulaire |
| POST | `/admin/utilisateurs/{id}/modifier` | `AdminUtilisateurController` | `enregistrer` | Enregistrer (rôle, ville, mot de passe…) |
| POST | `/admin/utilisateurs/{id}/supprimer` | `AdminUtilisateurController` | `supprimer` | Supprimer |
| GET | `/admin/epreuves` | `AdminEpreuveController` | `liste` | Liste + création |
| POST | `/admin/epreuves` | `AdminEpreuveController` | `creer` | Créer |
| GET | `/admin/epreuves/{id}/modifier` | `AdminEpreuveController` | `modifier` | Formulaire |
| POST | `/admin/epreuves/{id}/modifier` | `AdminEpreuveController` | `enregistrer` | Enregistrer |
| POST | `/admin/epreuves/{id}/statut` | `AdminEpreuveController` | `changerStatut` | À venir → en cours → terminée |
| POST | `/admin/epreuves/{id}/supprimer` | `AdminEpreuveController` | `supprimer` | Supprimer (et ses participations) |
| GET | `/admin/epreuves/{id}/participations` | `AdminEpreuveController` | `participations` | Équipes inscrites, résultats |
| POST | `/admin/epreuves/{id}/participations` | `AdminEpreuveController` | `inscrire` | Inscrire une équipe |
| POST | `/admin/epreuves/{id}/participations/resultat` | `AdminEpreuveController` | `resultat` | Saisir score et rang |
| POST | `/admin/epreuves/{id}/participations/valider` | `AdminEpreuveController` | `validerParticipation` | Valider une inscription en attente |
| POST | `/admin/epreuves/{id}/participations/retirer` | `AdminEpreuveController` | `retirerParticipation` | Retirer une équipe |

### 6.3 Routes des équipes (super admin **et** manager, chacun sur sa ville)

| Méthode | Adresse | Méthode de `AdminEquipeController` | Action |
|---|---|---|---|
| GET | `/admin/equipes` | `liste` | Liste + création (le manager ne voit que sa ville) |
| POST | `/admin/equipes` | `creer` | Créer (ville imposée pour un manager) |
| GET | `/admin/equipes/{id}` | `membres` | Membres, ajout, inscriptions aux épreuves |
| POST | `/admin/equipes/{id}/supprimer` | `supprimer` | Supprimer (membres et inscriptions compris) |
| POST | `/admin/equipes/{id}/membres` | `ajouterMembre` | Ajouter un compte à l'équipe |
| POST | `/admin/equipes/{id}/membres/modifier` | `modifierMembre` | Rôle interne, pénalité, récompense |
| POST | `/admin/equipes/{id}/membres/retirer` | `retirerMembre` | Retirer un membre |
| POST | `/admin/equipes/{id}/inscriptions` | `inscrireEpreuve` | Inscrire l'équipe à une épreuve à venir |
| POST | `/admin/equipes/{id}/inscriptions/retirer` | `retirerInscription` | Annuler une inscription |

**Pourquoi deux routes `/login` ?** La version `GET` **affiche** le formulaire ;
la version `POST` **traite** ce que l'utilisateur a saisi. C'est un schéma
classique : afficher en GET, traiter en POST, puis rediriger (voir « PRG » dans
le glossaire). Toutes les routes `POST` passent par le contrôle CSRF du routeur.

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
| `modifier($id, $nom)` | Renommer | l'identifiant, le nouveau nom | rien | Modifie la ligne `id = $id`. Utilisée par le formulaire « Modifier la ville ». |
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

    public static function modifier(int $id, string $nom, ?string $description): void
    {
        SupabaseClient::update('sport', ['id' => 'eq.' . $id], ['nom' => $nom, 'description' => $description]);
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
| `modifier($id, $nom, $description)` | Renommer / changer la description | id, nom, description | rien | Met à jour les deux colonnes. |
| `supprimer($id)` | Supprimer | l'identifiant | rien | Refusé par le contrôleur si des équipes ou des épreuves utilisent ce sport. |

### 7.3 `Utilisateur.php` — table `utilisateur` (colonnes : `id`, `email`, `nom_compte`, `role`, `ville_id`, `mot_de_passe`)

```php
class Utilisateur
{
    /** Colonnes renvoyées aux pages d'administration : jamais le hash du mot de passe */
    private const COLONNES_PUBLIQUES = 'id,email,nom_compte,role,ville_id,ville:ville_id(nom)';

    private static function aplatir(array $ligne): array
    {
        $ligne['ville_nom'] = $ligne['ville']['nom'] ?? null;
        unset($ligne['ville']);
        return $ligne;
    }

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

    public static function tous(): array
    {
        $lignes = SupabaseClient::select('utilisateur', self::COLONNES_PUBLIQUES, [], 'nom_compte.asc');
        return array_map([self::class, 'aplatir'], $lignes);
    }

    public static function parRole(string $role): array
    {
        $lignes = SupabaseClient::select('utilisateur', self::COLONNES_PUBLIQUES, ['role' => 'eq.' . $role], 'nom_compte.asc');
        return array_map([self::class, 'aplatir'], $lignes);
    }

    public static function parVille(int $villeId, bool $inclureSansVille = false): array
    {
        $filtres = $inclureSansVille
            ? ['or' => '(ville_id.eq.' . $villeId . ',ville_id.is.null)']
            : ['ville_id' => 'eq.' . $villeId];

        $lignes = SupabaseClient::select('utilisateur', self::COLONNES_PUBLIQUES, $filtres, 'nom_compte.asc');
        return array_map([self::class, 'aplatir'], $lignes);
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

    public static function modifier(int $id, array $donnees): void
    {
        SupabaseClient::update('utilisateur', ['id' => 'eq.' . $id], $donnees);
    }

    public static function changerMotDePasse(int $id, string $motDePasseClair): void
    {
        SupabaseClient::update('utilisateur', ['id' => 'eq.' . $id], [
            'mot_de_passe' => password_hash($motDePasseClair, PASSWORD_DEFAULT),
        ]);
    }

    public static function supprimer(int $id): void
    {
        SupabaseClient::delete('utilisateur', ['id' => 'eq.' . $id]);
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

#### La constante `COLONNES_PUBLIQUES`

Les pages d'administration listent les comptes. Elles n'ont **jamais** besoin
du hash du mot de passe : on demande donc explicitement les colonnes utiles
(`id,email,nom_compte,role,ville_id`) plus l'embed du nom de ville, au lieu de
`*`. Moins on fait circuler de données sensibles, mieux c'est.

#### `trouverParEmail(string $email): ?array`

- **À quoi ça sert :** retrouver un compte à partir de son email (unique dans la base).
- **Renvoie :** la fiche complète (y compris le mot de passe haché, nécessaire pour vérifier une connexion) ou `null`.

#### `trouver(int $id): ?array`

- Même chose par identifiant.

#### `tous()`, `parRole(string $role)`, `parVille(int $villeId, bool $inclureSansVille = false)` : array

| Méthode | À quoi ça sert | Comment |
|---|---|---|
| `tous()` | La liste des comptes pour la page Utilisateurs. | Colonnes publiques + `ville_nom`, tri par nom de compte. |
| `parRole($role)` | Par exemple tous les managers, affichés sur le tableau de bord. | Filtre `role = ...`. |
| `parVille($villeId, $inclureSansVille)` | Les comptes d'une ville : sert au manager pour recruter, et au super admin pour refuser la suppression d'une ville encore utilisée. | Avec `$inclureSansVille = true`, le filtre devient `or=(ville_id.eq.5,ville_id.is.null)` : « ville 5 **ou** pas de ville ». Un joueur inscrit sans choisir de ville reste recrutable. |

#### `modifier(int $id, array $donnees)`, `changerMotDePasse(int $id, string $clair)`, `supprimer(int $id)` : void

- `modifier()` reçoit une fiche des colonnes à changer (`nom_compte`, `email`, `role`, `ville_id`) : une seule méthode pour tous les cas.
- `changerMotDePasse()` hache le nouveau mot de passe avant de l'enregistrer : le clair ne touche jamais la base.
- `supprimer()` efface le compte. Le contrôleur retire d'abord la personne de ses équipes (`MembreEquipe::retirerUtilisateurPartout`).

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
    private const EMBED = '*,ville:ville_id(nom),sport:sport_id(nom)';

    private static function aplatir(array $ligne): array
    {
        $ligne['ville_nom'] = $ligne['ville']['nom'] ?? null;
        $ligne['sport_nom'] = $ligne['sport']['nom'] ?? null;
        unset($ligne['ville'], $ligne['sport']);
        return $ligne;
    }

    public static function toutes(): array
    {
        $lignes = SupabaseClient::select('equipe', self::EMBED, [], 'nom.asc');
        return array_map([self::class, 'aplatir'], $lignes);
    }

    public static function trouver(int $id): ?array
    {
        $resultats = SupabaseClient::select('equipe', self::EMBED, ['id' => 'eq.' . $id]);
        return isset($resultats[0]) ? self::aplatir($resultats[0]) : null;
    }

    public static function parVille(int $villeId): array
    {
        $lignes = SupabaseClient::select('equipe', self::EMBED, ['ville_id' => 'eq.' . $villeId], 'nom.asc');
        return array_map([self::class, 'aplatir'], $lignes);
    }

    public static function parSport(int $sportId): array
    {
        $lignes = SupabaseClient::select('equipe', self::EMBED, ['sport_id' => 'eq.' . $sportId], 'nom.asc');
        return array_map([self::class, 'aplatir'], $lignes);
    }

    public static function creer(string $nom, int $villeId, int $sportId): int
    {
        $ligne = SupabaseClient::insert('equipe', [
            'nom' => $nom, 'ville_id' => $villeId, 'sport_id' => $sportId,
        ]);
        return (int) $ligne['id'];
    }

    public static function modifier(int $id, string $nom, int $villeId, int $sportId): void
    {
        SupabaseClient::update('equipe', ['id' => 'eq.' . $id], [
            'nom' => $nom, 'ville_id' => $villeId, 'sport_id' => $sportId,
        ]);
    }

    public static function supprimer(int $id): void
    {
        SupabaseClient::delete('equipe', ['id' => 'eq.' . $id]);
    }
}
```

La constante `EMBED` évite de répéter quatre fois la même chaîne d'embed.

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

#### `parVille(int $villeId): array` et `parSport(int $sportId): array`

- **À quoi ça sert :** les équipes d'une ville (l'espace du manager n'affiche que celles-là) ou d'un sport (pour refuser la suppression d'un sport encore utilisé). Même embed et même aplatissement que `toutes()`.

#### `creer(...)`, `modifier(...)`, `supprimer(int $id)`

- Création, modification des trois colonnes et suppression, sur le même modèle que `Ville`. Avant `supprimer()`, le contrôleur vide le roster et les inscriptions de l'équipe.

### 7.5 `Epreuve.php` — table `epreuve` (colonnes : `id`, `sport_id`, `ville_id`, `date_heure`, `statut`)

Une épreuve est un match ou une compétition d'un sport, dans une ville, à une
date. Son `statut` vaut `a_venir`, `en_cours` ou `terminee`.

```php
class Epreuve
{
    /** Statuts possibles : valeur en base => libellé affiché */
    public const STATUTS = [
        'a_venir'  => 'À venir',
        'en_cours' => 'En cours',
        'terminee' => 'Terminée',
    ];

    private const EMBED = '*,sport:sport_id(nom),ville:ville_id(nom)';

    private static function aplatir(array $ligne): array
    {
        $ligne['sport_nom'] = $ligne['sport']['nom'] ?? null;
        $ligne['ville_nom'] = $ligne['ville']['nom'] ?? null;
        unset($ligne['sport'], $ligne['ville']);
        return $ligne;
    }

    public static function toutes(): array
    {
        $lignes = SupabaseClient::select('epreuve', self::EMBED, [], 'date_heure.desc');
        return array_map([self::class, 'aplatir'], $lignes);
    }

    public static function trouver(int $id): ?array
    {
        $resultats = SupabaseClient::select('epreuve', self::EMBED, ['id' => 'eq.' . $id]);
        return isset($resultats[0]) ? self::aplatir($resultats[0]) : null;
    }

    public static function aVenir(): array
    {
        return self::parStatut('a_venir', 'date_heure.asc');
    }

    public static function parStatut(string $statut, string $ordre = 'date_heure.asc'): array
    {
        $lignes = SupabaseClient::select('epreuve', self::EMBED, ['statut' => 'eq.' . $statut], $ordre);
        return array_map([self::class, 'aplatir'], $lignes);
    }

    public static function parVille(int $villeId): array { /* filtre ville_id, tri date desc */ }
    public static function parSport(int $sportId): array { /* filtre sport_id, tri date desc */ }

    public static function creer(int $sportId, int $villeId, ?string $dateHeure, string $statut = 'a_venir'): int
    {
        $ligne = SupabaseClient::insert('epreuve', [
            'sport_id' => $sportId, 'ville_id' => $villeId,
            'date_heure' => $dateHeure, 'statut' => $statut,
        ]);
        return (int) $ligne['id'];
    }

    public static function modifier(int $id, int $sportId, int $villeId, ?string $dateHeure, string $statut): void
    {
        SupabaseClient::update('epreuve', ['id' => 'eq.' . $id], [
            'sport_id' => $sportId, 'ville_id' => $villeId,
            'date_heure' => $dateHeure, 'statut' => $statut,
        ]);
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

(`parVille` et `parSport` sont abrégées ici : elles suivent exactement le
modèle de `parStatut`.)

| Méthode | À quoi ça sert | Reçoit | Renvoie | Comment |
|---|---|---|---|---|
| `STATUTS` (constante) | La liste des statuts et leurs libellés | | | Sert aux menus déroulants et à vérifier qu'un statut reçu en POST est connu (`isset(Epreuve::STATUTS[$statut])`). |
| `aplatir($ligne)` | Simplifier une ligne | une ligne avec sous-fiches | la ligne aplatie | Identique à `Equipe::aplatir`. |
| `toutes()` | Toutes les épreuves pour le planning et l'administration | rien | liste aplatie | Embed sport + ville, tri par date **décroissante** (les plus récentes en premier). |
| `trouver($id)` | Une épreuve | l'identifiant | fiche ou `null` | |
| `aVenir()` | Les épreuves à venir (accueil, inscriptions) | rien | liste aplatie | Raccourci de `parStatut('a_venir')`, tri par date **croissante**. |
| `parStatut($statut, $ordre)` | Les épreuves d'un statut donné (ex. « en cours » sur le tableau de bord) | statut, tri | liste aplatie | Filtre `statut = ...`. |
| `parVille($villeId)`, `parSport($sportId)` | Vérifier avant de supprimer une ville ou un sport | id | liste aplatie | |
| `creer($sportId, $villeId, $dateHeure, $statut)` | Créer une épreuve | ids du sport et de la ville, date (peut être `null`), statut (`a_venir` par défaut) | nouvel `id` | Formulaire de la page Épreuves. |
| `modifier($id, ...)` | Modifier les quatre colonnes | id + mêmes champs | rien | Formulaire « Modifier l'épreuve ». |
| `changerStatut($id, $statut)` | Passer une épreuve « en cours » ou « terminée » | id, nouveau statut | rien | Menu rapide dans la liste. |
| `supprimer($id)` | Supprimer | id | rien | Le contrôleur supprime d'abord ses participations. |

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
            unset($ligne['equipe']);
            return $ligne;
        }, $lignes);
    }
```

#### `parUtilisateur(int $utilisateurId): array`

- **À quoi ça sert :** les équipes dont une personne fait partie (page « Mon profil »).
- **Comment ça marche :** l'embed est **imbriqué** sur deux niveaux : `equipe:equipe_id(nom, ville:ville_id(nom), sport:sport_id(nom))` = « va chercher l'équipe, et dans l'équipe va chercher sa ville et son sport ». La réponse ressemble à `['equipe' => ['nom' => 'Les Dodos', 'ville' => ['nom' => 'Saint-Denis'], 'sport' => ['nom' => 'Football']]]`. La fonction anonyme remonte tout au premier niveau (`equipe_nom`, `ville_nom`, `sport_nom`).

```php
    public const ROLES = [
        'joueur'    => 'Joueur',
        'capitaine' => 'Capitaine',
    ];

    public static function estMembre(int $equipeId, int $utilisateurId): bool
    {
        $lignes = SupabaseClient::select('membre_equipe', 'equipe_id', [
            'equipe_id' => 'eq.' . $equipeId, 'utilisateur_id' => 'eq.' . $utilisateurId,
        ]);
        return $lignes !== [];
    }

    public static function ajouter(int $equipeId, int $utilisateurId, string $roleInterne = 'joueur'): void
    {
        SupabaseClient::insert('membre_equipe', [
            'equipe_id' => $equipeId, 'utilisateur_id' => $utilisateurId, 'role_interne' => $roleInterne,
        ]);
    }

    public static function modifier(int $equipeId, int $utilisateurId, array $donnees): void
    {
        SupabaseClient::update(
            'membre_equipe',
            ['equipe_id' => 'eq.' . $equipeId, 'utilisateur_id' => 'eq.' . $utilisateurId],
            $donnees
        );
    }

    public static function definirRole(int $equipeId, int $utilisateurId, string $roleInterne): void
    {
        self::modifier($equipeId, $utilisateurId, ['role_interne' => $roleInterne]);
    }

    public static function definirPenalite(int $equipeId, int $utilisateurId, ?string $penalite): void
    {
        self::modifier($equipeId, $utilisateurId, ['penalite' => $penalite]);
    }

    public static function definirRecompense(int $equipeId, int $utilisateurId, ?string $recompense): void
    {
        self::modifier($equipeId, $utilisateurId, ['recompense' => $recompense]);
    }

    public static function retirer(int $equipeId, int $utilisateurId): void
    {
        SupabaseClient::delete('membre_equipe', [
            'equipe_id' => 'eq.' . $equipeId, 'utilisateur_id' => 'eq.' . $utilisateurId,
        ]);
    }

    public static function retirerTous(int $equipeId): void
    {
        SupabaseClient::delete('membre_equipe', ['equipe_id' => 'eq.' . $equipeId]);
    }

    public static function retirerUtilisateurPartout(int $utilisateurId): void
    {
        SupabaseClient::delete('membre_equipe', ['utilisateur_id' => 'eq.' . $utilisateurId]);
    }
}
```

| Méthode | À quoi ça sert | Reçoit | Comment |
|---|---|---|---|
| `ROLES` (constante) | Les rôles internes et leurs libellés | | Menu déroulant « Rôle dans l'équipe » et vérification des valeurs reçues. |
| `estMembre($equipeId, $utilisateurId)` | Savoir si une personne est déjà dans l'équipe | les deux ids | `select` d'une seule colonne avec les deux filtres ; vrai si la liste n'est pas vide. Évite un doublon. |
| `ajouter($equipeId, $utilisateurId, $roleInterne)` | Mettre une personne dans une équipe | ids de l'équipe et de la personne, rôle (`joueur` par défaut) | Insère une ligne d'association. |
| `modifier($equipeId, $utilisateurId, $donnees)` | Changer rôle, pénalité et/ou récompense en une fois | les deux ids + fiche des colonnes | Modifie la ligne identifiée par les **deux** ids (il faut les deux filtres pour viser une seule ligne). |
| `definirRole`, `definirPenalite`, `definirRecompense` | Raccourcis pour une seule colonne | ids + valeur | Appellent `modifier()`. |
| `retirer($equipeId, $utilisateurId)` | Enlever une personne d'une équipe | les deux ids | Supprime la ligne d'association. |
| `retirerTous($equipeId)` | Vider le roster | id de l'équipe | Appelé avant de supprimer une équipe. |
| `retirerUtilisateurPartout($utilisateurId)` | Sortir une personne de toutes ses équipes | id du compte | Appelé avant de supprimer un compte. |

### 7.7 `Participation.php` — table `participation` (colonnes : `epreuve_id`, `equipe_id`, `statut`, `score`, `classement`)

Cette table relie les équipes et les épreuves. Une ligne signifie « telle
équipe est inscrite à telle épreuve » et, une fois l'épreuve jouée, contient
son `score` et son `classement` (rang). `statut` vaut `en_attente` ou `validee`.

```php
class Participation
{
    public const STATUTS = [
        'en_attente' => 'En attente',
        'validee'    => 'Validée',
    ];

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

    public static function parEquipe(int $equipeId): array
    {
        $lignes = SupabaseClient::select(
            'participation',
            '*,epreuve:epreuve_id(date_heure,statut,sport:sport_id(nom),ville:ville_id(nom))',
            ['equipe_id' => 'eq.' . $equipeId]
        );
        $lignes = array_map(function (array $ligne) {
            $ligne['epreuve_date']   = $ligne['epreuve']['date_heure'] ?? null;
            $ligne['epreuve_statut'] = $ligne['epreuve']['statut'] ?? null;
            $ligne['sport_nom']      = $ligne['epreuve']['sport']['nom'] ?? null;
            $ligne['ville_nom']      = $ligne['epreuve']['ville']['nom'] ?? null;
            unset($ligne['epreuve']);
            return $ligne;
        }, $lignes);

        usort($lignes, fn(array $a, array $b) => strcmp((string) $b['epreuve_date'], (string) $a['epreuve_date']));
        return $lignes;
    }

    public static function existe(int $epreuveId, int $equipeId): bool
    {
        $lignes = SupabaseClient::select('participation', 'epreuve_id', [
            'epreuve_id' => 'eq.' . $epreuveId, 'equipe_id' => 'eq.' . $equipeId,
        ]);
        return $lignes !== [];
    }

    public static function inscrire(int $epreuveId, int $equipeId, string $statut = 'en_attente'): void
    {
        SupabaseClient::insert('participation', [
            'epreuve_id' => $epreuveId, 'equipe_id' => $equipeId, 'statut' => $statut,
        ]);
    }

    public static function saisirResultat(int $epreuveId, int $equipeId, ?int $score, ?int $classement): void
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

    public static function retirer(int $epreuveId, int $equipeId): void { /* delete avec les deux filtres */ }
    public static function retirerParEpreuve(int $epreuveId): void { /* delete epreuve_id = ... */ }
    public static function retirerParEquipe(int $equipeId): void   { /* delete equipe_id = ... */ }

    public static function classementGeneral(): array
    {
        return SupabaseClient::select('vue_classement_general', '*', [], 'total_points.desc');
    }
}
```

| Méthode | À quoi ça sert | Reçoit | Renvoie | Comment |
|---|---|---|---|---|
| `STATUTS` (constante) | Statuts d'inscription et libellés | | | Badges « En attente » / « Validée ». |
| `parEpreuve($epreuveId)` | Les équipes inscrites à une épreuve, avec leur résultat | id de l'épreuve | liste avec `equipe_nom`, `ville_nom`, `score`, `classement`, `statut` | Embed imbriqué équipe → ville. Tri par `classement` croissant, les équipes sans classement (`NULL`) à la fin. Aplatissement sur place. |
| `parEquipe($equipeId)` | Les épreuves auxquelles une équipe est inscrite (page de gestion d'une équipe, « Ma ville ») | id de l'équipe | liste avec `epreuve_date`, `epreuve_statut`, `sport_nom`, `ville_nom`, `statut`, `score`, `classement` | Embed imbriqué épreuve → sport et ville. Tri en PHP par date décroissante (`usort` avec une fonction fléchée `fn`), car PostgREST ne trie pas sur une colonne embarquée. |
| `existe($epreuveId, $equipeId)` | Savoir si l'équipe est déjà inscrite | les deux ids | `true`/`false` | Évite une double inscription. |
| `inscrire($epreuveId, $equipeId, $statut)` | Inscrire une équipe | les deux ids, statut (`en_attente` par défaut) | rien | Le super admin inscrit en `validee`, le manager en `en_attente`. |
| `saisirResultat($epreuveId, $equipeId, $score, $classement)` | Enregistrer le résultat | les deux ids, score et rang (ou `null` pour effacer) | rien | Modifie la ligne visée par les deux ids. |
| `validerInscription($epreuveId, $equipeId)` | Accepter une inscription faite par un manager | les deux ids | rien | Passe `statut` à `validee`. |
| `retirer(...)`, `retirerParEpreuve(...)`, `retirerParEquipe(...)` | Retirer une inscription, ou toutes celles d'une épreuve / d'une équipe | ids | rien | Utilisées avant de supprimer une épreuve ou une équipe. |
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
        $villes    = Ville::toutes();
        $sports    = Sport::tous();
        $equipes   = Equipe::toutes();
        $epreuves  = Epreuve::toutes();

        $epreuvesParStatut = ['a_venir' => 0, 'en_cours' => 0, 'terminee' => 0];
        foreach ($epreuves as $epreuve) {
            $statut = $epreuve['statut'] ?? null;
            if (isset($epreuvesParStatut[$statut])) {
                $epreuvesParStatut[$statut]++;
            }
        }

        $this->afficher('accueil/index', [
            'titre'             => 'Accueil',
            'nombreVilles'      => count($villes),
            'nombreSports'      => count($sports),
            'nombreEquipes'     => count($equipes),
            'nombreEpreuves'    => count($epreuves),
            'epreuvesParStatut' => $epreuvesParStatut,
            'epreuvesAVenir'    => Epreuve::aVenir(),
        ]);
    }
}
```

#### `index(): void`

- **Page :** `/`.
- **Comment ça marche :**
  1. Charge les quatre listes (villes, sports, équipes, épreuves) pour afficher des compteurs avec `count()`.
  2. `$epreuvesParStatut` commence avec trois compteurs à zéro. La boucle parcourt les épreuves et incrémente (`++` = « ajoute 1 ») la case correspondant à son statut. Le `isset(...)` ignore un statut inconnu au lieu de créer une case par erreur.
  3. Affiche la vue `accueil/index` avec `titre` (utilisé dans l'onglet du navigateur), les quatre nombres, la répartition par statut et les épreuves à venir.

### 8.2 `AuthController.php` — connexion, inscription, déconnexion

```php
class AuthController extends Controller
{
    public function afficherLogin(): void
    {
        if (Auth::estConnecte()) {
            $this->rediriger($this->pageApresConnexion());
        }
        $this->afficher('auth/login', ['titre' => 'Connexion', 'erreur' => null]);
    }
```

#### `afficherLogin(): void`

- **Page :** `GET /login`. Affiche le formulaire, sans message d'erreur (`null`). Une personne déjà connectée est renvoyée directement vers sa page d'accueil.

```php
    public function traiterLogin(): void
    {
        $email = $this->champ('email');
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
        $this->rediriger($this->pageApresConnexion());
    }

    /** Chaque rôle arrive sur "sa" page d'accueil après connexion */
    private function pageApresConnexion(): string
    {
        if (Auth::estSuperAdmin()) {
            return '/admin';
        }
        if (Auth::estManager()) {
            return '/gestion';
        }
        return '/';
    }
```

#### `traiterLogin(): void`

- **Page :** `POST /login`.
- **Variables :**

| Variable | Contenu |
|---|---|
| `$email` | Le champ `email` du formulaire, lu par `$this->champ()` (espaces retirés, chaîne vide si absent). |
| `$motDePasse` | Le champ `mot_de_passe`, sans `trim` (un espace peut faire partie d'un mot de passe). |
| `$utilisateur` | La fiche renvoyée par `verifierIdentifiants`, ou `null`. |

- **Comment ça marche :** si `null` → ré-affiche le formulaire avec un message volontairement vague (« Email ou mot de passe incorrect ») et `return` (fin). Sinon → `Auth::connecter()` puis redirection vers la page du rôle.

#### `pageApresConnexion(): string` (privée)

- **Ce qu'elle renvoie :** `/admin` pour un super admin, `/gestion` pour un manager, `/` sinon. Une méthode privée est un outil interne au contrôleur, jamais appelé par le routeur.

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
        $nomCompte = $this->champ('nom_compte');
        $email = $this->champ('email');
        $motDePasse = $_POST['mot_de_passe'] ?? '';
        $motDePasseConfirmation = $_POST['mot_de_passe_confirmation'] ?? '';
        $villeId = $this->champEntier('ville_id');

        $erreur = null;
        if ($nomCompte === '' || $email === '' || $motDePasse === '') {
            $erreur = 'Tous les champs marqués * sont obligatoires.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = 'L\'adresse email n\'est pas valide.';
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

        // L'inscription publique crée toujours un simple joueur : les managers
        // et super admins sont créés par un super admin depuis /admin/utilisateurs
        $id = Utilisateur::creer($nomCompte, $email, $motDePasse, Auth::ROLE_JOUEUR, $villeId);
        $utilisateur = Utilisateur::trouver($id);

        Auth::connecter($utilisateur);
        $this->rediriger('/profil');
    }
```

#### `traiterInscription(): void`

- **Page :** `POST /inscription`.
- **Variables :**

| Variable | D'où elle vient | Règle vérifiée |
|---|---|---|
| `$nomCompte` | champ `nom_compte`, lu par `champ()` | obligatoire |
| `$email` | champ `email`, lu par `champ()` | obligatoire, de forme valide (`filter_var(..., FILTER_VALIDATE_EMAIL)`), et aucun compte ne doit déjà l'utiliser |
| `$motDePasse` | champ `mot_de_passe` | obligatoire, au moins 8 caractères (`strlen` = longueur) |
| `$motDePasseConfirmation` | champ `mot_de_passe_confirmation` | doit être identique au mot de passe (`!==` = différent) |
| `$villeId` | champ `ville_id` (menu déroulant), lu par `champEntier()` | `null` si vide (choix « Aucune »), sinon un entier |
| `$erreur` | calculée | `null` tant que tout va bien ; sinon le **premier** message d'erreur rencontré (les `elseif` s'arrêtent à la première règle qui échoue) |
| `$id` | `Utilisateur::creer(...)` | identifiant du nouveau compte, créé avec le rôle `joueur` (on ne peut pas s'auto-déclarer admin) |
| `$utilisateur` | `Utilisateur::trouver($id)` | la fiche complète rechargée depuis la base |

- **Comment ça marche :** si `$erreur` n'est pas `null` → ré-affiche le formulaire avec le message (la vue pré-remplit les champs déjà saisis). Sinon → création du compte, connexion automatique et redirection vers `/profil`.

```php
    public function deconnexion(): void
    {
        Auth::deconnecter();
        $this->rediriger('/');
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

### 8.7 `AdminController.php` — tableau de bord, villes, sports (super admin)

```php
class AdminController extends Controller
{
    public function __construct()
    {
        // Toutes les actions de ce contrôleur exigent d'être super admin
        Auth::exigerSuperAdmin();
    }

    public function tableauDeBord(): void
    {
        $this->afficher('admin/tableau_de_bord', [
            'titre' => 'Administration',
            'stats' => [
                'villes'       => count(Ville::toutes()),
                'sports'       => count(Sport::tous()),
                'utilisateurs' => count(Utilisateur::tous()),
                'equipes'      => count(Equipe::toutes()),
                'epreuves'     => count(Epreuve::toutes()),
            ],
            'epreuvesEnCours' => Epreuve::parStatut('en_cours'),
            'managers'        => Utilisateur::parRole(Auth::ROLE_MANAGER),
        ]);
    }

    public function villes(): void
    {
        $this->afficher('admin/villes/index', ['titre' => 'Villes', 'villes' => Ville::toutes()]);
    }

    public function creerVille(): void
    {
        $nom = $this->champ('nom');

        if ($nom === '') {
            Flash::erreur('Le nom de la ville est obligatoire.');
            $this->rediriger('/admin/villes');
        }

        Ville::creer($nom);
        Flash::succes('Ville « ' . $nom . ' » créée.');
        $this->rediriger('/admin/villes');
    }

    public function modifierVille(string $id): void
    {
        $ville = Ville::trouver((int) $id);
        if ($ville === null) {
            $this->introuvable();
        }

        $this->afficher('admin/villes/modifier', ['titre' => 'Modifier la ville', 'ville' => $ville]);
    }

    public function enregistrerVille(string $id): void
    {
        $ville = Ville::trouver((int) $id);
        if ($ville === null) {
            $this->introuvable();
        }

        $nom = $this->champ('nom');
        if ($nom === '') {
            $this->afficher('admin/villes/modifier', [
                'titre'  => 'Modifier la ville',
                'ville'  => $ville,
                'erreur' => 'Le nom est obligatoire.',
            ]);
            return;
        }

        Ville::modifier((int) $id, $nom);
        Flash::succes('Ville renommée en « ' . $nom . ' ».');
        $this->rediriger('/admin/villes');
    }

    public function supprimerVille(string $id): void
    {
        $villeId = (int) $id;

        if (Equipe::parVille($villeId) !== []) {
            Flash::erreur('Impossible de supprimer cette ville : des équipes y sont rattachées.');
            $this->rediriger('/admin/villes');
        }
        if (Epreuve::parVille($villeId) !== []) {
            Flash::erreur('Impossible de supprimer cette ville : des épreuves y sont programmées.');
            $this->rediriger('/admin/villes');
        }
        if (Utilisateur::parVille($villeId) !== []) {
            Flash::erreur('Impossible de supprimer cette ville : des comptes (joueurs ou manager) y sont rattachés.');
            $this->rediriger('/admin/villes');
        }

        try {
            Ville::supprimer($villeId);
            Flash::succes('Ville supprimée.');
        } catch (SupabaseException $e) {
            Flash::erreur('Suppression refusée par la base de données : ' . $e->getMessage());
        }
        $this->rediriger('/admin/villes');
    }

    // sports(), creerSport(), modifierSport(), enregistrerSport(), supprimerSport() :
    // exactement le même schéma, sur la table sport (avec la colonne description en plus).
}
```

#### `__construct()`

- **Ce que c'est :** le **constructeur**, une méthode spéciale exécutée automatiquement au moment du `new AdminController()` fait par le routeur, **avant** l'action demandée.
- **À quoi ça sert :** placer le videur `Auth::exigerSuperAdmin()` une seule fois pour **toutes** les pages du contrôleur. Impossible d'oublier de protéger une page. Les trois autres contrôleurs d'administration font pareil, chacun avec le videur adapté à son public.

#### `tableauDeBord(): void`

- **Page :** `/admin`.
- **Variables passées à la vue :** `stats` (une fiche de cinq compteurs, obtenus avec `count()` = « combien d'éléments dans la liste »), `epreuvesEnCours` (les épreuves au statut `en_cours`), `managers` (les comptes de rôle `manager`).

#### `villes(): void`

- **Page :** `GET /admin/villes`. Une seule page réunit la liste des villes et le formulaire de création.

#### `creerVille(): void`

- **Page :** `POST /admin/villes`.
- **Variable `$nom` :** le champ `nom`, lu par `champ()`. S'il est vide → `Flash::erreur()` puis redirection vers la liste (le message s'affichera en haut). Sinon → `Ville::creer($nom)`, `Flash::succes()` et redirection. C'est le schéma **PRG** (Post/Redirect/Get) avec un message flash : si l'utilisateur rafraîchit la page, le formulaire n'est pas renvoyé.

#### `modifierVille(string $id)` et `enregistrerVille(string $id)`

- **Pages :** `GET` puis `POST /admin/villes/4/modifier`.
- **Comment ça marche :** on cherche la ville ; si elle n'existe pas → `introuvable()` (404). Le `GET` affiche le formulaire pré-rempli. Le `POST` lit le nom, ré-affiche le formulaire avec `erreur` s'il est vide, sinon `Ville::modifier()` et redirection avec message.

#### `supprimerVille(string $id): void`

- **Page :** `POST /admin/villes/4/supprimer`.
- **Comment ça marche :** avant de supprimer, on vérifie qu'**aucune** équipe, épreuve ou compte n'est rattaché à la ville (`!== []` = « la liste n'est pas vide »). Sinon, message d'erreur explicite plutôt que suppression en cascade : supprimer une ville ne doit pas effacer silencieusement ses équipes. Le `try { ... } catch (SupabaseException $e) { ... }` est une seconde sécurité : si la base refuse malgré tout (contrainte de clé étrangère), on affiche son message au lieu de planter.

#### Les cinq méthodes « sports »

`sports()`, `creerSport()`, `modifierSport($id)`, `enregistrerSport($id)`,
`supprimerSport($id)` reproduisent exactement le schéma des villes, avec la
colonne `description` en plus (facultative : `$this->champ('description') ?: null`
transforme une chaîne vide en `null`). La suppression est refusée si des
équipes ou des épreuves utilisent le sport.

### 8.8 `AdminUtilisateurController.php` — les comptes (super admin)

C'est ici que le super admin **crée les managers de ville**.

| Méthode | Page | Rôle |
|---|---|---|
| `__construct()` | | `Auth::exigerSuperAdmin()`. |
| `liste()` | `GET /admin/utilisateurs` | Liste de tous les comptes (via `Utilisateur::tous()`, sans hash) + formulaire de création. Appelle `afficherListe()`. |
| `afficherListe($erreur, $saisie)` (privée) | | Affiche la vue avec `utilisateurs`, `villes`, `roles` (= `Auth::ROLES`), et, en cas d'erreur, le message et la saisie précédente pour pré-remplir le formulaire. |
| `creer()` | `POST /admin/utilisateurs` | Lit `nom_compte`, `email`, `mot_de_passe`, `role`, `ville_id` ; valide (voir `valider()`), vérifie que l'email est libre, puis `Utilisateur::creer()` et redirection avec message. |
| `modifier($id)` | `GET /admin/utilisateurs/7/modifier` | Formulaire pré-rempli. Passe `estMoi` à la vue (vrai si l'on modifie son propre compte). |
| `enregistrer($id)` | `POST /admin/utilisateurs/7/modifier` | Valide, refuse un email pris par **un autre** compte, enregistre via `Utilisateur::modifier()`, et change le mot de passe seulement si le champ est rempli. |
| `supprimer($id)` | `POST /admin/utilisateurs/7/supprimer` | Retire d'abord la personne de ses équipes, puis supprime le compte. |
| `valider(...)` (privée) | | Les règles communes : nom et email obligatoires, email de forme valide, rôle connu, **un manager doit avoir une ville**, ville existante, mot de passe obligatoire à la création et d'au moins 8 caractères s'il est fourni. Renvoie le premier message d'erreur rencontré, ou `null`. |

Deux garde-fous évitent de se bloquer soi-même :

- on ne peut pas **supprimer son propre compte** ;
- on ne peut pas **changer son propre rôle** (`$role = $estMoi ? $utilisateur['role'] : $this->champ('role')` : si c'est moi, on garde le rôle actuel quoi qu'envoie le formulaire).

### 8.9 `AdminEpreuveController.php` — épreuves, participations, résultats (super admin)

| Méthode | Page | Rôle |
|---|---|---|
| `liste()` / `afficherListe()` | `GET /admin/epreuves` | Toutes les épreuves + formulaire de création (sport, ville, date, statut). |
| `creer()` | `POST /admin/epreuves` | Lit et valide le formulaire via `lireFormulaire()`, puis `Epreuve::creer()`. |
| `modifier($id)` / `enregistrer($id)` | `GET`/`POST /admin/epreuves/5/modifier` | Formulaire pré-rempli et enregistrement des quatre colonnes. |
| `changerStatut($id)` | `POST /admin/epreuves/5/statut` | Menu rapide dans la liste : vérifie que le statut est dans `Epreuve::STATUTS`, puis `Epreuve::changerStatut()`. C'est ainsi qu'on passe une épreuve « en cours » puis « terminée ». |
| `supprimer($id)` | `POST /admin/epreuves/5/supprimer` | Supprime d'abord les participations, puis l'épreuve. |
| `participations($id)` | `GET /admin/epreuves/5/participations` | Équipes inscrites avec leur résultat, et liste des équipes **du même sport** pas encore inscrites (`array_filter` = « garde les éléments qui vérifient la condition »). |
| `inscrire($id)` | `POST /admin/epreuves/5/participations` | Inscrit une équipe, directement en statut `validee`. Refuse si le sport ne correspond pas ou si elle est déjà inscrite. |
| `resultat($id)` | `POST .../participations/resultat` | Enregistre `score` (≥ 0) et `classement` (≥ 1) d'une équipe. Le score alimente le classement général des villes. |
| `validerParticipation($id)` | `POST .../participations/valider` | Passe une inscription faite par un manager de `en_attente` à `validee`. |
| `retirerParticipation($id)` | `POST .../participations/retirer` | Retire une équipe de l'épreuve. |
| `lireFormulaire()` (privée) | | Lit `sport_id`, `ville_id`, `statut`, `date_heure` ; vérifie que le sport et la ville existent et que le statut est connu ; convertit la date du champ `datetime-local` (`2026-09-20T15:00`) au format de la base (`2026-09-20 15:00:00`) avec `DateTime`, dans un `try/catch` pour rejeter une date invalide. Renvoie une liste de cinq valeurs, récupérée par **déstructuration** : `[$sportId, $villeId, $dateHeure, $statut, $erreur] = $this->lireFormulaire();`. |

### 8.10 `AdminEquipeController.php` — équipes et membres (super admin **et** manager)

C'est le contrôleur partagé. Son constructeur appelle `Auth::exigerAdmin()`
(super admin **ou** manager) ; ensuite, chaque action qui touche une équipe
précise passe par `chargerEquipe()`, qui vérifie la ville.

```php
    private function chargerEquipe(string $id): array
    {
        $equipe = Equipe::trouver((int) $id);
        if ($equipe === null) {
            $this->introuvable();
        }
        Auth::exigerGestionVille((int) $equipe['ville_id']);
        return $equipe;
    }
```

- **404** si l'équipe n'existe pas, **403** si le compte connecté ne gère pas sa ville (un manager de Saint-Denis ne peut pas ouvrir une équipe de Saint-Pierre, même en tapant l'adresse à la main).

| Méthode | Page | Rôle |
|---|---|---|
| `liste()` / `afficherListe()` | `GET /admin/equipes` | Super admin : toutes les équipes et un menu « Ville ». Manager : seulement `Equipe::parVille(Auth::villeGeree())`, sans menu (la vue affiche « Ville : X (ta ville) »). |
| `creer()` | `POST /admin/equipes` | `$villeId = Auth::estManager() ? Auth::villeGeree() : $this->champEntier('ville_id')` : pour un manager, la ville est **imposée**, quoi qu'envoie le formulaire. Après création, redirection vers la page de l'équipe pour ajouter des membres. |
| `supprimer($id)` | `POST /admin/equipes/3/supprimer` | Vide le roster et les inscriptions, puis supprime. |
| `membres($id)` | `GET /admin/equipes/3` | La page de gestion : membres, comptes que l'on peut ajouter (`candidats`), inscriptions aux épreuves et épreuves à venir du même sport (`epreuvesDisponibles`). Pour un manager, les candidats sont les comptes de sa ville **ou sans ville** (`Utilisateur::parVille($ville, true)`). |
| `ajouterMembre($id)` | `POST /admin/equipes/3/membres` | Vérifie que le compte existe, que le rôle interne est connu, qu'un manager ne recrute pas dans une autre ville, et que la personne n'est pas déjà membre. |
| `modifierMembre($id)` | `POST .../membres/modifier` | Rôle interne (joueur / capitaine), pénalité, récompense, en une seule mise à jour. |
| `retirerMembre($id)` | `POST .../membres/retirer` | Retire un membre. |
| `inscrireEpreuve($id)` | `POST /admin/equipes/3/inscriptions` | Inscrit l'équipe à une épreuve **à venir** du **même sport**, sans doublon. Statut : `validee` si c'est un super admin, `en_attente` si c'est un manager (un super admin devra valider). |
| `retirerInscription($id)` | `POST .../inscriptions/retirer` | Annule une inscription. |
| `retourEquipe($equipeId)` (privée) | | Raccourci : redirection vers `/admin/equipes/<id>`. |

### 8.11 `ManagerController.php` — la page « Ma ville » (manager)

| Méthode | Page | Rôle |
|---|---|---|
| `__construct()` | | `Auth::exigerManager()`. |
| `index()` | `GET /gestion` | Charge la ville du manager (`Auth::villeGeree()`), ses équipes, les inscriptions encore `en_attente` (en parcourant `Participation::parEquipe()` pour chaque équipe, avec deux `foreach` imbriqués) et les épreuves à venir. Si le compte n'a pas de ville, la vue affiche un message demandant à un super admin de corriger le compte. |

La gestion proprement dite (créer une équipe, ajouter des membres, inscrire à
une épreuve) se fait dans `AdminEquipeController`, partagé avec le super admin.

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
    <meta name="color-scheme" content="dark light">
    <title><?= isset($titre) ? htmlspecialchars($titre) . ' — ' : '' ?>Entrevilles-Reu</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="/assets/js/theme.js"></script>
</head>
<body>
    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="conteneur">
        <?php foreach (Flash::recuperer() as $flash): ?>
            <p class="flash flash-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></p>
        <?php endforeach; ?>

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
| `<meta name="color-scheme" content="dark light">` | Prévient le navigateur que la page existe en sombre et en clair. |
| `<link rel="stylesheet" href="<?= Format::asset('/assets/css/style.css') ?>">` | Charge la feuille de style. `Format::asset()` ajoute `?v=<date de modification>` à l'adresse pour que le navigateur retélécharge le fichier après chaque mise à jour (section 5.9). |
| `<script src="<?= Format::asset('/assets/js/theme.js') ?>">` | Charge le script du thème **dans l'entête**, avant l'affichage, pour appliquer tout de suite le thème mémorisé (section 11.3). Même protection anti-cache. |
| `require navbar.php` | Insère la barre de navigation. |
| `foreach (Flash::recuperer() as $flash)` | Affiche les messages laissés par la page précédente (« Ville créée. »), un bandeau par message, puis les efface (voir 5.7). |
| `<main class="conteneur">` + `require $cheminVue` | Insère **la vue demandée** par le contrôleur (`$cheminVue` vient de `Controller::afficher`). |
| `require footer.php` | Insère le pied de page. |

**Variables attendues :** `$titre` (facultative), `$cheminVue` (obligatoire).

#### `navbar.php` — la barre de navigation

```php
<?php
$uriNav = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$lienActif = function (string ...$chemins) use ($uriNav): string {
    foreach ($chemins as $chemin) {
        if ($uriNav === $chemin || ($chemin !== '/' && str_starts_with($uriNav, $chemin . '/'))) {
            return 'actif';
        }
    }
    return '';
};
?>
<nav class="navbar">
    <a href="/" class="navbar-logo">Entrevilles-Reu 🏝️</a>
    <div class="navbar-liens">
        <a href="/" class="<?= $lienActif('/') ?>">Accueil</a>
        <a href="/planning" class="<?= $lienActif('/planning', '/epreuves') ?>">Planning</a>
        <a href="/equipes" class="<?= $lienActif('/equipes') ?>">Équipes</a>
        <a href="/classement" class="<?= $lienActif('/classement') ?>">Classement</a>

        <?php if (Auth::estConnecte()): ?>
            <?php if (Auth::estSuperAdmin()): ?>
                <a href="/admin" class="<?= $lienActif('/admin') ?>">Administration</a>
            <?php elseif (Auth::estManager()): ?>
                <a href="/gestion" class="<?= $lienActif('/gestion', '/admin') ?>">Ma ville</a>
            <?php endif; ?>
            <a href="/profil" class="<?= $lienActif('/profil') ?>">Profil (<?= htmlspecialchars(Auth::utilisateur()['nom_compte']) ?>)</a>
            <a href="/deconnexion">Déconnexion</a>
        <?php else: ?>
            <a href="/login" class="<?= $lienActif('/login') ?>">Connexion</a>
            <a href="/inscription" class="<?= $lienActif('/inscription') ?>">Créer un compte</a>
        <?php endif; ?>

        <button type="button" id="basculeTheme" class="bouton-theme" aria-pressed="false" title="Passer au thème clair">☀️ Thème clair</button>
    </div>
</nav>
```

- `$lienActif` est une **fonction anonyme** rangée dans une variable. On l'appelle avec un ou plusieurs chemins (`string ...$chemins` = « autant de chemins que l'on veut ») ; elle renvoie `'actif'` si l'adresse de la page en cours est l'un d'eux ou commence par l'un d'eux. Ainsi « Planning » reste surligné sur `/epreuves/5`. `use ($uriNav)` donne à la fonction accès à la variable calculée juste avant.
- Les quatre premiers liens sont toujours visibles.
- Si connecté : lien « Administration » (super admin) **ou** « Ma ville » (manager), puis « Profil (pseudo) », « Déconnexion ».
- Sinon : « Connexion », « Créer un compte ».
- Le bouton `#basculeTheme` change de thème : son texte (« ☀️ Thème clair » ou « 🌙 Thème sombre ») est mis à jour par `theme.js` (section 11.3). `type="button"` évite qu'il soit pris pour un bouton d'envoi de formulaire.
- La barre ne reçoit aucune variable : elle interroge directement `Auth`.

#### `footer.php`

Une seule ligne de texte dans une balise `<footer class="pied-page">`.

#### `menu_admin.php` — le sous-menu de l'espace d'administration

```php
<?php
$uriActuelle = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$liensMenu = Auth::estSuperAdmin()
    ? [
        '/admin'              => 'Tableau de bord',
        '/admin/villes'       => 'Villes',
        '/admin/sports'       => 'Sports',
        '/admin/utilisateurs' => 'Utilisateurs',
        '/admin/epreuves'     => 'Épreuves',
        '/admin/equipes'      => 'Équipes',
    ]
    : [
        '/gestion'       => 'Ma ville',
        '/admin/equipes' => 'Mes équipes',
    ];
?>
<nav class="menu-admin">
    <?php foreach ($liensMenu as $lien => $libelle): ?>
        <?php $actif = $uriActuelle === $lien || ($lien !== '/admin' && str_starts_with($uriActuelle, $lien . '/')); ?>
        <a href="<?= $lien ?>" class="<?= $actif ? 'actif' : '' ?>"><?= htmlspecialchars($libelle) ?></a>
    <?php endforeach; ?>
</nav>
```

- Inclus en haut de chaque vue d'administration (`require __DIR__ . '/../partials/menu_admin.php'`).
- `$liensMenu` dépend du rôle : six rubriques pour le super admin, deux pour le manager.
- `$actif` : le lien de la page en cours est surligné. Vrai si l'adresse est exactement celle du lien, ou si elle commence par le lien suivi de `/` (`/admin/equipes/3` surligne « Équipes »). L'exception `$lien !== '/admin'` évite que « Tableau de bord » soit surligné sur toutes les pages.

#### `erreur_403.php`, `erreur_404.php`, `erreur_500.php`

Pages complètes et autonomes (avec leur propre `<html>`) :

| Page | Quand | Variable affichée |
|---|---|---|
| `erreur_403.php` | Accès refusé : `Auth::refuser()` ou jeton CSRF manquant dans le routeur | `$motifRefus` (le pourquoi) et un lien « Se connecter » si personne n'est connecté |
| `erreur_404.php` | Adresse ou `id` inconnu : routeur ou `Controller::introuvable()` | — |
| `erreur_500.php` | Erreur imprévue attrapée par `index.php` (base injoignable…) | `$messageErreur` |

### 9.2 Les vues de pages

#### `accueil/index.php` — variables : `$nombreVilles`, `$nombreSports`, `$nombreEquipes`, `$nombreEpreuves`, `$epreuvesParStatut`, `$epreuvesAVenir`

```php
<div class="hero">
    <div class="hero-contenu">
        <span class="hero-surtitre">Championnat inter-communes · La Réunion</span>
        <h1>Le défi sportif des <span>communes</span> de La Réunion</h1>
        <p>Épreuves, équipes et classement inter-villes, en direct.</p>
        <div class="hero-actions">
            <a href="/planning" class="bouton">Voir le planning</a>
            <a href="/classement" class="bouton bouton-secondaire">Classement général</a>
        </div>
    </div>
</div>

<h2>En chiffres</h2>

<div class="grille-cartes grille-chiffres">
    <div class="carte carte-chiffre">
        <span class="chiffre"><?= (int) $nombreVilles ?></span>
        <p>Villes participantes</p>
    </div>
    <!-- … même chose pour les sports, les équipes et les épreuves -->
</div>

<h2>Épreuves par statut</h2>

<div class="grille-cartes grille-chiffres">
    <div class="carte carte-chiffre">
        <span class="badge badge-a_venir">À venir</span>
        <span class="chiffre"><?= (int) $epreuvesParStatut['a_venir'] ?></span>
        <p>épreuves programmées</p>
    </div>
    <!-- … idem pour en_cours et terminee -->
</div>

<h2>Prochaines épreuves</h2>

<?php if (empty($epreuvesAVenir)): ?>
    <div class="etat-vide">
        <p>Aucune épreuve à venir pour le moment.</p>
        <a href="/planning" class="bouton bouton-secondaire">Voir tout le planning</a>
    </div>
<?php else: ?>
    <div class="grille-cartes">
        <?php foreach ($epreuvesAVenir as $epreuve): ?>
            <div class="carte">
                <span class="badge badge-<?= htmlspecialchars($epreuve['statut']) ?>">
                    <?= htmlspecialchars(Epreuve::STATUTS[$epreuve['statut']] ?? $epreuve['statut']) ?>
                </span>
                <h3><?= htmlspecialchars($epreuve['sport_nom']) ?></h3>
                <p>À <?= htmlspecialchars($epreuve['ville_nom']) ?></p>
                <?php if ($epreuve['date_heure']): ?>
                    <p><?= Format::dateHeure($epreuve['date_heure'], 'd/m/Y à H:i') ?></p>
                <?php endif; ?>
                <a href="/epreuves/<?= (int) $epreuve['id'] ?>" class="bouton">Voir le détail</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
```

- La **bannière** (`.hero`) : un surtitre, un titre dont le mot « communes » est coloré (`<span>`), et deux boutons d'action vers le planning et le classement.
- **En chiffres** : quatre cartes-compteurs (`.carte-chiffre`) avec le nombre en grand (`.chiffre`) et un libellé. `(int)` garantit qu'on affiche un nombre.
- **Épreuves par statut** : trois cartes du même type, avec un badge coloré en plus. La valeur vient de `$epreuvesParStatut['a_venir']`, calculée par le contrôleur.
- `empty($epreuvesAVenir)` : « la liste est-elle vide ? » → bloc « état vide » avec un bouton, sinon grille.
- `foreach` : une carte par épreuve.
- `class="badge badge-<?= statut ?>"` : la classe CSS est construite à partir du statut (`badge-a_venir`), ce qui donne sa couleur au badge ; le texte affiché est le libellé lisible pris dans `Epreuve::STATUTS`.
- `Format::dateHeure(...)` écrit la date brute de la base (`2026-09-20T15:00:00`) sous la forme « 20/09/2026 à 15:00 » (voir 5.9).
- Le lien « Voir le détail » pointe vers `/epreuves/<id>`.

#### `auth/login.php` — variable : `$erreur`

```php
<h1>Connexion</h1>

<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="POST" action="/login">
    <?= Csrf::champ() ?>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>

    <label for="mot_de_passe">Mot de passe</label>
    <input type="password" id="mot_de_passe" name="mot_de_passe" required>

    <button type="submit">Se connecter</button>
</form>
```

- Affiche le message d'erreur s'il y en a un.
- `<?= Csrf::champ() ?>` insère le champ caché portant le jeton de sécurité (voir 5.6). **Tous** les formulaires `POST` du site commencent ainsi ; sans lui, le routeur refuse l'envoi.
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
<?php $medailles = [1 => '🥇', 2 => '🥈', 3 => '🥉']; ?>
<?php foreach ($classement as $i => $ligne): ?>
    <?php $rang = $i + 1; ?>
    <tr class="<?= $rang <= 3 ? 'podium podium-' . $rang : '' ?>">
        <td><?= $rang ?><?= isset($medailles[$rang]) ? ' ' . $medailles[$rang] : '' ?></td>
        <td><?= htmlspecialchars($ligne['ville_nom']) ?></td>
        <td><strong><?= (int) $ligne['total_points'] ?></strong></td>
    </tr>
<?php endforeach; ?>
```

- `foreach ($classement as $i => $ligne)` : `$i` est la position dans la liste (0, 1, 2…), `$ligne` la fiche. Le rang est `$i + 1` (1, 2, 3…) : comme la liste est déjà triée par points, la position **est** le rang.
- `$medailles` : une fiche « rang → emoji ». Pour les trois premiers, la ligne reçoit les classes `podium podium-1` (ou 2, 3), que le CSS colore en or, argent et bronze, et une médaille s'affiche à côté du rang.

### 9.3 Les vues d'administration

Toutes commencent par `require menu_admin.php` et suivent les mêmes motifs :

- **Une page = une liste + un formulaire de création** en bas (villes, sports, utilisateurs, épreuves, équipes).
- **Un formulaire par action dans les tableaux** (`class="form-inline"`) : supprimer, changer un statut, enregistrer un résultat. Chaque petit formulaire contient `Csrf::champ()`, éventuellement un `<input type="hidden">` avec l'identifiant visé, et un bouton.
  ```php
  <form method="POST" action="/admin/villes/<?= (int) $ville['id'] ?>/supprimer" class="form-inline" onsubmit="return confirm('Supprimer cette ville ?');">
      <?= Csrf::champ() ?>
      <button type="submit" class="bouton-danger">Supprimer</button>
  </form>
  ```
  `onsubmit="return confirm(...)"` est le seul JavaScript écrit directement dans les vues (le reste est dans `theme.js`) : une boîte « OK / Annuler » ; si l'on annule, rien n'est envoyé. On utilise `POST` (et non un simple lien) pour une suppression, car une action qui modifie des données ne doit jamais se déclencher par un simple clic sur un lien `GET`.
- **Pré-remplissage après erreur** : `value="<?= htmlspecialchars($saisie['nom'] ?? '') ?>"` et `<?= ... === ... ? 'selected' : '' ?>` sur les options des menus, pour que l'utilisateur ne retape pas tout.
- **Badges** : `class="badge badge-<?= $valeur ?>"` avec la valeur brute (`super_admin`, `manager`, `en_attente`…) et le libellé lisible pris dans la constante correspondante (`$roles[...]`, `$statuts[...]`).

| Vue | Variables reçues | Contenu |
|---|---|---|
| `admin/tableau_de_bord.php` | `stats`, `epreuvesEnCours`, `managers` | Cinq compteurs cliquables (`.grille-stats`), les épreuves en cours avec un lien « Résultats », la liste des managers et leur ville. |
| `admin/villes/index.php` | `villes` | Tableau Nom / Modifier / Supprimer + formulaire « Ajouter une ville ». |
| `admin/villes/modifier.php` | `ville`, `erreur` | Formulaire de renommage avec bouton « Annuler ». |
| `admin/sports/index.php`, `sports/modifier.php` | `sports` / `sport`, `erreur` | Idem, avec une `<textarea>` pour la description. |
| `admin/utilisateurs/index.php` | `utilisateurs`, `villes`, `roles`, `erreur`, `saisie` | Tableau Nom / Email / Rôle (badge) / Ville / Actions ; « (moi) » à côté de son propre compte, sans bouton Supprimer. Formulaire de création avec menus Rôle et Ville. |
| `admin/utilisateurs/modifier.php` | `utilisateur`, `villes`, `roles`, `erreur`, `estMoi` | Même formulaire ; le menu Rôle est `disabled` si `estMoi` ; champ « Nouveau mot de passe » facultatif. |
| `admin/epreuves/index.php` | `epreuves`, `sports`, `villes`, `statuts`, `erreur`, `saisie` | Tableau avec, par ligne, un badge de statut **et** un mini-formulaire « Changer » (menu des statuts), puis les boutons Participations / Modifier / Supprimer. Formulaire de création avec `<input type="datetime-local">`. |
| `admin/epreuves/modifier.php` | `epreuve`, `sports`, `villes`, `statuts`, `erreur` | Formulaire pré-rempli ; la date est convertie par `Format::dateHeureLocal()`. |
| `admin/epreuves/participations.php` | `epreuve`, `participations`, `equipesDisponibles`, `statuts`, `statutsEpreuve` | Par équipe inscrite : badge d'inscription, mini-formulaire score / rang, bouton « Valider » (si en attente) et « Retirer ». En bas, menu pour inscrire une équipe du même sport. |
| `admin/equipes/index.php` | `equipes`, `sports`, `villes`, `villeGeree`, `erreur`, `saisie` | Liste (limitée à la ville pour un manager) + formulaire de création ; le menu Ville n'apparaît que pour le super admin. |
| `admin/equipes/membres.php` | `equipe`, `membres`, `candidats`, `rolesInternes`, `participations`, `epreuvesDisponibles`, `statutsParticipation`, `statutsEpreuve` | Membres avec, par ligne, un formulaire rôle / pénalité / récompense et un bouton Retirer ; formulaire « Ajouter un membre » ; tableau des inscriptions aux épreuves ; formulaire « Inscrire à une épreuve » (avec un rappel pour le manager : l'inscription sera « en attente »). |
| `gestion/index.php` | `ville`, `equipes`, `enAttente`, `epreuvesAVenir` | La page « Ma ville » du manager : compteurs, ses équipes avec lien « Membres & inscriptions », inscriptions en attente, prochaines épreuves. |

### 9.4 Récapitulatif : qui fournit quoi aux vues publiques

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
| `gestion/index` et `admin/*` | voir le tableau de la section 9.3 | |

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

### 10.4 `migration_roles.sql` — autoriser les rôles manager et super_admin

```sql
DO $$
DECLARE
    contrainte record;
BEGIN
    FOR contrainte IN
        SELECT conname
        FROM pg_constraint
        WHERE conrelid = 'utilisateur'::regclass
          AND contype = 'c'
          AND pg_get_constraintdef(oid) ILIKE '%role%'
    LOOP
        EXECUTE format('ALTER TABLE utilisateur DROP CONSTRAINT %I', contrainte.conname);
    END LOOP;
END $$;

UPDATE utilisateur SET role = 'super_admin' WHERE role = 'admin';

ALTER TABLE utilisateur DROP CONSTRAINT IF EXISTS utilisateur_role_check;
ALTER TABLE utilisateur
    ADD CONSTRAINT utilisateur_role_check
    CHECK (role IN ('joueur', 'manager', 'super_admin'));

ALTER TABLE utilisateur DROP CONSTRAINT IF EXISTS utilisateur_manager_ville_check;
ALTER TABLE utilisateur
    ADD CONSTRAINT utilisateur_manager_ville_check
    CHECK (role <> 'manager' OR ville_id IS NOT NULL);
```

À exécuter une fois dans Supabase (SQL Editor), après `migration_mot_de_passe.sql`.

| Bloc | En français |
|---|---|
| `DO $$ ... END $$;` | Un petit programme SQL. Il cherche dans le catalogue de la base (`pg_constraint`) toute règle `CHECK` existante sur la table `utilisateur` qui mentionne `role` (son nom dépend du schéma d'origine, d'où la recherche dynamique) et la supprime. Sans cela, l'ancienne règle « role ∈ (joueur, admin) » refuserait les nouvelles valeurs. |
| `UPDATE ... SET role = 'super_admin' WHERE role = 'admin'` | Les anciens admins deviennent super admins. |
| `CHECK (role IN (...))` | Nouvelle règle : seules ces trois valeurs sont acceptées. Le `DROP CONSTRAINT IF EXISTS` juste avant permet de relancer le script sans erreur. |
| `CHECK (role <> 'manager' OR ville_id IS NOT NULL)` | « Si le rôle est manager, alors la ville est obligatoire. » La base impose la règle que le PHP vérifie déjà. |

Le fichier se termine par les instructions à utiliser si la colonne `role` est
un type `ENUM` plutôt qu'un texte (cas où les `ALTER` ci-dessus échoueraient).

### 10.5 `creer_admin.php` — créer le premier super administrateur

```php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Ce script ne s\'exécute qu\'en ligne de commande.');
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/autoload.php';

$email           = $argv[1] ?? 'admin@entrevilles-reu.re';
$motDePasseClair = $argv[2] ?? 'changez-moi123';
$nomCompte       = $argv[3] ?? 'Super administrateur';

if (strlen($motDePasseClair) < 8) {
    echo "Le mot de passe doit faire au moins 8 caractères.\n";
    exit(1);
}

$existant = Utilisateur::trouverParEmail($email);
if ($existant !== null) {
    echo "Un compte existe déjà avec l'email $email (rôle : {$existant['role']}).\n";
    exit(1);
}

$id = Utilisateur::creer($nomCompte, $email, $motDePasseClair, Auth::ROLE_SUPER_ADMIN);
echo "Super administrateur créé (id $id) : $email / $motDePasseClair\n";
```

**À quoi ça sert :** l'inscription publique crée toujours des `joueur`, et seul
un super admin peut créer d'autres comptes. Il faut donc un moyen de créer le
**premier** super admin : ce script, lancé une fois en ligne de commande.

```
php database/creer_admin.php
php database/creer_admin.php moi@exemple.re MonMotDePasse "Prénom Nom"
```

| Ligne / variable | Rôle |
|---|---|
| `PHP_SAPI !== 'cli'` | Refuse de s'exécuter depuis un navigateur : uniquement en ligne de commande (`cli`). Double sécurité avec le `.htaccess` du dossier. |
| `$argv[1]`, `$argv[2]`, `$argv[3]` | Les arguments tapés après le nom du script (email, mot de passe, nom). `?? 'valeur'` = valeur par défaut si absent. |
| `Utilisateur::trouverParEmail($email)` | Si le compte existe déjà, on s'arrête (`exit(1)` = fin avec un code d'erreur) en expliquant comment le promouvoir en SQL. |
| `Utilisateur::creer(..., Auth::ROLE_SUPER_ADMIN)` | Réutilise le modèle : le mot de passe est haché, la ligne insérée via Supabase. |

Une fois connecté, changer le mot de passe depuis « Utilisateurs », puis
supprimer le fichier.

---

## 11. La feuille de style — `assets/css/style.css`

Le CSS décide de l'apparence : couleurs, polices, espacements. Le thème par
défaut est « tableau de bord esport » : fond sombre, accents lumineux cyan et
violet. Un **thème clair** est disponible via le bouton de la barre de
navigation.

### 11.1 Les variables de couleur et le thème clair

En haut du fichier, un bloc `:root { ... }` définit des **variables CSS** : on
donne un nom à chaque couleur, puis on l'utilise partout avec `var(--nom)`.
Changer une valeur ici change tout le site.

| Variable | Sombre | Clair | Utilisée pour |
|---|---|---|---|
| `--fond` | `#0B0E14` (presque noir) | `#F4F1EA` (blanc cassé chaud) | Le fond de la page |
| `--panneau` | `#12161F` | `#FFFFFF` | Le fond des cartes, tableaux, formulaires |
| `--panneau-alt` | `#171C27` | `#EDE9E0` | Fond au survol, en-têtes de tableau |
| `--panneau-translucide` | panneau à 85 % | blanc à 85 % | La barre de navigation (effet de flou derrière) |
| `--bordure`, `--bordure-vive` | blanc à 8 % / 16 % | noir à 10 % / 18 % | Bordures discrètes / au survol |
| `--texte` | `#E7E9F0` | `#1B1E24` | Texte principal |
| `--texte-doux` | `#8B93A7` | `#5B6270` | Texte secondaire, étiquettes |
| `--texte-fort` | `#FFFFFF` | `#0B0E14` | Titres, logo |
| `--cyan`, `--cyan-vif` | `#2DE1C2`, `#4FF5D8` | `#0E8A75`, `#0AA98E` | Liens, boutons, accent principal, badge « à venir » |
| `--violet`, `--violet-clair` | `#8B5CF6`, `#B79CFF` | `#6D3FD9`, `#5B32C4` | Accent secondaire, badge super admin |
| `--or`, `--argent`, `--bronze` | `#F5B942`, `#C0C7D1`, `#D89A5B` | `#B9790C`, `#6B7280`, `#A0522D` | Badge « terminée », manager, podium du classement |
| `--rose` | `#FF4D6D` | `#D6314F` | Badge « en cours », « Capitaine », bouton Supprimer |
| `--bouton-texte` | `#06110F` | `#FFFFFF` | Texte d'un bouton cyan (contraste) |
| `--lueur`, `--ombre` | cyan translucide, ombre noire | cyan translucide, ombre légère | Halo des boutons et cartes au survol |
| `--halo-cyan`, `--halo-violet` (+ `-fort`) | très transparents | un peu plus présents | Dégradés de fond de page et de bannière |
| `--rayon` | `8px` | idem | Arrondi commun des cartes, tableaux, formulaires |

Les couleurs `#RRGGBB` sont des codes hexadécimaux : deux chiffres pour le
rouge, deux pour le vert, deux pour le bleu. Les couleurs d'accent sont plus
**foncées** en clair : un cyan néon lisible sur du noir devient illisible sur
du blanc.

**Comment marche le thème clair :** juste après `:root`, un second bloc
`[data-theme="clair"] { ... }` redéfinit **les mêmes variables**. Ce sélecteur
signifie « l'élément qui porte l'attribut `data-theme="clair"` » ; `theme.js`
pose cet attribut sur la balise `<html>`. Comme tout le reste de la feuille
utilise `var(--nom)`, aucune autre règle n'a besoin de changer. La propriété
`color-scheme` (`dark` ou `light`) indique en plus au navigateur de dessiner
les menus déroulants et cases natives dans le bon ton.

### 11.2 Les principales classes

Une **classe CSS** est une étiquette posée sur une balise HTML
(`<div class="carte">`) pour lui appliquer un style.

| Classe | Rôle |
|---|---|
| `.conteneur` | Colonne centrée avec des marges, autour du contenu principal. |
| `.navbar`, `.navbar-logo`, `.navbar-liens`, `.navbar-liens a.actif` | La barre de navigation, collée en haut (`position: sticky`) avec un fond translucide flouté (`backdrop-filter`). Le lien de la page en cours porte la classe `actif`. Sous 640 px de large (téléphone), elle passe en colonne. |
| `.bouton-theme` | Le bouton clair / sombre, en forme de pilule, qui annule le style cyan des boutons ordinaires. |
| `.hero`, `.hero-contenu`, `.hero-surtitre`, `.hero-actions` | La grande bannière de l'accueil : dégradé de fond, deux halos colorés dessinés par `::before` (un pseudo-élément, c'est-à-dire un calque ajouté par le CSS sans balise HTML), surtitre en capitales, boutons d'action. |
| `.grille-cartes`, `.carte` | Une grille de cartes qui s'adapte à la largeur. Au survol, la carte se soulève (`transform: translateY(-2px)`) et un filet dégradé cyan → violet apparaît en haut (`::before`). |
| `.grille-chiffres`, `.carte-chiffre`, `.chiffre` | Les cartes-compteurs de l'accueil : grille plus serrée, nombre en grand et en cyan. |
| `.badge` + `.badge-a_venir` / `.badge-en_cours` / `.badge-terminee` | Les pastilles de statut. Le suffixe correspond **exactement** à la valeur en base, d'où `class="badge badge-<?= $statut ?>"` dans les vues. |
| `.podium`, `.podium-1` / `-2` / `-3` | Les trois premières lignes du classement : rang en gras, coloré or, argent ou bronze. |
| `button`, `.bouton` | Les boutons et liens-boutons. Au survol, la couleur du texte est fixée explicitement : sinon la règle `a:hover` (texte cyan) rendait un lien-bouton illisible sur son fond cyan. |
| `:focus-visible` | Contour cyan autour de l'élément sélectionné **au clavier** (touche Tab), invisible à la souris : indispensable pour naviguer sans souris. |
| `.erreur` | Le message d'erreur des formulaires (rouge). |
| `.etat-vide` | Bloc « aucune donnée » en pointillés, utilisé par les pages publiques quand une liste est vide. |
| `.pied-page` | Le pied de page. |
| `@media (max-width: 640px)` | Règles spéciales pour les petits écrans ; les tableaux y défilent horizontalement au lieu de déborder. |
| `@media (prefers-reduced-motion: reduce)` | Supprime les animations pour les personnes qui les ont désactivées dans leur système. |
| `.menu-admin`, `.menu-admin a.actif` | Le sous-menu de l'administration ; le lien actif est surligné en cyan. |
| `.grille-stats`, `.stat` | Les compteurs du tableau de bord (gros chiffre en Rajdhani, libellé en dessous). |
| `.flash`, `.flash-succes`, `.flash-erreur` | Les bandeaux de message flash (vert cyan ou rouge). |
| `form.form-inline` | Annule la mise en forme « panneau » des formulaires pour les petits formulaires dans les tableaux : affichage en ligne, sans fond ni bordure. |
| `form.form-large` | Formulaire plus large (720 px) pour les pages avec plusieurs menus. |
| `.actions` | Aligne côte à côte les boutons d'une cellule. |
| `.bouton-petit`, `.bouton-secondaire`, `.bouton-danger` | Variantes de bouton : compact, discret (fond sombre), rouge pour les suppressions. |
| `.badge-super_admin`, `.badge-manager`, `.badge-joueur` | Pastilles de rôle (violet, or, gris). |
| `.badge-validee`, `.badge-en_attente` | Pastilles de statut d'inscription (cyan, or). |
| `.texte-doux` | Petit texte gris d'aide. |

Les polices `Rajdhani` (titres) et `Inter` (texte) sont chargées depuis Google
Fonts par la ligne `@import` du début.

### 11.3 Le bouton clair / sombre — `assets/js/theme.js`

C'est le seul fichier **JavaScript** du projet. Contrairement au PHP, qui
s'exécute sur le serveur, le JavaScript s'exécute **dans le navigateur**, une
fois la page reçue : il peut réagir aux clics et modifier la page sans la
recharger.

```js
(function () {
    var CLE = 'entrevilles-theme';
    var racine = document.documentElement; // la balise <html>

    function themeMemorise() {
        try {
            return localStorage.getItem(CLE);
        } catch (e) {
            return null;
        }
    }

    function memoriser(theme) {
        try {
            localStorage.setItem(CLE, theme);
        } catch (e) {
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

    function mettreAJourBouton(bouton) {
        var clair = themeActuel() === 'clair';
        bouton.textContent = clair ? '🌙 Thème sombre' : '☀️ Thème clair';
        bouton.title = clair ? 'Passer au thème sombre' : 'Passer au thème clair';
        bouton.setAttribute('aria-pressed', clair ? 'true' : 'false');
    }

    appliquer(themeMemorise());

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
```

| Élément | Explication |
|---|---|
| `(function () { ... })();` | Tout le code est enfermé dans une fonction exécutée immédiatement : ses variables restent privées et ne risquent pas d'entrer en conflit avec un autre script. |
| `var CLE = 'entrevilles-theme';` | Le nom sous lequel le choix est enregistré. En JavaScript, les variables se déclarent avec `var` (ou `let`/`const`) et n'ont pas de `$`. |
| `document.documentElement` | La balise `<html>` de la page. C'est sur elle que l'on pose `data-theme`. |
| `localStorage` | Une petite mémoire du navigateur, propre à chaque site, qui survit à la fermeture de l'onglet. `getItem` lit, `setItem` écrit. Le `try { } catch (e) { }` protège des navigateurs qui l'interdisent (navigation privée stricte) : dans ce cas on continue sans mémoriser. |
| `appliquer(theme)` | Pose ou retire l'attribut `data-theme="clair"` sur `<html>` ; le CSS fait le reste (section 11.1). |
| `themeActuel()` | Lit l'attribut pour savoir dans quel thème on est. |
| `mettreAJourBouton(bouton)` | Change le texte du bouton pour annoncer l'action possible, et `aria-pressed` pour les lecteurs d'écran. |
| `appliquer(themeMemorise());` | Exécuté **immédiatement** au chargement du script : comme celui-ci est placé dans `<head>`, le thème mémorisé est appliqué avant que la page ne s'affiche. Sans cela, on verrait un flash sombre avant le passage au clair. |
| `document.addEventListener('DOMContentLoaded', ...)` | « Quand la page sera entièrement construite, exécute ceci. » Indispensable : au moment où le script tourne, le bouton de la barre n'existe pas encore. |
| `document.getElementById('basculeTheme')` | Retrouve le bouton par son `id`. S'il est absent (page d'erreur sans barre), on s'arrête sans planter. |
| `bouton.addEventListener('click', ...)` | À chaque clic : calcule le thème opposé, l'applique, le mémorise, met le bouton à jour. |

**Le bug d'origine, expliqué :** la première version du bouton ne fonctionnait
pas pour trois raisons cumulées. Le bouton `#basculeTheme` attendu par le script
n'avait jamais été ajouté à la barre de navigation ; le bloc `[data-theme="clair"]`
avait disparu de la feuille de style lors d'une mise à jour qui avait aussi
effacé par accident les 270 dernières lignes du CSS ; et une règle orpheline
utilisait une variable inexistante (`--lagon-fonce`) tout en agrandissant tous
les titres de cartes. La leçon : un thème repose sur **trois** pièces qui
doivent exister ensemble, le bouton dans le HTML, le script qui pose
l'attribut, et le bloc CSS qui réagit à cet attribut.

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

Ces observations viennent de la lecture du code. Les premières ont été
**corrigées** lors de l'ajout des rôles et de l'espace d'administration : elles
restent listées parce que comprendre le problème et sa correction est
instructif.

### 13.1 Problèmes corrigés

| Problème d'origine | Correction apportée |
|---|---|
| **La configuration par `.env` ne fonctionnait pas** : `config.php` remplissait `putenv()` mais `SupabaseClient` lisait `$GLOBALS['env']`. | `config.php` remplit maintenant `$env` **et** `putenv()` ; `SupabaseClient::init()` retombe sur `getenv()` si besoin (4.3, 5.5). |
| **`creer_admin.php` chargeait un fichier disparu** (`Database.php`). | Réécrit avec `Utilisateur::creer()` et le rôle `super_admin` (10.5). |
| **Avertissement possible** sur `$_POST['ville_id'] !== ''` si le champ manquait. | Lecture via `champEntier()`, qui gère l'absence (5.3). |
| **Pas de protection CSRF.** | Jeton par session (`Csrf`), champ caché dans tous les formulaires, vérification centrale dans le routeur (5.6, 5.2). |
| **Session non renouvelée à la connexion.** | `session_regenerate_id(true)` dans `Auth::connecter()` (5.4). |
| **Arrêts brutaux par `die()`** dans `SupabaseClient` et `Auth`. | Exceptions `SupabaseException` attrapées par `index.php` (page 500) ou par les contrôleurs (message flash) ; page 403 propre (5.8, 4.1). |
| **Ligne sans effet** dans `MembreEquipe::parUtilisateur`. | Supprimée. |
| **Méthodes prêtes mais non branchées** (création d'épreuves, d'équipes, membres, participations…). | Toutes utilisées par l'espace d'administration (section 8). |
| **Email non vérifié** à l'inscription. | `filter_var(..., FILTER_VALIDATE_EMAIL)` à l'inscription et dans l'administration. |
| **Bouton de thème sans effet** : script présent mais bouton absent, bloc CSS clair perdu, variable `--lagon-fonce` inexistante. | Bouton ajouté à la barre, palette claire complète, script chargé dans `<head>`, règle orpheline supprimée (11.1, 11.3). |
| **Bannière d'accueil sans style** : les classes `.hero` n'existaient pas dans le CSS. | Bannière dessinée (dégradé, halos, boutons d'action). |
| **Texte invisible au survol des liens-boutons** : `a:hover` mettait le texte en cyan sur fond cyan. | `.bouton:hover` fixe la couleur du texte. |
| **Anciennes versions du CSS et du JS gardées 30 jours** par les navigateurs (réglage de cache de l'hébergeur) : après une mise à jour, le bouton de thème semblait ne pas fonctionner. | `Format::asset()` ajoute la date de modification à l'adresse des fichiers statiques (5.9) ; les pages d'erreur chargent aussi le thème. |

### 13.2 Points restants

1. **Les erreurs sont affichées à l'écran** (`display_errors = 1` dans
   `config.php`). Sur un site public, cela révèle des chemins internes. À passer
   à `'0'` en ligne (le `try/catch` d'`index.php` affiche déjà une page propre).
2. **La clé `service_role`** donne tous les droits sur la base. Elle ne doit
   exister que sur le serveur, dans `env.local.php`, protégé par `.htaccess`.
3. **Code en double :** `Epreuve::aplatir()`, `Equipe::aplatir()` et
   `Utilisateur::aplatir()` se ressemblent beaucoup. Un **trait** PHP (morceau de
   classe réutilisable) permettrait de n'écrire l'aplatissement qu'une fois.
4. **La ville d'un manager est lue en session.** Si un super admin change la
   ville d'un manager pendant que celui-ci est connecté, le manager continue de
   voir son ancienne ville jusqu'à sa prochaine connexion. Recharger le compte
   depuis la base à chaque requête corrigerait cela, au prix d'une requête
   supplémentaire.
5. **Le tableau de bord compte en chargeant des listes entières**
   (`count(Ville::toutes())`). Négligeable pour un championnat, mais avec des
   milliers de lignes on demanderait plutôt à PostgREST de compter
   (en-tête `Prefer: count=exact`).
6. **La page « Ma ville » fait une requête par équipe** pour trouver les
   inscriptions en attente. Là encore acceptable à cette échelle ; une vue SQL
   ferait mieux.
7. **Le routeur ne protège pas les caractères spéciaux** des chemins
   (`preg_quote`). Sans conséquence avec les routes actuelles.
8. **Pas de pagination** dans les listes d'administration.
9. **Migration à exécuter** : sans `migration_roles.sql`, la base peut refuser
   les rôles `manager` et `super_admin` si une contrainte limite la colonne
   `role`. Les anciens comptes `admin` continuent de fonctionner comme super
   admins en attendant.

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
| **Constante de classe** | Valeur fixe nommée dans une classe (`Auth::ROLE_MANAGER`) ; évite les fautes de frappe sur des chaînes répétées. |
| **Constructeur** | Méthode `__construct()` exécutée automatiquement à la création d'un objet. |
| **Déstructuration** | `[$a, $b] = $liste;` range d'un coup plusieurs valeurs d'une liste dans des variables. |
| **Exception** | Signal d'erreur levé par `throw` et attrapé par `try { } catch { }` ; permet de gérer l'erreur là où l'on sait quoi faire. |
| **Fonction fléchée** | `fn($x) => $x * 2` : fonction anonyme sur une ligne, très utilisée avec `array_map` et `array_filter`. |
| **Jeton CSRF** | Chaîne secrète liée à la session, glissée dans chaque formulaire pour prouver qu'il vient bien de notre site. |
| **Message flash** | Message stocké en session pour être affiché une seule fois sur la page suivante. |
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
| **JavaScript** | Langage exécuté dans le navigateur (et non sur le serveur comme PHP) ; sert ici au bouton de thème. |
| **JSON** | Format texte pour échanger des données structurées (`{"id": 1, "nom": "X"}`). |
| **localStorage** | Petite mémoire du navigateur, propre à chaque site, où une page peut enregistrer des réglages (ici le thème choisi). |
| **Événement** | Quelque chose qui se produit dans la page (clic, fin de chargement) et auquel du JavaScript peut réagir avec `addEventListener`. |
| **Pseudo-élément** | Calque dessiné par le CSS sans balise HTML (`::before`, `::after`), utilisé ici pour les halos de la bannière et le filet des cartes. |
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
| **Trait** | Morceau de classe (méthodes) que plusieurs classes peuvent inclure avec `use`, pour partager du code sans héritage. |
| **Tableau (array)** | Une liste ou une fiche de valeurs. |
| **Tableau associatif** | Un tableau dont les cases ont un nom (`['nom' => 'X']`). |
| **Variable** | Une boîte nommée contenant une valeur, précédée de `$` en PHP. |
| **Vue (MVC)** | Le gabarit HTML qui affiche les données. |
| **Vue (SQL)** | Une requête enregistrée dans la base, lisible comme une table. |
| **XSS** | Injection de code via des données affichées ; contrée par `htmlspecialchars`. |
