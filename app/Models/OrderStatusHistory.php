<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    protected $fillable = [
        'order_id',
        'status',
        'changed_by_type',
        'changed_by_id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function changedBy()
    {
        if ($this->changed_by_type === 'admin') {
            return $this->belongsTo(User::class, 'changed_by_id');
        }
        return $this->belongsTo(Employee::class, 'changed_by_id');
    }
}
