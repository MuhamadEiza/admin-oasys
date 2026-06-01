<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terasosiasi dengan model.
     *
     * @var string
     */
    protected $table = 'activity_logs';

    /**
     * Atribut yang diizinkan untuk modifikasi massal (mass assignable).
     *
     * @var array
     */
    protected $fillable = [
        'school_id',
        'admin_id',
        'activity_type',
        'description',
        'ip_address',
        'old_data',
        'new_data',
    ];

    /**
     * Konversi tipe data otomatis (casting) untuk kolom spesifik.
     *
     * @var array
     */
    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi menuju entitas administrator (pelaku aktivitas).
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Relasi menuju entitas sekolah terkait (opsional, tergantung konteks).
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}