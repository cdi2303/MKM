<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    protected $fillable = [
        'project_id','user_id','keyword','title','content','html','meta','thumbnail_url','generated_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'generated_at' => 'datetime'
    ];
}
