<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'location_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}