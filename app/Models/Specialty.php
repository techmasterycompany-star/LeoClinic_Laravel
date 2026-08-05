<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function doctorProfiles()
    {
        return $this->hasMany(DoctorProfile::class);
    }
}