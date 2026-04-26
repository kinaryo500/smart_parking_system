<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kendaraan extends Model
{
    protected $fillable = [
        'user_id',
        'jenis',
        'plat_nomor',
        'merk',
        'warna'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isMotor()
    {
        return $this->jenis === 'motor';
    }

    public function isMobil()
    {
        return $this->jenis === 'mobil';
    }

    public function parkirTransaksis(): HasMany
    {
        return $this->hasMany(ParkirTransaksi::class);
    }
}