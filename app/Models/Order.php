<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessor to easily get customer name for Backpack
    public function getCustomerNameAttribute()
    {
        if ($this->user) {
            return $this->user->name . " (Registered)";
        }
        return $this->attributes['customer_name'] ?? 'N/A (Guest)';
    }
}
