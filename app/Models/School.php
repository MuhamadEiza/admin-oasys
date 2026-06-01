<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Student;

class School extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'schools';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'npsn',
        'nss',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'website',
        'principal_name',
        'principal_nip',
        'principal_phone',
        'principal_email',
        'accreditation',
        'accreditation_year',
        'education_level',
        'status',
        'subscription_status',
        'subscription_start_date',
        'subscription_expiry_date',
        'max_students',
        'max_teachers',
        'max_classes',
        'logo',
        'theme_color',
        'created_by',
    ];

    protected $casts = [
        'subscription_start_date' => 'date',
        'subscription_expiry_date' => 'date',
        'accreditation_year' => 'integer',
        'max_students' => 'integer',
        'max_teachers' => 'integer',
        'max_classes' => 'integer',
        'deleted_at' => 'datetime',
    ];

    // Auto generate UUID jika tidak diisi
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // ========== RELATIONS ==========

    // Relasi ke users (admin sekolah, guru)
    public function users()
    {
        return $this->hasMany(User::class, 'school_id');
    }

    // Relasi ke students
    public function students()
    {
        return $this->hasMany(Student::class, 'school_id');
    }

    public function teachers()
    {
        return $this->hasMany(User::class, 'school_id')->whereHas('roles', function ($q) {
            $q->where('name', 'guru');
        });
    }

    // Relasi ke classes
    public function classes()
    {
        return $this->hasMany(Classes::class, 'school_id');
    }

    // Relasi ke subjects
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'school_id');
    }

    // Relasi ke attendances
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'school_id');
    }

    // Relasi ke schedules
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'school_id');
    }

    // Relasi ke academic_years
    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class, 'school_id');
    }

    // Relasi ke subscription plans
    public function subscriptions()
    {
        return $this->hasMany(SchoolSubscription::class, 'school_id');
    }

    // Relasi ke activity logs
    public function activityLogs()
    {
        return $this->hasMany(SchoolActivityLog::class, 'school_id');
    }

    // Relasi ke user pembuat (super admin)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ========== SCOPES ==========

    // Scope untuk sekolah aktif
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope untuk sekolah yang akan kadaluarsa
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('subscription_expiry_date', '<=', now()->addDays($days))
            ->where('subscription_expiry_date', '>', now());
    }

    // Scope untuk sekolah yang sudah kadaluarsa
    public function scopeExpired($query)
    {
        return $query->where('subscription_expiry_date', '<', now())
            ->where('status', 'active');
    }

    // Scope untuk sekolah menunggu verifikasi
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ========== ACCESSORS ==========

    // Status label
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'pending' => 'Menunggu Verifikasi',
            'suspended' => 'Ditangguhkan',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'active' => 'success',
            'inactive' => 'danger',
            'pending' => 'warning',
            'suspended' => 'gray',
            default => 'secondary',
        };
    }

    // Subscription status label
    public function getSubscriptionStatusLabelAttribute()
    {
        return match ($this->subscription_status) {
            'active' => 'Aktif',
            'trial' => 'Trial',
            'expired' => 'Kadaluarsa',
            'cancelled' => 'Dibatalkan',
            default => $this->subscription_status,
        };
    }

    // Cek apakah subscription masih valid
    public function getIsSubscriptionValidAttribute()
    {
        return $this->subscription_status === 'active'
            && $this->subscription_expiry_date
            && $this->subscription_expiry_date >= now();
    }

    // Logo URL
    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    // Full address
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }
    protected static function getApiBaseUrl()
    {
        return config('app.api_url', 'http://localhost:8001/api');
    }

    /**
     * Get auth token from session
     */
    protected static function getAuthToken()
    {
        return session('api_token');
    }

    /**
     * Get all schools from API (with caching)
     */
    public static function getAllFromApi()
    {
        $cacheKey = 'schools_all';

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . self::getAuthToken(),
                'Accept' => 'application/json',
            ])->get(self::getApiBaseUrl() . '/admin/schools');

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                return collect($data)->map(function ($item) {
                    return new self($item);
                });
            }

            return collect();
        });
    }

    /**
     * Get paginated schools from API
     */
    public static function paginateFromApi($perPage = 10, $page = 1)
    {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . self::getAuthToken(),
            'Accept' => 'application/json',
        ])->get(self::getApiBaseUrl() . '/admin/schools', [
                    'per_page' => $perPage,
                    'page' => $page
                ]);

        if ($response->successful()) {
            $result = $response->json();
            return [
                'data' => collect($result['data'])->map(fn($item) => new self($item)),
                'total' => $result['meta']['total'] ?? 0,
                'current_page' => $result['meta']['current_page'] ?? 1,
                'last_page' => $result['meta']['last_page'] ?? 1,
            ];
        }

        return ['data' => collect(), 'total' => 0, 'current_page' => 1, 'last_page' => 1];
    }

    /**
     * Create school via API
     */
    public static function createFromApi(array $data)
    {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . self::getAuthToken(),
            'Accept' => 'application/json',
        ])->post(self::getApiBaseUrl() . '/admin/schools', $data);

        if ($response->successful()) {
            $result = $response->json();
            \Illuminate\Support\Facades\Cache::forget('schools_all');
            return new self($result['data']);
        }

        throw new \Exception($response->json()['message'] ?? 'Failed to create school');
    }

    /**
     * Update school via API
     */
    public function updateFromApi(array $data)
    {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . self::getAuthToken(),
            'Accept' => 'application/json',
        ])->put(self::getApiBaseUrl() . '/admin/schools/' . $this->id, $data);

        if ($response->successful()) {
            $result = $response->json();
            \Illuminate\Support\Facades\Cache::forget('schools_all');
            foreach ($result['data'] as $key => $value) {
                $this->$key = $value;
            }
            return true;
        }

        throw new \Exception($response->json()['message'] ?? 'Failed to update school');
    }

    /**
     * Delete school via API
     */
    public function deleteFromApi()
    {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . self::getAuthToken(),
            'Accept' => 'application/json',
        ])->delete(self::getApiBaseUrl() . '/admin/schools/' . $this->id);

        if ($response->successful()) {
            \Illuminate\Support\Facades\Cache::forget('schools_all');
            return true;
        }

        throw new \Exception($response->json()['message'] ?? 'Failed to delete school');
    }
}