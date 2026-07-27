<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('hidden_at')->nullable()->after('read_at');
            $table->index(['notifiable_type', 'notifiable_id', 'hidden_at']);
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['notifiable_type', 'notifiable_id', 'hidden_at']);
            $table->dropColumn('hidden_at');
        });
    }
};
