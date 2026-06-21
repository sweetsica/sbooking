<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'chuc_danh',
        'username',
        'email',
        'password',
        'co_so_id',
        'phong_ban_id',
        'vai_tro_id',
        'is_admin',
        'is_tu_van',
        'thoi_gian_kham',
        'gio_bat_dau',
        'gio_ket_thuc',
    ];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    public function phongBan(): BelongsTo
    {
        return $this->belongsTo(PhongBan::class, 'phong_ban_id');
    }

    public function vaiTro(): BelongsTo
    {
        return $this->belongsTo(VaiTro::class, 'vai_tro_id');
    }

    public function getTenDayDuAttribute(): string
    {
        return trim(($this->chuc_danh ? $this->chuc_danh . ' ' : '') . $this->name);
    }

    public function caKhams(): HasMany
    {
        return $this->hasMany(CaKham::class, 'user_id');
    }

    public function taoCaKham(): void
    {
        $this->caKhams()->delete();

        $start = strtotime($this->gio_bat_dau);
        $end = strtotime($this->gio_ket_thuc);
        $duration = (int) $this->thoi_gian_kham * 60;
        $order = 0;

        while ($start + $duration <= $end) {
            $this->caKhams()->create([
                'gio_bat_dau' => date('H:i:00', $start),
                'gio_ket_thuc' => date('H:i:00', $start + $duration),
                'thu_tu' => $order++,
            ]);
            $start += $duration;
        }
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_tu_van' => 'boolean',
        ];
    }
}
