<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredients')->withTimestamps();
    }

    public function ingredientItems()
    {
        return $this->hasMany(ProductIngredient::class);
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
