<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bac_si_tu_van', function (Blueprint $table) {
            $table->id();
            $table->foreignId('co_so_id')->constrained('co_so')->cascadeOnDelete();
            $table->string('ten');
            $table->string('chuc_danh')->nullable();
            $table->unsignedSmallInteger('thoi_gian_kham')->default(20);
            $table->time('gio_bat_dau')->default('08:00:00');
            $table->time('gio_ket_thuc')->default('17:00:00');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('ca_kham', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bac_si_tu_van_id')->constrained('bac_si_tu_van')->cascadeOnDelete();
            $table->time('gio_bat_dau');
            $table->time('gio_ket_thuc');
            $table->unsignedSmallInteger('thu_tu')->default(0);
            $table->timestamps();
        });

        Schema::create('lich_hen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('co_so_id')->constrained('co_so')->cascadeOnDelete();
            $table->foreignId('khach_hang_id')->constrained('khach_hang')->cascadeOnDelete();
            $table->foreignId('bac_si_tu_van_id')->nullable()->constrained('bac_si_tu_van')->nullOnDelete();
            $table->foreignId('ca_kham_id')->nullable()->constrained('ca_kham')->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('ngay_hen');
            $table->string('nguon')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->enum('trang_thai', ['cho_duyet', 'da_duyet', 'tu_choi'])->default('cho_duyet');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_hen');
        Schema::dropIfExists('ca_kham');
        Schema::dropIfExists('bac_si_tu_van');
    }
};
