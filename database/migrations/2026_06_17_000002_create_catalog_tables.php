<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Điều dưỡng / Bác sĩ — gắn với cơ sở
        Schema::create('bac_si', function (Blueprint $table) {
            $table->id();
            $table->foreignId('co_so_id')->constrained('co_so')->cascadeOnDelete();
            $table->string('ten');
            $table->string('chuc_danh')->nullable();   // BS. / KTV. / Điều dưỡng
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Dịch vụ / Liệu pháp — đẩy vào form tạo mới
        Schema::create('dich_vu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('co_so_id')->nullable()->constrained('co_so')->cascadeOnDelete(); // null = dùng chung
            $table->string('ten');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Menu — đẩy vào form tạo mới dạng ô tick
        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('co_so_id')->nullable()->constrained('co_so')->cascadeOnDelete();
            $table->string('ten');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu');
        Schema::dropIfExists('dich_vu');
        Schema::dropIfExists('bac_si');
    }
};
