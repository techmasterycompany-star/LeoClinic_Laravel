<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact_number',
        'date_of_birth',
        'address',
        'is_approved',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_approved' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }
}