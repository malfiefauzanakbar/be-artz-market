<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailTransaction extends Model
{
    protected $fillable = [
        'transaction_id', 'product_id', 'price', 'qty',
    ];
}
