<?php
class Equipe
{
    private const EMBED = '*,ville:ville_id(nom),sport:sport_id(nom)';

    /** Aplati les objets imbriqués ville:{nom} et sport:{nom} en ville_nom / sport_nom */
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

    /** Équipes d'une ville (espace manager) */
    public static function parVille(int $villeId): array
    {
        $lignes = SupabaseClient::select('equipe', self::EMBED, ['ville_id' => 'eq.' . $villeId], 'nom.asc');
        return array_map([self::class, 'aplatir'], $lignes);
    }

    /** Équipes pratiquant un sport (sert à vérifier avant de supprimer un sport) */
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
