<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('locations');
    }

    public function down(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('country')->default('Cambodia');
            $table->string('province');
            $table->string('school_name');
            $table->timestamps();
        });
    }
};
