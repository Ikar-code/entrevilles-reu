<?php
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
