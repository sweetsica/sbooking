<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ktv', function (Blueprint $table) {
            if (! Schema::hasColumn('ktv', 'nhom')) {
                $table->string('nhom', 50)->nullable()->after('ten');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ktv', function (Blueprint $table) {
            $table->dropColumn('nhom');
        });
    }
};
