<?php
/**
 * Table "utilisateur" : les comptes (joueur, manager de ville, super admin).
 * Le mot de passe n'est stocké que sous forme de hash (password_hash).
 */
class Utilisateur
{
    /** Colonnes renvoyées aux pages d'administration : jamais le hash du mot de passe */
    private const COLONNES_PUBLIQUES = 'id,email,nom_compte,role,ville_id,ville:ville_id(nom)';

    /** Aplati l'objet imbriqué ville:{nom} en ville_nom */
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

    /** Tous les comptes avec le nom de leur ville, triés par nom de compte */
    public static function tous(): array
    {
        $lignes = SupabaseClient::select('utilisateur', self::COLONNES_PUBLIQUES, [], 'nom_compte.asc');
        return array_map([self::class, 'aplatir'], $lignes);
    }

    /** Comptes ayant un rôle donné (ex. tous les managers) */
    public static function parRole(string $role): array
    {
        $lignes = SupabaseClient::select('utilisateur', self::COLONNES_PUBLIQUES, ['role' => 'eq.' . $role], 'nom_compte.asc');
        return array_map([self::class, 'aplatir'], $lignes);
    }

    /**
     * Comptes rattachés à une ville.
     * $inclureSansVille = true ajoute aussi les comptes sans ville (utile pour
     * qu'un manager puisse recruter un joueur qui n'a pas encore choisi de ville).
     */
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

    /** Modifie nom_compte, email, role et/ou ville_id ($donnees : clés parmi ces colonnes) */
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

    /** Vérifie email + mot de passe, renvoie l'utilisateur si ok, sinon null */
    public static function verifierIdentifiants(string $email, string $motDePasseClair): ?array
    {
        $utilisateur = self::trouverParEmail($email);
        if ($utilisateur && $utilisateur['mot_de_passe'] && password_verify($motDePasseClair, $utilisateur['mot_de_passe'])) {
            return $utilisateur;
        }
        return null;
    }
}
