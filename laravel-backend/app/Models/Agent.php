<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = ['name',  'status' ,'is_available','user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
