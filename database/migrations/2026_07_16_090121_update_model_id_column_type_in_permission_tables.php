<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $modelMorphKey = $columnNames['model_morph_key'];

        // Clear existing data to avoid casting issues
        DB::table($tableNames['model_has_permissions'])->truncate();
        DB::table($tableNames['model_has_roles'])->truncate();

        // Update model_has_permissions table
        DB::statement("ALTER TABLE {$tableNames['model_has_permissions']} ALTER COLUMN {$modelMorphKey} DROP DEFAULT");
        DB::statement("ALTER TABLE {$tableNames['model_has_permissions']} ALTER COLUMN {$modelMorphKey} TYPE uuid USING NULL::uuid");
        DB::statement("ALTER TABLE {$tableNames['model_has_permissions']} ALTER COLUMN {$modelMorphKey} SET NOT NULL");

        // Update model_has_roles table
        DB::statement("ALTER TABLE {$tableNames['model_has_roles']} ALTER COLUMN {$modelMorphKey} DROP DEFAULT");
        DB::statement("ALTER TABLE {$tableNames['model_has_roles']} ALTER COLUMN {$modelMorphKey} TYPE uuid USING NULL::uuid");
        DB::statement("ALTER TABLE {$tableNames['model_has_roles']} ALTER COLUMN {$modelMorphKey} SET NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $modelMorphKey = $columnNames['model_morph_key'];

        // Clear data before reverting
        DB::table($tableNames['model_has_permissions'])->truncate();
        DB::table($tableNames['model_has_roles'])->truncate();

        // Revert model_has_permissions table
        DB::statement("ALTER TABLE {$tableNames['model_has_permissions']} ALTER COLUMN {$modelMorphKey} TYPE bigint USING NULL::bigint");
        DB::statement("ALTER TABLE {$tableNames['model_has_permissions']} ALTER COLUMN {$modelMorphKey} SET NOT NULL");

        // Revert model_has_roles table
        DB::statement("ALTER TABLE {$tableNames['model_has_roles']} ALTER COLUMN {$modelMorphKey} TYPE bigint USING NULL::bigint");
        DB::statement("ALTER TABLE {$tableNames['model_has_roles']} ALTER COLUMN {$modelMorphKey} SET NOT NULL");
    }
};