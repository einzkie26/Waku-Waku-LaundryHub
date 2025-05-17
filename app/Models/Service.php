<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'base_price',
        'category',
        'is_active'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getRevenueAttribute()
    {
        return $this->orders()
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    public function getOrderCountAttribute()
    {
        return $this->orders()
            ->where('status', 'completed')
            ->count();
    }
} 