<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationProgress extends Model
{
    use HasFactory;

    protected $table = 'donation_progresses';

    protected $fillable = [
        'current_amount',
        'target_amount',
    ];
}