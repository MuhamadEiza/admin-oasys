<?php
// app/Models/Classes.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'classes'; 
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tingkat',
        'school_id',
        'sub_kelas',
        'permintaan',
        'wali_kelas',
        'jumlah_murid',
        'academic_year_id'
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }


}