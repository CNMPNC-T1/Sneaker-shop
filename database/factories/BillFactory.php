<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Bill;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Bill::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // Tạo người dùng mới cho mỗi hóa đơn
            'total' => $this->faker->randomFloat(2, 1, 1000), // Số tiền ngẫu nhiên
            'delivery_date' => $this->faker->dateTimeBetween('now', '+1 month'), // Ngày giao hàng
            'payment_status' => 0, // Trạng thái thanh toán
            'payment_method' => 0, // Phương thức thanh toán
            'status' => 1, // Trạng thái hóa đơn (có thể thay đổi tùy vào enum của bạn)
        ];
    }
}
