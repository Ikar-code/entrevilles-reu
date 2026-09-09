<?php
/**
 * Gestion de l'authentification via les sessions PHP natives.
 * Pas de dépendance à Supabase Auth : tout est géré ici.
 *
 * Trois rôles :
 *   - super_admin : gère tout (villes, sports, utilisateurs, épreuves, équipes…)
 *   - manager     : gère les équipes et membres de SA ville (ville_id obligatoire)
 *                   et inscrit ses équipes aux épreuves
 *   - joueur      : compte standard créé par l'inscription publique
 * L'ancien rôle "admin" est accepté comme synonyme de super_admin.
 */
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

    /** Rôle de la personne connectée, ou null si personne n'est connecté */
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

    /** Bloque l'accès à la page si pas connecté */
    public static function exigerConnexion(): void
    {
        if (!self::estConnecte()) {
            header('Location: /login');
            exit;
        }
    }

    /** Bloque l'accès si ni super admin ni manager */
    public static function exigerAdmin(): void
    {
        self::exigerConnexion();
        if (!self::estAdmin()) {
            self::refuser('Cette page est réservée aux administrateurs et aux managers de ville.');
        }
    }

    /** Bloque l'accès si pas super admin */
    public static function exigerSuperAdmin(): void
    {
        self::exigerConnexion();
        if (!self::estSuperAdmin()) {
            self::refuser('Cette page est réservée au super administrateur.');
        }
    }

    /** Bloque l'accès si pas manager de ville */
    public static function exigerManager(): void
    {
        self::exigerConnexion();
        if (!self::estManager()) {
            self::refuser('Cette page est réservée aux managers de ville.');
        }
    }

    /** Bloque l'accès si le compte connecté ne gère pas cette ville */
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
