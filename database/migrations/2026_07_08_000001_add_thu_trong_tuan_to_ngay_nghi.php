<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lặp theo thứ trong tuần cho ngày nghỉ.
     * thu_trong_tuan = CSV các thứ ISO (1=Thứ 2 … 7=Chủ nhật) mà bản ghi nghỉ áp dụng,
     * trong khoảng [tu_ngay, den_ngay]. NULL/'' = áp dụng MỌI ngày trong khoảng (như cũ).
     * Ví dụ Bác Biên chỉ làm thứ 6 → nghỉ các thứ "1,2,3,4,6,7".
     */
    public function up(): void
    {
        Schema::table('ngay_nghi', function (Blueprint $table) {
            $table->string('thu_trong_tuan')->nullable()->after('ca');
        });
    }

    public function down(): void
    {
        Schema::table('ngay_nghi', function (Blueprint $table) {
            $table->dropColumn('thu_trong_tuan');
        });
    }
};
