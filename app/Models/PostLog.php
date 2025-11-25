<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostLog extends Model
{
    protected $fillable = [
        'post_id',
        'action',
        'request',
        'response',
        'success',
    ];

    protected $casts = [
        'request' => 'array',
        'response' => 'array'
    ];
}
