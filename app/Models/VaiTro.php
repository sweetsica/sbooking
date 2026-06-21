<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaiTro extends Model
{
    protected $table = 'vai_tro';

    protected $fillable = ['ten', 'ma'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'vai_tro_id');
    }

    public function phanQuyens(): HasMany
    {
        return $this->hasMany(PhanQuyen::class, 'vai_tro_id');
    }
}
