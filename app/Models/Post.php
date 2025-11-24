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
        'keyword',
        'title',
        'html',
        'content',
        'meta',

        // platform sync
        'platform',
        'external_id',
        'external_slug',
        'external_url',
        'wp_api_url',
        'tistory_access_token',
        'blog_name',

        'views',
        'clicks',
        'ctr',
        'likes',
        'comments',
        'last_synced_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'publish_meta'  => 'array',
        'is_draft' => 'boolean',
        'generated_at' => 'datetime',
    ];

    // Project 관계
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // 버전 관계
    public function versions()
    {
        return $this->hasMany(PostVersion::class);
    }
}
