<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix the model_has_roles and model_has_permissions tables
     * so that model_id uses the native PostgreSQL uuid type.
     *
     * The previous migration set model_id to string(36), which creates
     * a varchar column in PostgreSQL. But users.id is uuid type, and
     * PostgreSQL cannot implicitly compare uuid with varchar.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE model_has_roles ALTER COLUMN model_id TYPE UUID USING model_id::uuid');
            DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN model_id TYPE UUID USING model_id::uuid');
        } elseif ($driver === 'mysql') {
            // MySQL handles this fine with the existing string(36) type
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE model_has_roles ALTER COLUMN model_id TYPE VARCHAR(36)');
            DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN model_id TYPE VARCHAR(36)');
        }
    }
};
