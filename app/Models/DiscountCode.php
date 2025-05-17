<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    protected $fillable = ['code', 'discount_percent', 'used', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

