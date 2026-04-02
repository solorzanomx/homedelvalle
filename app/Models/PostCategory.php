<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'description', 'color'])]
class PostCategory extends Model
{
    protected $table = 'post_categories';

    public function posts() { return $this->hasMany(Post::class, 'category_id'); }
}
