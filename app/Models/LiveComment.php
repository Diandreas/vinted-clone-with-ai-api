<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_id',
        'user_id',
        'content',
    ];

    public function live()
    {
        return $this->belongsTo(Live::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}