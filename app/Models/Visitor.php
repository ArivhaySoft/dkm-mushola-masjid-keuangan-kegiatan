<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = ['tanggal', 'ip', 'user_agent', 'latitude', 'longitude'];

    protected $casts = [
        'tanggal'   => 'date',
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];
}
