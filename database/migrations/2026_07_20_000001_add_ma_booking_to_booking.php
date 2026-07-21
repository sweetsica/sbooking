<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->string('ma_booking', 40)->nullable()->unique()->after('id');
        });

        // Backfill record cũ theo (yyMMdd created_at + id 6 số).
        DB::table('booking')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $r) {
                $ymd = $r->created_at ? date('ymd', strtotime($r->created_at)) : date('ymd');
                DB::table('booking')->where('id', $r->id)->update([
                    'ma_booking' => sprintf('BKG-%s-%06d', $ymd, $r->id),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropUnique(['ma_booking']);
            $table->dropColumn('ma_booking');
        });
    }
};
