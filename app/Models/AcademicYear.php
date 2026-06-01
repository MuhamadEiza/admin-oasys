<?php
// app/Models/AcademicYear.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'school_id', 
        'semester',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function classes()
    {
        return $this->hasMany(Classes::class, 'academic_year_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'academic_year_id');
    }
     public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}