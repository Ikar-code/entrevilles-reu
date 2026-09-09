<?php
/**
 * Table d'association equipe <-> utilisateur (le "roster").
 * Une ligne est identifiée par le couple (equipe_id, utilisateur_id).
 */
class MembreEquipe
{
    /** Rôles possibles dans une équipe : valeur en base => libellé */
    public const ROLES = [
        'joueur'    => 'Joueur',
        'capitaine' => 'Capitaine',
    ];

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

        // Le capitaine en premier, puis tri alphabétique par nom de compte
        // (PostgREST ne permet pas de trier sur une expression booléenne côté requête)
        usort($lignes, function (array $a, array $b) {
            $capitaineA = $a['role_interne'] === 'capitaine' ? 0 : 1;
            $capitaineB = $b['role_interne'] === 'capitaine' ? 0 : 1;
            return $capitaineA <=> $capitaineB ?: strcmp((string) $a['nom_compte'], (string) $b['nom_compte']);
        });

        return $lignes;
    }

    /** Équipe(s) dont fait partie un utilisateur donné — utilisé par la page /profil */
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

    /** true si l'utilisateur fait déjà partie de l'équipe */
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

    /** Modifie rôle, pénalité et/ou récompense ($donnees : clés parmi role_interne, penalite, recompense) */
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

    /** Vide le roster d'une équipe (avant de supprimer l'équipe) */
    public static function retirerTous(int $equipeId): void
    {
        SupabaseClient::delete('membre_equipe', ['equipe_id' => 'eq.' . $equipeId]);
    }

    /** Retire un utilisateur de toutes ses équipes (avant de supprimer le compte) */
    public static function retirerUtilisateurPartout(int $utilisateurId): void
    {
        SupabaseClient::delete('membre_equipe', ['utilisateur_id' => 'eq.' . $utilisateurId]);
    }
}
