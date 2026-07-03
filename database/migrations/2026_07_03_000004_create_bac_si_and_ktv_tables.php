<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bac_si')) {
            Schema::table('bac_si', function (Blueprint $table) {
                if (! Schema::hasColumn('bac_si', 'xuat_hien_moi_co_so')) {
                    $table->boolean('xuat_hien_moi_co_so')->default(false)->after('co_so_id');
                }
                if (! Schema::hasColumn('bac_si', 'nhan_tu_van')) {
                    $table->boolean('nhan_tu_van')->default(false)->after('xuat_hien_moi_co_so');
                }
                if (! Schema::hasColumn('bac_si', 'phut_tu_van')) {
                    $table->unsignedSmallInteger('phut_tu_van')->default(30)->after('nhan_tu_van');
                }
                if (! Schema::hasColumn('bac_si', 'nhan_kham_ls')) {
                    $table->boolean('nhan_kham_ls')->default(false)->after('phut_tu_van');
                }
                if (! Schema::hasColumn('bac_si', 'phut_kham_ls')) {
                    $table->unsignedSmallInteger('phut_kham_ls')->default(5)->after('nhan_kham_ls');
                }
                if (! Schema::hasColumn('bac_si', 'gio_bat_dau')) {
                    $table->string('gio_bat_dau', 5)->nullable()->after('phut_kham_ls');
                }
                if (! Schema::hasColumn('bac_si', 'gio_ket_thuc')) {
                    $table->string('gio_ket_thuc', 5)->nullable()->after('gio_bat_dau');
                }
            });
        } else {
            Schema::create('bac_si', function (Blueprint $table) {
                $table->id();
                $table->string('ten');
                $table->string('chuc_danh', 50)->nullable();
                $table->foreignId('co_so_id')->constrained('co_so')->cascadeOnDelete();
                $table->boolean('xuat_hien_moi_co_so')->default(false);
                $table->boolean('nhan_tu_van')->default(false);
                $table->unsignedSmallInteger('phut_tu_van')->default(30);
                $table->boolean('nhan_kham_ls')->default(false);
                $table->unsignedSmallInteger('phut_kham_ls')->default(5);
                $table->string('gio_bat_dau', 5)->nullable();
                $table->string('gio_ket_thuc', 5)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ktv')) {
            Schema::create('ktv', function (Blueprint $table) {
                $table->id();
                $table->string('ten');
                $table->foreignId('co_so_id')->constrained('co_so')->cascadeOnDelete();
                $table->string('gio_bat_dau', 5)->nullable();
                $table->string('gio_ket_thuc', 5)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ktv');

        if (Schema::hasTable('bac_si')) {
            Schema::table('bac_si', function (Blueprint $table) {
                $cols = ['xuat_hien_moi_co_so', 'nhan_tu_van', 'phut_tu_van', 'nhan_kham_ls', 'phut_kham_ls', 'gio_bat_dau', 'gio_ket_thuc'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('bac_si', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
