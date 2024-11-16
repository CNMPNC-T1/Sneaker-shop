<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class receipt_detail extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'goods_receipt_id',
        'product_id',
        'amount',
        'price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getProductNameAttribute()
    {
        return $this->product->name;
    }
}
