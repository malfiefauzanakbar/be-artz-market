<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'sub_total', 'tax', 'service', 'total', 'status', 'bank', 'no_rek', 'payment_proof'
    ];
}
