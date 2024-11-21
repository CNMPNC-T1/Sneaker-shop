<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher_bills extends Model
{
    use HasFactory;
    protected $table = 'voucher_bills';

    protected $fillable = [
        'bill_id',
        'vouchers_id',
    ];

    /**
     * Quan hệ với bảng vouchers.
     */
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'vouchers_id', 'id_voucher');
    }

    /**
     * Quan hệ với bảng bills.
     */
    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id', 'id'); // Giả định bảng bills có khóa chính là 'id'
    }
}
