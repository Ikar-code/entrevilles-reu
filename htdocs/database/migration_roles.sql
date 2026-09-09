-- ============================================================
--  Migration : rôles super_admin / manager de ville
--  À exécuter UNE FOIS dans Supabase → SQL Editor,
--  après migration_mot_de_passe.sql.
--
--  Rôles après migration :
--    joueur      : compte standard (inscription publique)
--    manager     : gère les équipes/membres de SA ville (ville_id obligatoire)
--    super_admin : gère tout
--  L'ancien rôle "admin" est converti en "super_admin".
-- ============================================================

-- 1. Retire une éventuelle contrainte CHECK qui limitait les valeurs de "role"
--    (son nom dépend du schéma d'origine : on la cherche dynamiquement).
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

-- 2. Les anciens "admin" deviennent des super administrateurs
UPDATE utilisateur SET role = 'super_admin' WHERE role = 'admin';

-- 3. Nouvelles règles : valeurs autorisées, et un manager doit avoir une ville
ALTER TABLE utilisateur DROP CONSTRAINT IF EXISTS utilisateur_role_check;
ALTER TABLE utilisateur
    ADD CONSTRAINT utilisateur_role_check
    CHECK (role IN ('joueur', 'manager', 'super_admin'));

ALTER TABLE utilisateur DROP CONSTRAINT IF EXISTS utilisateur_manager_ville_check;
ALTER TABLE utilisateur
    ADD CONSTRAINT utilisateur_manager_ville_check
    CHECK (role <> 'manager' OR ville_id IS NOT NULL);

-- ------------------------------------------------------------
-- Si la colonne "role" est un type ENUM (et non VARCHAR/TEXT), les
-- instructions ci-dessus échouent. Dans ce cas, exécuter à la place,
-- UNE instruction à la fois (nom du type visible dans Table Editor) :
--   ALTER TYPE <nom_du_type_role> ADD VALUE IF NOT EXISTS 'manager';
--   ALTER TYPE <nom_du_type_role> ADD VALUE IF NOT EXISTS 'super_admin';
--   UPDATE utilisateur SET role = 'super_admin' WHERE role = 'admin';
-- ------------------------------------------------------------
