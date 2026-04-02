<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'color'];
    protected $table = 'post_categories';

    public function posts() { return $this->hasMany(Post::class, 'category_id'); }
}
