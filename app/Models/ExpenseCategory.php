<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'type', 'color', 'icon', 'is_active'])]
class ExpenseCategory extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
