<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
    ];

    public function doctorLocations()
    {
        return $this->hasMany(DoctorLocation::class);
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }
}