<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    /**
     * Atribut yang diizinkan untuk modifikasi massal (mass assignable).
     *
     * @var array
     */
    protected $fillable = [
        'school_id',
        'plan_id',
        'start_date',
        'end_date',
        'amount',
        'payment_method',
        'payment_status',
        'payment_proof',
        'invoice_number',
        'status',
    ];

    /**
     * Konversi tipe data otomatis (casting) untuk kolom spesifik.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Relasi menuju entitas sekolah.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * Relasi menuju entitas paket langganan (Subscription Plan).
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }
}