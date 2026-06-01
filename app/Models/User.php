<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'nuptk',
        'phone',
        'gender',
        'birth_place',
        'birth_date',
        'marital_status',
        'address',
        'position',
        'golongan',
        'employment_status',
        'join_date',
        'bidang_keahlian',
        'photo',
        'school_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects', 'teacher_id', 'subject_id');
    }


    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'teacher_subjects', 'teacher_id', 'class_id');
    }



    // Relasi ke teacher_subjects (untuk ambil mata pelajaran)
    public function teacherSubjects()
    {
        return $this->hasMany(TeacherSubject::class, 'teacher_id');
    }

    // Relasi ke kelas sebagai wali kelas
    public function homeroomClass()
    {
        return $this->hasOne(Classes::class, 'wali_kelas_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    public function children()
    {
        return $this->belongsToMany(Student::class, 'student_parent', 'parent_id', 'student_id');
    }

}