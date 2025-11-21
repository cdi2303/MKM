<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'description'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
