<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;
    protected $table = 'vouchers';
    protected $primaryKey = 'id_voucher';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'start_date',
        'end_date',
        'value',
        'type',
    ];

    /**
     * Quan hệ với bảng voucher_bills.
     */
    public function voucherBills()
    {
        return $this->hasMany(Voucher_bills::class, 'vouchers_id', 'id_voucher');
    }
}
