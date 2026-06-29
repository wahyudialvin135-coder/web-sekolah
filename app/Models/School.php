<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'npsn',
        'name',
        'type',
        'district',
        'address',
        'latitude',
        'longitude',
        'is_priority',
        'notes',
    ];

    protected $casts = [
        'is_priority' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $appends = [
        'monitoring_status',
        'monitoring_status_color',
    ];

    /**
     * Get all accreditations for the school.
     */
    public function accreditations()
    {
        return $this->hasMany(Accreditation::class);
    }

    /**
     * Get the latest accreditation for the school.
     */
    public function latestAccreditation()
    {
        return $this->hasOne(Accreditation::class)->latestOfMany();
    }

    /**
     * Get the logs for the school.
     */
    public function logs()
    {
        return $this->hasMany(MonitoringLog::class);
    }

    /**
     * Accessor for monitoring_status
     */
    public function getMonitoringStatusAttribute(): string
    {
        $latest = $this->latestAccreditation;

        if (!$latest) {
            return 'Belum Terakreditasi';
        }

        if ($latest->grade === 'TT') {
            return 'Belum Terakreditasi';
        }

        $now = Carbon::now();
        $expiry = Carbon::parse($latest->expiry_date);

        if ($expiry->isBefore($now)) {
            return 'Kadaluarsa';
        }

        $diffInMonths = $now->diffInMonths($expiry, false);

        if ($diffInMonths <= 6) {
            return 'Habis dalam 6 Bulan';
        } elseif ($diffInMonths <= 12) {
            return 'Habis dalam 12 Bulan';
        }

        return 'Aktif';
    }

    /**
     * Accessor for monitoring_status_color
     */
    public function getMonitoringStatusColorAttribute(): string
    {
        $status = $this->monitoring_status;

        return match ($status) {
            'Aktif' => 'green',
            'Habis dalam 12 Bulan' => 'yellow',
            'Habis dalam 6 Bulan' => 'red',
            default => 'gray', // Kadaluarsa or Belum Terakreditasi
        };
    }
}
