<?php
/**
 * Table "participation" : fusionne l'inscription d'une équipe à une épreuve
 * ET son résultat (score, classement) une fois l'épreuve jouée.
 * Une ligne est identifiée par le couple (epreuve_id, equipe_id).
 */
class Participation
{
    /** Statuts d'inscription : valeur en base => libellé */
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

    /** Épreuves auxquelles une équipe est inscrite, avec son résultat */
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

        // Les plus récentes en premier (tri en PHP : PostgREST ne trie pas sur une colonne embarquée)
        usort($lignes, fn(array $a, array $b) => strcmp((string) $b['epreuve_date'], (string) $a['epreuve_date']));
        return $lignes;
    }

    /** true si l'équipe est déjà inscrite à l'épreuve */
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

    public static function retirer(int $epreuveId, int $equipeId): void
    {
        SupabaseClient::delete('participation', [
            'epreuve_id' => 'eq.' . $epreuveId, 'equipe_id' => 'eq.' . $equipeId,
        ]);
    }

    /** Supprime toutes les participations d'une épreuve (avant de supprimer l'épreuve) */
    public static function retirerParEpreuve(int $epreuveId): void
    {
        SupabaseClient::delete('participation', ['epreuve_id' => 'eq.' . $epreuveId]);
    }

    /** Supprime toutes les participations d'une équipe (avant de supprimer l'équipe) */
    public static function retirerParEquipe(int $equipeId): void
    {
        SupabaseClient::delete('participation', ['equipe_id' => 'eq.' . $equipeId]);
    }

    /**
     * Classement général : somme des scores de toutes les équipes de chaque ville.
     * Nécessite la vue SQL "vue_classement_general" côté Supabase (voir migration
     * database/vue_classement_general.sql) car PostgREST ne fait pas de GROUP BY
     * à la volée sur une simple requête REST.
     */
    public static function classementGeneral(): array
    {
        return SupabaseClient::select('vue_classement_general', '*', [], 'total_points.desc');
    }
}
