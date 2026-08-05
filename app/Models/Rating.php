<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'rating',
        'comment',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}