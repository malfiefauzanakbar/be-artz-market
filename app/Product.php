<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'stock', 'price', 'expired_date', 'category_id', 'description', 
    ];
}
