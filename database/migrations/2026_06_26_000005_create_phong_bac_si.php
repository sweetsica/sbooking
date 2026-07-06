<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phong_bac_si', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phong_id')->constrained('phong')->cascadeOnDelete();
            $table->foreignId('bac_si_id')->constrained('bac_si')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['phong_id', 'bac_si_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phong_bac_si');
    }
};
