<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkirTransaksi extends Model
{
    protected $table = 'parkir_transaksis';

    protected $guarded = [];

    protected $casts = [
        'waktu_masuk' => 'datetime',
        'waktu_keluar' => 'datetime',
        'total_waktu' => 'integer',
        'total_bayar' => 'integer',
        'tarif_per_jam' => 'integer',
    ];

    protected $appends = ['durasi_saat_ini', 'estimasi_biaya', 'format_waktu_masuk'];

    public function hitungDurasi()
    {
        if (!$this->waktu_masuk) return 0;

        $masuk = $this->waktu_masuk;
        $keluar = $this->status === 'selesai' && $this->waktu_keluar 
                  ? $this->waktu_keluar 
                  : now();

        return (int) $masuk->diffInMinutes($keluar);
    }

    public function hitungTotalBayar()
    {
        $menit = $this->hitungDurasi();
        $jam = max(1, ceil($menit / 60));
        return (int) ($jam * ($this->tarif_per_jam ?? 2000));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id');
    }

    public function qrParkir(): BelongsTo
    {
        return $this->belongsTo(QRParkir::class, 'qr_parkir_id');
    }

    public function gateMasuk(): BelongsTo
    {
        return $this->belongsTo(Gate::class, 'gate_masuk_id');
    }

    public function gateKeluar(): BelongsTo
    {
        return $this->belongsTo(Gate::class, 'gate_keluar_id');
    }

    public function getDurasiSaatIniAttribute()
    {
        return $this->hitungDurasi();
    }

    public function getEstimasiBiayaAttribute()
    {
        return $this->hitungTotalBayar();
    }

    public function getFormatWaktuMasukAttribute()
    {
        return $this->waktu_masuk ? $this->waktu_masuk->format('H:i:s') : '-';
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }
}