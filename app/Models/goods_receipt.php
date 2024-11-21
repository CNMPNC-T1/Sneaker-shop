<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class goods_receipt extends Model
{
    use HasFactory;
    protected $fillable = [
        'provider_id',
        'date',
        'sum'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function getProviderNameAttribute()
    {
        return $this->provider->name;
    }
}
