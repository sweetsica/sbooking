<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingBinhLuan extends Model
{
    protected $table = 'booking_binh_luan';

    protected $fillable = ['booking_id', 'user_id', 'noi_dung'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
