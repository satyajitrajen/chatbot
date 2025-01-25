<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Metadata extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'timestamp',
        'country',
        'region',
        'city',
        'latitude',
        'longitude',
    ];

    /**
     * Automatically cast attributes.
     */
    protected $casts = [
        'timestamp' => 'datetime', // Ensures proper formatting for the timestamp field
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Disable timestamps if not required (optional).
     */
    public $timestamps = true; // Laravel will manage created_at and updated_at

    /**
     * Mutator to handle ISO 8601 format for `timestamp`.
     */
    public function setTimestampAttribute($value)
    {
        // Parse ISO 8601 or handle as a general date format
        $this->attributes['timestamp'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
