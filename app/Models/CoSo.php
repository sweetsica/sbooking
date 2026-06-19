<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoSo extends Model
{
    protected $table = 'co_so';

    protected $fillable = ['ten', 'slug', 'dia_chi', 'active'];

    protected $casts = ['active' => 'boolean'];

    // Route model binding theo slug: /{co_so:slug}/...
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function phongs(): HasMany
    {
        return $this->hasMany(Phong::class, 'co_so_id');
    }

    public function bacSis(): HasMany
    {
        return $this->hasMany(BacSi::class, 'co_so_id');
    }

    public function dichVus(): HasMany
    {
        return $this->hasMany(DichVu::class, 'co_so_id');
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'co_so_id');
    }

    public function nguoiDungs(): HasMany
    {
        return $this->hasMany(User::class, 'co_so_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'co_so_id');
    }

    public function bacSiTuVans(): HasMany
    {
        return $this->hasMany(BacSiTuVan::class, 'co_so_id');
    }

    public function lichHens(): HasMany
    {
        return $this->hasMany(LichHen::class, 'co_so_id');
    }
}
