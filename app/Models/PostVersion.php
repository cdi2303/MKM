<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostVersion extends Model
{
    protected $fillable = [
        'post_id',
        'version',
        'title',
        'keyword',
        'html',
        'content',
        'meta',
    ];
}
