<?php
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

    public static function parVille(int $villeId): array
    {
        $lignes = SupabaseClient::select('epreuve', self::EMBED, ['ville_id' => 'eq.' . $villeId], 'date_heure.desc');
        return array_map([self::class, 'aplatir'], $lignes);
    }

    public static function parSport(int $sportId): array
    {
        $lignes = SupabaseClient::select('epreuve', self::EMBED, ['sport_id' => 'eq.' . $sportId], 'date_heure.desc');
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
