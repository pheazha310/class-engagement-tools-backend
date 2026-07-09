<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('country')->default('Cambodia');
            $table->string('province');
            $table->string('school_name');
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('province');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
