<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'employee_id',
        'operator_type',
        'operator_id',
        'service_id',
        'service',
        'laundry_style',
        'fabric_type',
        'special_instructions',
        'additional_services',
        'additional_charges',
        'laundry_products',
        'product_charges',
        'weight',
        'date',
        'time',
        'total_price',
        'total_amount',
        'status',
        'reference_no',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_details',
        'paid_at',
        'completion_date',
        'revenue_category'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'additional_charges' => 'decimal:2',
        'laundry_products' => 'array',
        'product_charges' => 'decimal:2',
        'payment_details' => 'array',
        'additional_services' => 'array',
        'paid_at' => 'datetime',
        'completion_date' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function latestStatusHistory()
    {
        return $this->hasOne(OrderStatusHistory::class)->latest();
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function getRevenueAttribute()
    {
        return $this->total_amount ?? $this->total_price;
    }

    public function getTotalAmountAttribute()
    {
        return round((float) $this->total_price + (float) $this->additional_charges + (float) ($this->product_charges ?? 0), 2);
    }

    public function getLaundryStylesListAttribute()
    {
        return [
            'Regular Wash' => 'Regular Wash',
            'Delicate Wash' => 'Delicate Wash',
            'Hand Wash' => 'Hand Wash',
            'Dry Clean Only' => 'Dry Clean Only',
            'Express Wash' => 'Express Wash'
        ];
    }

    public function getFabricTypesListAttribute()
    {
        return [
            'Cotton' => 'Cotton',
            'Polyester' => 'Polyester',
            'Wool' => 'Wool',
            'Silk' => 'Silk',
            'Linen' => 'Linen',
            'Denim' => 'Denim',
            'Synthetic' => 'Synthetic',
            'Mixed' => 'Mixed'
        ];
    }

    public function getAdditionalServicesListAttribute()
    {
        return \App\Support\LaundryWorkflow::EXTRAS;
    }
}
