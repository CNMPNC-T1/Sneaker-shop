<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provide extends Model
{
    use HasFactory;

    // Chỉ định khóa chính là sự kết hợp của provider_id và product_id
    protected $primaryKey = ['provider_id', 'product_id'];

    // Đảm bảo Eloquent không tự động tạo các trường auto-increment
    public $incrementing = false;

    protected $fillable = [
        'provider_id',
        'product_id',
        'status'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getProductNameAttribute()
    {
        return $this->product->name;
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function getProviderNameAttribute()
    {
        return $this->provider->name;
    }
}
