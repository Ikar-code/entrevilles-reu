# Documentation pédagogique — Entrevilles-Reu

> Application web PHP (architecture MVC, sans framework ni Composer) qui gère un
> championnat sportif entre communes de La Réunion : villes, sports, équipes,
> membres, épreuves, participations et classement général.
> Les données sont stockées dans **Supabase** (PostgreSQL) et interrogées via
> son API REST (**PostgREST**) avec cURL.

---

## Sommaire

1. [Vue d'ensemble et cycle d'une requête](#1-vue-densemble)
2. [Arborescence commentée](#2-arborescence-commentée)
3. [Point d'entrée et configuration](#3-point-dentrée-et-configuration)
4. [Noyau — `app/Core`](#4-noyau--appcore)
5. [Routes — `app/routes.php`](#5-routes--approutesphp)
6. [Modèles — `app/Models`](#6-modèles--appmodels)
7. [Contrôleurs — `app/Controllers`](#7-contrôleurs--appcontrollers)
8. [Vues — `app/Views`](#8-vues--appviews)
9. [Base de données — `database/`](#9-base-de-données--database)
10. [Feuille de style — `assets/css/style.css`](#10-feuille-de-style--assetscssstylecss)
11. [Déploiement — `.github/workflows/deploy.yml`](#11-déploiement--githubworkflowsdeployyml)
12. [Variables globales et superglobales utilisées](#12-variables-globales-et-superglobales-utilisées)
13. [Points d'attention : bugs et améliorations possibles](#13-points-dattention--bugs-et-améliorations-possibles)
14. [Glossaire des concepts](#14-glossaire-des-concepts)

---

## 1. Vue d'ensemble

### Technologies

| Élément | Choix | Pourquoi |
|---|---|---|
| Langage | PHP ≥ 8.0 (utilise `str_starts_with`, `str_contains`, types `?array`) | Hébergement gratuit InfinityFree |
| Base de données | Supabase (PostgreSQL) via API REST PostgREST + cURL | `pdo_pgsql` indisponible sur InfinityFree |
| Authentification | Sessions PHP natives + `password_hash` / `password_verify` | Aucune dépendance à Supabase Auth |
| Serveur web | Apache + `.htaccess` (réécriture d'URL) | Standard mutualisé |
| Déploiement | GitHub Actions → FTP | Push sur `main` = mise en ligne |
| Front | HTML + CSS pur (thème sombre "esport") | Pas de JavaScript sauf un `confirm()` |

### Architecture : MVC + Front Controller

```
Navigateur ──▶ .htaccess ──▶ index.php (Front Controller)
                                 │
                                 ├─ config/config.php   (charge SUPABASE_URL/KEY, démarre la session)
                                 ├─ app/Core/autoload.php (charge les classes à la demande)
                                 ├─ app/routes.php       (déclare toutes les routes)
                                 │
                                 └─ Router::traiter()
                                        │
                                        ▼
                               Contrôleur::action()      ◀── Auth (vérifie la session)
                                        │
                                        ├─▶ Modèle::methode()  ──▶ SupabaseClient ──▶ Supabase (HTTP/JSON)
                                        │
                                        └─▶ $this->afficher('vue', [...])
                                                  │
                                                  └─▶ layout.php ─▶ navbar + vue + footer ─▶ HTML
```

### Cycle de vie d'une requête, exemple `GET /equipes/3`

1. Apache ne trouve pas de fichier `/equipes/3` → `.htaccess` redirige vers `index.php`.
2. `index.php` charge la config, l'autoloader et les routes, puis appelle `Router::traiter()`.
3. Le routeur transforme `/equipes/{id}` en regex `#^/equipes/([^/]+)$#`, qui matche `/equipes/3` → capture `"3"`.
4. Il instancie `EquipeController` et appelle `detail("3")`.
5. Le contrôleur appelle `Equipe::trouver(3)` et `MembreEquipe::parEquipe(3)`.
6. Chaque modèle appelle `SupabaseClient::select(...)`, qui envoie une requête HTTP GET à `https://<projet>.supabase.co/rest/v1/equipe?...` et décode le JSON.
7. Le contrôleur appelle `$this->afficher('equipes/detail', [...])`.
8. `Controller::afficher()` transforme le tableau en variables (`extract`) et inclut `layout.php`, qui inclut la vue.

---

## 2. Arborescence commentée

```
entrevilles-reu-main/
├── .github/workflows/deploy.yml     Déploiement FTP automatique
└── htdocs/                          Racine web (seul dossier lisible par PHP sur InfinityFree)
    ├── index.php                    Front controller : point d'entrée UNIQUE
    ├── .htaccess                    Réécriture : tout → index.php sauf fichiers existants
    ├── .env.example                 Modèle de fichier de config (à copier en .env)
    ├── assets/css/style.css         Feuille de style unique
    ├── config/
    │   ├── .htaccess                "Require all denied" : interdit l'accès HTTP direct
    │   └── config.php               Chargement des variables d'env + session
    ├── database/
    │   ├── .htaccess                Interdit l'accès HTTP direct
    │   ├── creer_admin.php          Script CLI (obsolète, voir §13) pour créer le 1er admin
    │   ├── migration_mot_de_passe.sql  Ajoute la colonne mot_de_passe + unicité email
    │   └── vue_classement_general.sql  Vue SQL pour le classement par ville
    └── app/
        ├── .htaccess                Interdit l'accès HTTP direct
        ├── routes.php               Table des routes
        ├── Core/                    Noyau technique (réutilisable dans n'importe quel projet)
        │   ├── autoload.php         Chargement automatique des classes
        │   ├── Router.php           Associe URL → contrôleur/méthode
        │   ├── Controller.php       Classe mère des contrôleurs (méthode afficher)
        │   ├── Auth.php             Session utilisateur, garde-fous d'accès
        │   └── SupabaseClient.php   Client HTTP vers l'API REST Supabase
        ├── Models/                  Accès aux données (une classe = une table)
        │   ├── Ville.php  Sport.php  Utilisateur.php  Equipe.php
        │   ├── MembreEquipe.php  Epreuve.php  Participation.php
        ├── Controllers/             Logique de chaque page
        │   ├── AccueilController.php   AuthController.php   ProfilController.php
        │   ├── EpreuveController.php   EquipeController.php
        │   ├── ClassementController.php  AdminController.php
        └── Views/                   Gabarits HTML (PHP en mode template)
            ├── partials/ layout.php  navbar.php  footer.php
            ├── erreur_404.php
            ├── accueil/index.php   auth/login.php   auth/inscription.php
            ├── profil/index.php    epreuves/planning.php  epreuves/detail.php
            ├── equipes/liste.php   equipes/detail.php    classement/index.php
            └── admin/tableau_de_bord.php   admin/villes/nouvelle.php
```

---

## 3. Point d'entrée et configuration

### `htdocs/index.php` — Front Controller

```php
require_once __DIR__ . '/config/config.php';   // 1. config + session
require_once __DIR__ . '/app/Core/autoload.php'; // 2. autoloader
require_once __DIR__ . '/app/routes.php';        // 3. déclaration des routes
Router::traiter();                               // 4. dispatch
```

- `__DIR__` : constante magique PHP = dossier du fichier courant (`htdocs/`).
- `require_once` : inclut le fichier une seule fois, erreur fatale s'il manque.
- L'ordre est important : les routes utilisent `XxxController::class`, ce qui ne
  charge pas la classe (simple chaîne), mais `Router` doit exister → l'autoloader
  doit être enregistré avant `routes.php`.

### `htdocs/config/config.php` — Configuration et session

| Variable | Type | Rôle |
|---|---|---|
| `$envLocalPhp` | string | Chemin vers `config/env.local.php` (fichier PHP de config, méthode recommandée en ligne). |
| `$envPath` | string | Chemin vers `htdocs/.env` (fichier texte `CLE=VALEUR`, pour le local). |
| `$lignes` | string[] | Lignes du `.env` sans retours à la ligne ni lignes vides (`file()` avec les flags `FILE_IGNORE_NEW_LINES \| FILE_SKIP_EMPTY_LINES`). |
| `$ligne` | string | Ligne courante de la boucle. |
| `$cle`, `$valeur` | string | Résultat de `explode('=', $ligne, 2)` : découpe sur le **premier** `=` seulement (limite 2), pour autoriser un `=` dans la valeur. |

Logique :
1. Si `env.local.php` existe → `require`. Ce fichier (absent du dépôt, à créer sur
   le serveur) doit définir un tableau **`$env`** :
   ```php
   <?php
   $env = ['SUPABASE_URL' => 'https://xxx.supabase.co', 'SUPABASE_KEY' => '...'];
   ```
   Comme `config.php` est inclus depuis `index.php` (portée globale), `$env`
   devient une variable globale lisible via `$GLOBALS['env']` (utilisée par `SupabaseClient`).
2. Sinon, si `.env` existe → chaque ligne non commentée (`#`) contenant `=` est
   passée à `putenv()` (variable d'environnement du processus). **Attention** :
   voir §13, ce chemin ne remplit pas `$GLOBALS['env']`.
3. `session_start()` uniquement si aucune session n'est active (`session_status() === PHP_SESSION_NONE`).
4. `error_reporting(E_ALL)` + `display_errors=1` : affiche toutes les erreurs
   (mode développement).

### `htdocs/.env.example`

Modèle à copier en `.env`. Deux clés :
- `SUPABASE_URL` : URL du projet, ex. `https://xxxx.supabase.co`.
- `SUPABASE_KEY` : clé **service_role** (clé serveur qui contourne les règles RLS ;
  ne jamais l'exposer côté navigateur).

### Fichiers `.htaccess`

| Fichier | Contenu | Effet |
|---|---|---|
| `htdocs/.htaccess` | `RewriteCond !-f`, `!-d`, `RewriteRule ^ index.php [L]` | Si l'URL ne correspond ni à un fichier (`-f`) ni à un dossier (`-d`) réel, tout est envoyé à `index.php`. Les fichiers CSS/images restent servis normalement. `[L]` = dernière règle. |
| `app/.htaccess`, `config/.htaccess`, `database/.htaccess` | `Require all denied` | Interdit toute lecture HTTP directe (ex. `/config/config.php`). PHP peut toujours les `require` en interne. |

---

## 4. Noyau — `app/Core`

### 4.1 `autoload.php` — Chargement automatique des classes

```php
spl_autoload_register(function (string $classe) { ... });
```

- **Rôle** : quand PHP rencontre une classe inconnue (`new Router`, `Ville::toutes()`),
  il appelle cette fonction anonyme avec le nom de la classe.
- `$dossiers = ['Core', 'Models', 'Controllers']` : dossiers explorés dans cet ordre.
- `$chemin` : `app/<dossier>/<NomClasse>.php`. Convention **1 classe = 1 fichier du même nom**.
- Dès qu'un fichier existe → `require` puis `return` (on s'arrête au premier trouvé).
- Pas de namespaces dans ce projet : les noms de classe sont globaux.

### 4.2 `Router` — Routeur

| Membre | Signature | Rôle |
|---|---|---|
| `$routes` | `private static array` | Liste des routes. Chaque entrée : `['methode', 'chemin', 'controleur', 'action']`. |
| `ajouter()` | `(string $methode, string $chemin, string $controleur, string $action): void` | Ajoute une route brute au tableau. |
| `get()` | `(string $chemin, string $controleur, string $action): void` | Raccourci pour `ajouter('GET', ...)`. |
| `post()` | `(string $chemin, string $controleur, string $action): void` | Raccourci pour `ajouter('POST', ...)`. |
| `traiter()` | `(): void` | Trouve la route correspondant à la requête et exécute l'action. Sinon page 404. |

Détail de `traiter()` :

| Variable locale | Contenu | Explication |
|---|---|---|
| `$methode` | `$_SERVER['REQUEST_METHOD']` | `GET` ou `POST`. |
| `$uri` | `parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)` | Chemin sans la query string (`/equipes/3?x=1` → `/equipes/3`). |
| `$uri` (suite) | `rtrim($uri, '/') ?: '/'` | Supprime le `/` final ; si le résultat est vide (URL racine), retombe sur `/`. L'opérateur `?:` (Elvis) renvoie la gauche si elle est "vraie", sinon la droite. |
| `$motif` | `preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $route['chemin'])` puis `'#^' . $motif . '$#'` | Remplace chaque `{param}` par un groupe capturant "tout sauf `/`", et ancre début/fin. |
| `$correspondances` | Tableau rempli par `preg_match` | Index 0 = match complet (retiré par `array_shift`), puis les paramètres capturés. |
| `$controleur` | `new $route['controleur']()` | Instanciation dynamique à partir du nom de classe (chaîne). Déclenche l'autoloader et le constructeur (ex. `AdminController` vérifie les droits ici). |

- `call_user_func_array([$controleur, $route['action']], $correspondances)` :
  appelle la méthode avec les paramètres capturés **sous forme de chaînes** → c'est
  pourquoi les actions déclarent `string $id` et font `(int) $id`.
- Aucune route ne matche → `http_response_code(404)` + inclusion de `erreur_404.php`.

### 4.3 `Controller` — Classe mère des contrôleurs

| Membre | Signature | Rôle |
|---|---|---|
| `afficher()` | `protected (string $vue, array $donnees = []): void` | Rend une vue à l'intérieur du layout commun. |

Fonctionnement :
1. `extract($donnees)` : chaque clé du tableau devient une variable locale
   (`['titre' => 'X']` → `$titre = 'X'`). C'est ainsi que les vues reçoivent leurs données.
2. `$cheminVue = app/Views/<vue>.php` : chemin du gabarit demandé (ex. `'equipes/liste'`).
3. `require layout.php` : le layout est inclus **dans la portée de cette méthode**,
   donc il "voit" `$titre`, `$cheminVue` et toutes les variables extraites, et
   les transmet à son tour à la vue qu'il inclut.

### 4.4 `Auth` — Authentification par session

Toutes les méthodes sont statiques. L'état est stocké dans `$_SESSION['utilisateur']`.

| Méthode | Signature | Rôle et détails |
|---|---|---|
| `connecter()` | `(array $utilisateur): void` | Copie en session **uniquement** `id`, `nom_compte`, `email`, `role`, `ville_id`. Le hash du mot de passe n'est jamais stocké en session. |
| `deconnecter()` | `(): void` | `unset($_SESSION['utilisateur'])` puis `session_destroy()` (supprime le fichier de session côté serveur). |
| `estConnecte()` | `(): bool` | `isset($_SESSION['utilisateur'])`. |
| `estAdmin()` | `(): bool` | Connecté **et** `role === 'admin'`. L'évaluation court-circuit (`&&`) évite l'accès à une clé inexistante. |
| `utilisateur()` | `(): ?array` | Retourne le tableau en session ou `null` (`??`). |
| `exigerConnexion()` | `(): void` | Garde-fou : si non connecté → redirection HTTP `Location: /login` + `exit` (indispensable, sinon le script continuerait). |
| `exigerAdmin()` | `(): void` | Appelle `exigerConnexion()` puis, si non admin → code 403 + `die()`. |

### 4.5 `SupabaseClient` — Client HTTP PostgREST

Remplace une ancienne classe `Database` (PDO). Toutes les tables Supabase sont
exposées automatiquement en REST : `https://<projet>.supabase.co/rest/v1/<table>`.

| Propriété | Type | Rôle |
|---|---|---|
| `$url` | `private static ?string` | URL de base du projet, sans `/` final. `null` tant que `init()` n'a pas été appelée (chargement paresseux). |
| `$key` | `private static ?string` | Clé API (service_role). |

| Méthode | Signature | Rôle |
|---|---|---|
| `init()` | `private static (): void` | Lit `$GLOBALS['env']['SUPABASE_URL']` et `['SUPABASE_KEY']` une seule fois. `die()` si l'une manque. |
| `requete()` | `private static (string $method, string $path, ?array $body = null, string $prefer = ''): array` | Cœur du client : envoie une requête HTTP et renvoie le JSON décodé. |
| `select()` | `public static (string $table, string $select = '*', array $filtres = [], string $ordre = ''): array` | SELECT. Renvoie une liste de lignes (tableaux associatifs). |
| `insert()` | `public static (string $table, array $donnees): array` | INSERT. Renvoie la ligne créée (avec son `id`). |
| `update()` | `public static (string $table, array $filtres, array $donnees): void` | UPDATE (`PATCH`) des lignes filtrées. |
| `delete()` | `public static (string $table, array $filtres): void` | DELETE des lignes filtrées. |
| `rpc()` | `public static (string $fonction, array $params = []): array` | Appelle une fonction PostgreSQL exposée sous `/rpc/<fonction>`. Non utilisée actuellement. |

Détail de `requete()` :

| Variable | Rôle |
|---|---|
| `$ch` | Handle cURL créé par `curl_init(url)`. |
| `$entetes` | En-têtes HTTP : `apikey`, `Authorization: Bearer <clé>` (les deux sont exigés par Supabase), `Content-Type: application/json`, et `Prefer` si fourni. |
| `CURLOPT_CUSTOMREQUEST` | Méthode HTTP (GET/POST/PATCH/DELETE). |
| `CURLOPT_RETURNTRANSFER` | `true` = `curl_exec` **retourne** la réponse au lieu de l'afficher. |
| `CURLOPT_TIMEOUT` | 15 secondes max. |
| `CURLOPT_POSTFIELDS` | Corps JSON (`json_encode($body)`) seulement si `$body !== null`. |
| `$reponse` | Corps de la réponse, ou `false` en cas d'échec réseau → `die()` avec `curl_error`. |
| `$code` | Code HTTP (`curl_getinfo(..., CURLINFO_HTTP_CODE)`). `>= 400` → `die()` avec le message d'erreur PostgREST. |
| `$donnees` | `json_decode($reponse, true)` → tableau associatif (`true`). Si ce n'est pas un tableau → `[]`. |

Syntaxe PostgREST à connaître (utilisée par les modèles) :

| Syntaxe | Signification | Exemple |
|---|---|---|
| `select=*` | Toutes les colonnes | |
| `select=*,ville:ville_id(nom)` | **Embed** : suit la clé étrangère `ville_id`, renvoie un sous-objet nommé `ville` contenant `nom` | `{"id":1,"nom":"X","ville":{"nom":"Saint-Denis"}}` |
| `colonne=eq.5` | Filtre égalité | `['id' => 'eq.5']` |
| `order=nom.asc` | Tri croissant | `order=date_heure.desc` |
| `order=classement.asc.nullslast` | Tri avec les `NULL` à la fin | |
| `Prefer: return=representation` | Demande à PostgREST de renvoyer la ligne insérée | utilisé par `insert()` |

`select()` construit l'URL avec `http_build_query($params)`, qui encode
proprement les caractères spéciaux (`*` → `%2A`, `,` → `%2C`, `@` → `%40`).

---

## 5. Routes — `app/routes.php`

| Méthode | Chemin | Contrôleur | Action | Accès |
|---|---|---|---|---|
| GET | `/` | `AccueilController` | `index` | Public |
| GET | `/login` | `AuthController` | `afficherLogin` | Public |
| POST | `/login` | `AuthController` | `traiterLogin` | Public |
| GET | `/inscription` | `AuthController` | `afficherInscription` | Public |
| POST | `/inscription` | `AuthController` | `traiterInscription` | Public |
| GET | `/deconnexion` | `AuthController` | `deconnexion` | Public |
| GET | `/profil` | `ProfilController` | `index` | Connecté |
| GET | `/planning` | `EpreuveController` | `planning` | Public |
| GET | `/epreuves/{id}` | `EpreuveController` | `detail` | Public |
| GET | `/equipes` | `EquipeController` | `liste` | Public |
| GET | `/equipes/{id}` | `EquipeController` | `detail` | Public |
| GET | `/classement` | `ClassementController` | `index` | Public |
| GET | `/admin` | `AdminController` | `tableauDeBord` | Admin |
| GET | `/admin/villes/nouvelle` | `AdminController` | `nouvelleVille` | Admin |
| POST | `/admin/villes` | `AdminController` | `creerVille` | Admin |
| POST | `/admin/villes/{id}/supprimer` | `AdminController` | `supprimerVille` | Admin |

Convention : `GET` affiche un formulaire, `POST` le traite (pattern
**PRG — Post/Redirect/Get** : après un POST réussi, on redirige pour éviter un
double envoi au rafraîchissement).

---

## 6. Modèles — `app/Models`

Conventions communes :
- Une classe par table, **méthodes statiques** (pas d'objet à instancier).
- Retour = tableau associatif PHP (une ligne) ou liste de tableaux (plusieurs lignes), jamais d'objet.
- `trouver(int $id): ?array` renvoie `null` si introuvable (`$resultats[0] ?? null`).
- `creer(...): int` renvoie l'`id` généré (`(int) $ligne['id']`).
- Les filtres sont écrits en syntaxe PostgREST : `'eq.' . $id`.
- Les méthodes `aplatir()` transforment les sous-objets d'embed en colonnes plates
  pour simplifier les vues (`$ligne['ville']['nom']` → `$ligne['ville_nom']`).

### 6.1 `Ville` — table `ville` (`id`, `nom`)

| Méthode | Signature | Rôle |
|---|---|---|
| `toutes()` | `(): array` | Toutes les villes triées par nom. |
| `trouver()` | `(int $id): ?array` | Une ville par id. |
| `creer()` | `(string $nom): int` | Insère, renvoie l'id. |
| `modifier()` | `(int $id, string $nom): void` | Renomme. (Non utilisée par un contrôleur pour l'instant.) |
| `supprimer()` | `(int $id): void` | Supprime. |

### 6.2 `Sport` — table `sport` (`id`, `nom`, `description`)

| Méthode | Signature | Rôle |
|---|---|---|
| `tous()` | `(): array` | Tous les sports triés par nom. |
| `trouver()` | `(int $id): ?array` | Un sport par id. |
| `creer()` | `(string $nom, ?string $description): int` | Insère. `?string` = chaîne ou `null`. |
| `supprimer()` | `(int $id): void` | Supprime. |

### 6.3 `Utilisateur` — table `utilisateur` (`id`, `email`, `nom_compte`, `role`, `ville_id`, `mot_de_passe`)

| Méthode | Signature | Rôle |
|---|---|---|
| `trouverParEmail()` | `(string $email): ?array` | Recherche par email (colonne unique). |
| `trouver()` | `(int $id): ?array` | Par id. |
| `creer()` | `(string $nomCompte, string $email, string $motDePasseClair, string $role = 'joueur', ?int $villeId = null): int` | Hache le mot de passe puis insère. |
| `verifierIdentifiants()` | `(string $email, string $motDePasseClair): ?array` | Renvoie l'utilisateur si email + mot de passe corrects, sinon `null`. |

Détails sécurité :
- `$hash = password_hash($motDePasseClair, PASSWORD_DEFAULT)` : algorithme bcrypt
  (par défaut), sel aléatoire intégré, résultat ≈ 60 caractères. Le mot de passe
  en clair n'est **jamais** stocké.
- `password_verify($clair, $hash)` : recalcule et compare de manière sûre.
- Dans `verifierIdentifiants`, la condition `$utilisateur && $utilisateur['mot_de_passe'] && password_verify(...)`
  gère les comptes sans mot de passe (colonne `NULL` avant la migration).

### 6.4 `Equipe` — table `equipe` (`id`, `nom`, `ville_id`, `sport_id`)

| Méthode | Signature | Rôle |
|---|---|---|
| `aplatir()` | `private static (array $ligne): array` | `ville.nom` → `ville_nom`, `sport.nom` → `sport_nom`, puis supprime les sous-tableaux. |
| `toutes()` | `(): array` | Toutes les équipes avec nom de ville et de sport, triées par nom. `array_map([self::class, 'aplatir'], $lignes)` applique `aplatir` à chaque ligne. |
| `trouver()` | `(int $id): ?array` | Une équipe aplatie. |
| `parVille()` | `(int $villeId): array` | Équipes d'une ville (sans embed). Non utilisée pour l'instant. |
| `creer()` | `(string $nom, int $villeId, int $sportId): int` | Insère. |
| `supprimer()` | `(int $id): void` | Supprime. |

### 6.5 `Epreuve` — table `epreuve` (`id`, `sport_id`, `ville_id`, `date_heure`, `statut`)

`statut` ∈ `a_venir`, `en_cours`, `terminee` (valeurs déduites des classes CSS `badge-*`).

| Méthode | Signature | Rôle |
|---|---|---|
| `aplatir()` | `private static (array $ligne): array` | Même principe que `Equipe::aplatir`. |
| `toutes()` | `(): array` | Toutes les épreuves, **plus récentes d'abord** (`date_heure.desc`). Page planning. |
| `trouver()` | `(int $id): ?array` | Une épreuve. |
| `aVenir()` | `(): array` | Filtre `statut = a_venir`, tri chronologique croissant. Page d'accueil. |
| `creer()` | `(int $sportId, int $villeId, ?string $dateHeure, string $statut = 'a_venir'): int` | Insère. |
| `changerStatut()` | `(int $id, string $statut): void` | Met à jour le statut. |
| `supprimer()` | `(int $id): void` | Supprime. |

### 6.6 `MembreEquipe` — table `membre_equipe` (`equipe_id`, `utilisateur_id`, `role_interne`, `penalite`, `recompense`)

Table d'association N‑N entre `equipe` et `utilisateur` (le "roster").
`role_interne` ∈ `joueur`, `capitaine`.

| Méthode | Signature | Rôle |
|---|---|---|
| `parEquipe()` | `(int $equipeId): array` | Membres d'une équipe avec `nom_compte` et `email` (embed `utilisateur:utilisateur_id(nom_compte,email)`), capitaine en premier puis ordre alphabétique. |
| `parUtilisateur()` | `(int $utilisateurId): array` | Équipes d'un utilisateur, avec `equipe_nom`, `ville_nom`, `sport_nom` (embed **imbriqué** : `equipe:equipe_id(nom,ville:ville_id(nom),sport:sport_id(nom))`). Page profil. |
| `ajouter()` | `(int $equipeId, int $utilisateurId, string $roleInterne = 'joueur'): void` | Ajoute un membre. |
| `definirPenalite()` | `(int $equipeId, int $utilisateurId, ?string $penalite): void` | Met à jour `penalite` (filtre sur les **deux** colonnes de la clé composite). |
| `definirRecompense()` | `(int $equipeId, int $utilisateurId, ?string $recompense): void` | Idem pour `recompense`. |
| `retirer()` | `(int $equipeId, int $utilisateurId): void` | Supprime le membre. |

Le tri de `parEquipe()` en détail :

```php
usort($lignes, function (array $a, array $b) {
    $capitaineA = $a['role_interne'] === 'capitaine' ? 0 : 1;   // 0 = capitaine passe devant
    $capitaineB = $b['role_interne'] === 'capitaine' ? 0 : 1;
    return $capitaineA <=> $capitaineB ?: strcmp((string) $a['nom_compte'], (string) $b['nom_compte']);
});
```
- `<=>` (spaceship) renvoie `-1`, `0` ou `1`.
- `?:` : si la comparaison capitaine donne `0` (égalité), on départage avec `strcmp` (ordre alphabétique).
- Fait en PHP car PostgREST ne sait pas trier sur une expression booléenne.

### 6.7 `Participation` — table `participation` (`epreuve_id`, `equipe_id`, `statut`, `score`, `classement`)

Fusionne l'**inscription** d'une équipe à une épreuve et son **résultat**.
`statut` ∈ `en_attente`, `validee`.

| Méthode | Signature | Rôle |
|---|---|---|
| `parEpreuve()` | `(int $epreuveId): array` | Participations d'une épreuve avec `equipe_nom` et `ville_nom`, triées par classement, `NULL` en dernier. |
| `inscrire()` | `(int $epreuveId, int $equipeId, string $statut = 'en_attente'): void` | Inscrit une équipe. |
| `saisirResultat()` | `(int $epreuveId, int $equipeId, int $score, int $classement): void` | Enregistre score et rang. |
| `validerInscription()` | `(int $epreuveId, int $equipeId): void` | Passe `statut` à `validee`. |
| `classementGeneral()` | `(): array` | Lit la **vue SQL** `vue_classement_general` triée par `total_points.desc`. |

---

## 7. Contrôleurs — `app/Controllers`

Tous héritent de `Controller` et utilisent `$this->afficher(vue, données)`.
Les actions reçoivent les paramètres d'URL en `string`.

### 7.1 `AccueilController`

| Action | Variables passées à la vue | Rôle |
|---|---|---|
| `index()` | `titre`, `epreuvesAVenir` (= `Epreuve::aVenir()`) | Page d'accueil. |

### 7.2 `AuthController`

| Action | Rôle |
|---|---|
| `afficherLogin()` | Affiche `auth/login` avec `erreur = null`. |
| `traiterLogin()` | Lit `$_POST['email']` (trim) et `$_POST['mot_de_passe']`. `Utilisateur::verifierIdentifiants()` → si `null`, ré-affiche le formulaire avec un message générique (ne dit pas si c'est l'email ou le mot de passe qui est faux). Sinon `Auth::connecter()` puis redirection `/`. |
| `afficherInscription()` | Affiche `auth/inscription` avec la liste des `villes`. |
| `traiterInscription()` | Validation en chaîne puis création du compte (voir ci-dessous). |
| `deconnexion()` | `Auth::deconnecter()` puis redirection `/`. |

Variables de `traiterInscription()` :

| Variable | Source | Validation |
|---|---|---|
| `$nomCompte` | `$_POST['nom_compte']` trim | obligatoire |
| `$email` | `$_POST['email']` trim | obligatoire, unique (`Utilisateur::trouverParEmail`) |
| `$motDePasse` | `$_POST['mot_de_passe']` | obligatoire, ≥ 8 caractères (`strlen`) |
| `$motDePasseConfirmation` | `$_POST['mot_de_passe_confirmation']` | doit être identique (`!==`) |
| `$villeId` | `$_POST['ville_id']` | `''` → `null`, sinon `(int)` |
| `$erreur` | calculée | `null` si tout est bon ; sinon premier message d'erreur rencontré (`if / elseif`). |
| `$id` | `Utilisateur::creer(...)` | id du nouveau compte (rôle forcé à `joueur`) |
| `$utilisateur` | `Utilisateur::trouver($id)` | rechargé depuis la base puis mis en session → redirection `/profil` |

Le motif `$_POST['x'] ?? ''` (null coalescing) évite une erreur "Undefined index"
si le champ est absent.

### 7.3 `ProfilController`

| Action | Rôle |
|---|---|
| `index()` | `Auth::exigerConnexion()` d'abord. Recharge l'utilisateur complet (`Utilisateur::trouver`) et ses équipes (`MembreEquipe::parUtilisateur`) à partir de l'id en session. Vue `profil/index` avec `utilisateur`, `equipes`. |

### 7.4 `EpreuveController`

| Action | Rôle |
|---|---|
| `planning()` | Vue `epreuves/planning` avec `epreuves = Epreuve::toutes()`. |
| `detail(string $id)` | `Epreuve::trouver((int) $id)`. Si `null` → 404 (code HTTP + page d'erreur + `return`). Sinon vue `epreuves/detail` avec `epreuve`, `participations`, et `titre = "Sport — Ville"`. |

### 7.5 `EquipeController`

| Action | Rôle |
|---|---|
| `liste()` | Vue `equipes/liste` avec `equipes = Equipe::toutes()`. |
| `detail(string $id)` | Même schéma que l'épreuve : `Equipe::trouver`, 404 si absent, sinon `equipe` + `membres = MembreEquipe::parEquipe`. |

### 7.6 `ClassementController`

| Action | Rôle |
|---|---|
| `index()` | Vue `classement/index` avec `classement = Participation::classementGeneral()`. |

### 7.7 `AdminController`

| Membre | Rôle |
|---|---|
| `__construct()` | Appelle `Auth::exigerAdmin()`. Comme le routeur fait `new AdminController()` avant d'appeler l'action, **toutes** les actions sont protégées automatiquement. |
| `tableauDeBord()` | Vue `admin/tableau_de_bord` avec `villes`, `sports`. |
| `nouvelleVille()` | Affiche le formulaire `admin/villes/nouvelle`. |
| `creerVille()` | `$nom = trim($_POST['nom'] ?? '')`. Vide → ré-affiche avec `erreur`. Sinon `Ville::creer($nom)` + redirection `/admin`. |
| `supprimerVille(string $id)` | `Ville::supprimer((int) $id)` + redirection `/admin`. |

Le CRUD des villes sert de modèle à reproduire pour sports, équipes, épreuves.

---

## 8. Vues — `app/Views`

Les vues sont du HTML avec des balises PHP courtes `<?= ... ?>` (équivalent à `<?php echo ... ?>`).
Règle d'or appliquée partout : **`htmlspecialchars()`** sur toute donnée affichée
(convertit `<`, `>`, `&`, `"` en entités) → protection contre les failles XSS.

### 8.1 Gabarits communs — `partials/`

| Fichier | Variables attendues | Rôle |
|---|---|---|
| `layout.php` | `$titre` (facultatif), `$cheminVue` (obligatoire, fourni par `Controller::afficher`) | Squelette HTML : `<head>` avec titre et CSS, `navbar`, `<main class="conteneur">` qui `require $cheminVue`, `footer`. |
| `navbar.php` | aucune (utilise `Auth::estConnecte()`, `Auth::estAdmin()`, `Auth::utilisateur()`) | Liens publics + liens conditionnels : Administration (admin), Profil + Déconnexion (connecté), sinon Connexion + Créer un compte. |
| `footer.php` | aucune | Pied de page statique. |
| `erreur_404.php` | aucune | Page complète autonome (pas de layout), utilisée par le routeur et les contrôleurs. |

Syntaxe alternative des structures de contrôle (`if (...): ... endif;`,
`foreach (...): ... endforeach;`) : lisible dans du HTML.

### 8.2 Tableau des vues

| Vue | Contrôleur / action | Variables utilisées | Contenu |
|---|---|---|---|
| `accueil/index.php` | `Accueil::index` | `$epreuvesAVenir` (`id`, `statut`, `sport_nom`, `ville_nom`, `date_heure`) | Bannière + grille de cartes des prochaines épreuves. Date formatée `(new DateTime($x))->format('d/m/Y à H:i')`. |
| `auth/login.php` | `Auth::afficherLogin` / `traiterLogin` | `$erreur` | Formulaire POST `/login` : `email`, `mot_de_passe`. |
| `auth/inscription.php` | `Auth::afficherInscription` / `traiterInscription` | `$erreur`, `$villes` ; relit `$_POST` pour pré-remplir | Formulaire POST `/inscription` : `nom_compte`, `email`, `ville_id` (select, `""` = aucune), `mot_de_passe`, `mot_de_passe_confirmation` (`minlength="8"` côté navigateur, revérifié côté serveur). |
| `profil/index.php` | `Profil::index` | `$utilisateur` (`nom_compte`, `email`, `role`), `$equipes` (`equipe_id`, `equipe_nom`, `ville_nom`, `sport_nom`, `role_interne`) | Carte identité + cartes des équipes, badge "Capitaine". |
| `epreuves/planning.php` | `Epreuve::planning` | `$epreuves` | Tableau Sport / Ville / Date / Statut ; `—` si date `NULL`. |
| `epreuves/detail.php` | `Epreuve::detail` | `$epreuve`, `$participations` (`classement`, `equipe_nom`, `ville_nom`, `score`, `statut`) | Fiche épreuve + tableau des équipes participantes. `(string) ($x ?? '—')` : cast nécessaire car `htmlspecialchars` attend une chaîne. |
| `equipes/liste.php` | `Equipe::liste` | `$equipes` (`id`, `nom`, `ville_nom`, `sport_nom`) | Grille de cartes. |
| `equipes/detail.php` | `Equipe::detail` | `$equipe`, `$membres` (`nom_compte`, `role_interne`, `penalite`, `recompense`) | Fiche équipe + tableau des membres. |
| `classement/index.php` | `Classement::index` | `$classement` (`ville_nom`, `total_points`) | Tableau ; le rang affiché est `$i + 1` (index du `foreach`). |
| `admin/tableau_de_bord.php` | `Admin::tableauDeBord` | `$villes`, `$sports` | Liste des villes avec un mini-formulaire POST de suppression par ligne (`onsubmit="return confirm(...)"` = seule ligne de JavaScript du projet) + liste des sports. |
| `admin/villes/nouvelle.php` | `Admin::nouvelleVille` / `creerVille` | `$erreur` | Formulaire POST `/admin/villes` : `nom`. |

---

## 9. Base de données — `database/`

### 9.1 Schéma déduit du code

Le script de création d'origine (`schema.sql` / `BDDEntreVilles_supabase.sql`,
mentionné dans les commentaires) **n'est pas dans le dépôt**. Voici le schéma
reconstitué à partir des modèles :

| Table | Colonnes | Remarques |
|---|---|---|
| `ville` | `id`, `nom` | |
| `sport` | `id`, `nom`, `description` | |
| `utilisateur` | `id`, `email` (unique), `nom_compte`, `role`, `ville_id` → `ville`, `mot_de_passe` | `role` ∈ `joueur`, `admin`. `mot_de_passe` ajouté par migration. |
| `equipe` | `id`, `nom`, `ville_id` → `ville`, `sport_id` → `sport` | |
| `membre_equipe` | `equipe_id` → `equipe`, `utilisateur_id` → `utilisateur`, `role_interne`, `penalite`, `recompense` | Clé composite. `role_interne` ∈ `joueur`, `capitaine`. |
| `epreuve` | `id`, `sport_id` → `sport`, `ville_id` → `ville`, `date_heure`, `statut` | `statut` ∈ `a_venir`, `en_cours`, `terminee`. |
| `participation` | `epreuve_id` → `epreuve`, `equipe_id` → `equipe`, `statut`, `score`, `classement` | `statut` ∈ `en_attente`, `validee`. `score`/`classement` `NULL` tant que non joué. |
| `vue_classement_general` (vue) | `ville_id`, `ville_nom`, `total_points` | Calculée, lecture seule. |

Relations : une ville a plusieurs équipes et plusieurs utilisateurs ; une équipe
appartient à une ville et à un sport ; `membre_equipe` relie équipes et
utilisateurs ; `participation` relie équipes et épreuves.

### 9.2 `vue_classement_general.sql`

```sql
CREATE OR REPLACE VIEW vue_classement_general AS
SELECT v.id AS ville_id, v.nom AS ville_nom, COALESCE(SUM(p.score), 0) AS total_points
FROM ville v
LEFT JOIN equipe e        ON e.ville_id = v.id
LEFT JOIN participation p ON p.equipe_id = e.id AND p.score IS NOT NULL
GROUP BY v.id, v.nom;
```

- `LEFT JOIN` : garde les villes sans équipe ni score (elles apparaissent avec 0).
- `COALESCE(SUM(...), 0)` : remplace `NULL` (aucun score) par `0`.
- Pourquoi une vue ? PostgREST ne fait pas de `GROUP BY` à la volée ; une vue est
  exposée comme une table et devient interrogeable par `SupabaseClient::select`.

### 9.3 `migration_mot_de_passe.sql`

- `ALTER TABLE utilisateur ADD COLUMN IF NOT EXISTS mot_de_passe VARCHAR(255);` :
  colonne pour le hash bcrypt (60 caractères ; 255 laisse de la marge pour d'autres algorithmes).
- `ADD CONSTRAINT utilisateur_email_unique UNIQUE (email);` : interdit deux comptes
  avec le même email au niveau base (double sécurité avec le contrôle PHP).

### 9.4 `creer_admin.php` — script d'amorçage (obsolète)

| Variable | Rôle |
|---|---|
| `$nomCompte`, `$email`, `$motDePasseClair` | Identifiants du premier admin (`admin@entrevilles-reu.re` / `changez-moi123`). |
| `$hash` | `password_hash(...)`. |
| `$pdo` | Connexion PDO via `Database::connexion()`. |
| `$stmt` | Requête préparée `INSERT ... ON CONFLICT (email) DO NOTHING` (idempotent : relancer ne crée pas de doublon). |

Ce script `require` `app/Core/Database.php`, **qui n'existe plus** (remplacé par
`SupabaseClient`). Il ne fonctionne donc plus en l'état, voir §13.

---

## 10. Feuille de style — `assets/css/style.css`

Thème "dashboard esport" : fond sombre, accents néon. Polices Google Fonts
`Rajdhani` (titres) et `Inter` (texte) chargées via `@import`.

### Variables CSS (`:root`)

| Variable | Valeur | Usage |
|---|---|---|
| `--fond` | `#0B0E14` | Fond de page |
| `--panneau` | `#12161F` | Fond des cartes, tableaux, formulaires |
| `--panneau-alt` | `#171C27` | Fond survol / en-têtes de tableau |
| `--bordure` | `rgba(255,255,255,0.08)` | Bordures discrètes |
| `--bordure-vive` | `rgba(255,255,255,0.16)` | Bordures au survol |
| `--texte` | `#E7E9F0` | Texte principal |
| `--texte-doux` | `#8B93A7` | Texte secondaire, labels |
| `--cyan` / `--cyan-vif` | `#2DE1C2` / `#4FF5D8` | Liens, accent principal, badge `a_venir` |
| `--violet` | `#8B5CF6` | Accent secondaire |
| `--or` | `#F5B942` | Badge `terminee` |
| `--rose` | `#FF4D6D` | Badge `en_cours`, capitaine |

Utilisation : `color: var(--cyan);`. Changer une valeur ici met à jour tout le site.

### Classes principales

| Classe | Rôle |
|---|---|
| `.conteneur` | Colonne centrée avec marges (contenu de `<main>`). |
| `.navbar`, `.navbar-logo`, `.navbar-liens` | Barre de navigation (flex, passe en colonne sous 640 px). |
| `.hero`, `.hero-contenu` | Bannière d'accueil. |
| `.grille-cartes`, `.carte` | Grille responsive de cartes. |
| `.badge`, `.badge-a_venir`, `.badge-en_cours`, `.badge-terminee` | Pastilles de statut. Le suffixe correspond **exactement** à la valeur `statut` en base, d'où `class="badge badge-<?= $statut ?>"`. |
| `.bouton`, `button` | Boutons/appels à l'action. |
| `.erreur` | Message d'erreur de formulaire. |
| `.etat-vide` | Bloc "aucune donnée" (défini mais non utilisé par les vues). |
| `.pied-page` | Footer. |
| `@media (max-width: 640px)` | Ajustements mobile. |

---

## 11. Déploiement — `.github/workflows/deploy.yml`

| Clé | Valeur | Explication |
|---|---|---|
| `on.push.branches` | `main` | Se déclenche à chaque push sur `main`. |
| `on.workflow_dispatch` | | Permet un lancement manuel depuis l'onglet Actions. |
| `actions/checkout@v4` | | Récupère le code. |
| `SamKirkland/FTP-Deploy-Action@v4.3.5` | | Envoie `./htdocs/` vers `./htdocs/` du serveur FTP. |
| `secrets.FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD` | Secrets GitHub | Jamais en clair dans le dépôt. |

Pourquoi tout dans `htdocs/` ? InfinityFree confine PHP (`open_basedir`) à ce
dossier ; les sous-dossiers sensibles sont protégés par `Require all denied`.

---

## 12. Variables globales et superglobales utilisées

| Variable | Où | Rôle |
|---|---|---|
| `$_SESSION['utilisateur']` | `Auth` | Tableau `id`, `nom_compte`, `email`, `role`, `ville_id` de l'utilisateur connecté. Absent = visiteur anonyme. |
| `$_POST['email']`, `['mot_de_passe']` | `AuthController::traiterLogin` | Formulaire de connexion. |
| `$_POST['nom_compte']`, `['email']`, `['ville_id']`, `['mot_de_passe']`, `['mot_de_passe_confirmation']` | `AuthController::traiterInscription`, vue inscription | Formulaire d'inscription. |
| `$_POST['nom']` | `AdminController::creerVille` | Formulaire nouvelle ville. |
| `$_SERVER['REQUEST_METHOD']`, `['REQUEST_URI']` | `Router::traiter` | Méthode et URL de la requête. |
| `$GLOBALS['env']` | `SupabaseClient::init` | Tableau de config défini par `config/env.local.php`. |
| `$env` | `config/env.local.php` (à créer) | Source de `$GLOBALS['env']`. |
| `__DIR__` | partout | Dossier du fichier courant ; permet des chemins absolus fiables. |
| `Router::$routes` | `Router` | Table des routes (statique, remplie par `routes.php`). |
| `SupabaseClient::$url`, `::$key` | `SupabaseClient` | Identifiants Supabase mis en cache. |

---

## 13. Points d'attention : bugs et améliorations possibles

Utile pour l'étude : ce sont des cas concrets de ce qu'il faut savoir repérer.

1. **Le chemin `.env` ne fonctionne pas.** `config.php` charge `.env` avec
   `putenv()`, mais `SupabaseClient::init()` lit `$GLOBALS['env']`, jamais
   `getenv()`. Avec un `.env` seul, l'application meurt sur "variables manquantes".
   Correctif possible dans `config.php` : remplir aussi `$env[trim($cle)] = trim($valeur);`,
   ou dans `init()` : `$GLOBALS['env']['SUPABASE_URL'] ?? getenv('SUPABASE_URL')`.
2. **`database/creer_admin.php` est cassé** : il inclut `app/Core/Database.php`
   qui n'existe plus. À réécrire avec `Utilisateur::creer('Administrateur', $email, $mdp, 'admin')`.
3. **Avertissement possible à l'inscription** : `$_POST['ville_id'] !== ''` sans
   `??` déclenche un warning si le champ est absent. Écrire `($_POST['ville_id'] ?? '') !== ''`.
4. **`display_errors = 1` en production** expose des chemins et messages internes.
   À désactiver en ligne (ou conditionner à un flag `APP_DEBUG`).
5. **Pas de protection CSRF** sur les formulaires POST (notamment la suppression
   de ville). Un jeton en session vérifié à chaque POST est la solution standard.
6. **Pas de `session_regenerate_id(true)` après connexion** : recommandé contre
   la fixation de session.
7. **Duplication** : `Epreuve::aplatir()` et `Equipe::aplatir()` sont identiques.
   Un trait ou une fonction utilitaire éviterait la répétition.
8. **Ligne inutile** dans `MembreEquipe::parUtilisateur` : `$ligne['equipe_id'] = $ligne['equipe_id'] ?? null;` ne change rien.
9. **Méthodes non encore utilisées** (`Ville::modifier`, `Equipe::parVille`,
   `Epreuve::creer/changerStatut/supprimer`, `Sport::creer/supprimer`,
   `MembreEquipe::ajouter/...`, `Participation::inscrire/saisirResultat/validerInscription`,
   `SupabaseClient::rpc`) : elles préparent le CRUD admin complet annoncé dans le tableau de bord.
10. **Clé `service_role`** : elle contourne toute règle de sécurité Supabase (RLS).
    Elle ne doit vivre que côté serveur, dans `env.local.php` protégé par `.htaccess`.
11. **Routeur** : les chemins ne sont pas passés par `preg_quote()`. Sans
    conséquence ici (aucun caractère spécial regex dans les routes), mais à garder
    en tête si on ajoute des routes avec `.` ou `+`.
12. **Gestion d'erreur par `die()`** dans `SupabaseClient` : simple mais brutal.
    Une exception (`throw new RuntimeException`) capturée par le front controller
    permettrait une page d'erreur propre.

---

## 14. Glossaire des concepts

| Terme | Définition courte |
|---|---|
| **MVC** | Modèle (données), Vue (affichage), Contrôleur (logique qui relie les deux). |
| **Front Controller** | Un seul fichier (`index.php`) reçoit toutes les requêtes et les distribue. |
| **Routeur** | Composant qui associe une URL + méthode HTTP à une action de contrôleur. |
| **Autoloader** | Fonction qui charge automatiquement le fichier d'une classe à sa première utilisation (`spl_autoload_register`). |
| **PostgREST** | Serveur qui expose une base PostgreSQL en API REST. Supabase l'utilise. |
| **Embed (PostgREST)** | Syntaxe `alias:cle_etrangere(colonnes)` pour joindre une table liée dans la même requête. |
| **RLS** | Row Level Security : règles d'accès par ligne dans PostgreSQL ; contournées par la clé `service_role`. |
| **cURL** | Bibliothèque PHP pour faire des requêtes HTTP. |
| **Session PHP** | Stockage côté serveur identifié par un cookie ; accessible via `$_SESSION`. |
| **Hash de mot de passe** | Empreinte irréversible (`password_hash`) ; on compare avec `password_verify`. |
| **XSS** | Injection de HTML/JS via des données affichées ; contrée par `htmlspecialchars`. |
| **CSRF** | Envoi d'un formulaire à l'insu de l'utilisateur ; contré par un jeton secret. |
| **PRG** | Post/Redirect/Get : rediriger après un POST réussi. |
| **`extract()`** | Transforme les clés d'un tableau en variables locales. |
| **`??`** | Null coalescing : `$a ?? 'x'` vaut `$a` s'il existe et n'est pas `null`, sinon `'x'`. |
| **`?:`** | Elvis : `$a ?: 'x'` vaut `$a` s'il est "vrai", sinon `'x'`. |
| **`<=>`** | Spaceship : comparaison renvoyant -1, 0 ou 1. |
| **`?string`, `?array`** | Type nullable : la valeur peut être du type indiqué **ou** `null`. |
| **Méthode statique** | Appelée sur la classe (`Ville::toutes()`) sans créer d'objet. |
| **Table d'association** | Table qui relie deux tables en N‑N (`membre_equipe`, `participation`). |
| **Vue SQL** | Requête enregistrée qui se comporte comme une table en lecture. |
| **`.htaccess`** | Fichier de configuration Apache par dossier (réécriture, accès). |
