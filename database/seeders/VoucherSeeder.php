<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('vouchers')->insert([
            [
                'start_date' => now()->subDays(5), // Ngày bắt đầu
                'end_date' => now()->addDays(5),  // Ngày kết thúc
                'value' => 'DISCOUNT10',         // Mã voucher
                'type' => 'percent',            // Loại giảm giá (percent/fixed)
                'amount' => 10.00,              // Giá trị giảm (10%)
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(10),
                'value' => 'DISCOUNT50',
                'type' => 'fixed',
                'amount' => 50.00,              // Giảm giá cố định 50 VND
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(3),
                'value' => 'FREESHIP',
                'type' => 'fixed',
                'amount' => 0.00,               // Miễn phí
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
