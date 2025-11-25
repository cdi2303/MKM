<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'is_draft',
        'keyword',
        'title',
        'content',
        'html',
        'meta',

        // platform sync
        'platform',
        'external_id',
        'external_slug',
        'external_url',
        'wp_api_url',
        'tistory_access_token',
        'blog_name',

        'publish_meta',
        'wp_ctr',
        'tistory_ctr',

        'thumbnail_url',
        'generated_at',
        'tags',

        // stats
        'views',
        'clicks',
        'ctr',
        'likes',
        'comments',
        'last_synced_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'publish_meta' => 'array',
        'is_draft' => 'boolean',
        'generated_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function versions()
    {
        return $this->hasMany(PostVersion::class);
    }
}
