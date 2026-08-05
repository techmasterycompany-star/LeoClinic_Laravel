<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'location_id',
        'date',
        'start_time',
        'end_time',
        'is_booked',
    ];

    protected $casts = [
        'date' => 'date',
        'is_booked' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}