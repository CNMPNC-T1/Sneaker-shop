<?php

namespace Tests\Unit;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\BillDetail;
use Database\Seeders\ProductSeeder;
use Database\Seeders\UserSeeder;
use  App\Enums\BillStatusEnum;

class BillControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_paginate_bills()
    {
        $user = User::factory()->create();
        Bill::factory()->count(10)->create([
            'user_id' => $user->id
        ]);
        $response = $this->actingAs($user)->get(route('api.bill.index') . '?page=1');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'user_name',
                    'total',
                    'payment_method',
                    'status_array',
                    'status_payment_array'
                ],
            ],
        ]);
    }
    public function test_get_paginate_returns_sai_data_trong_bill()
    {
        $user = User::factory()->create();
        Bill::factory()->count(10)->create([
            'user_id' => $user->id
        ]);
        $response = $this->actingAs($user)->get(route('api.bill.index') . '?page=1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'user_name',
                    'total',
                    'payment_method',
                    'status_array',
                    'status_payment_array',
                    'detail'
                ],
            ],
        ]);
    }
    public function test_get_paginate_load_method_ngoai_dinh_nghia()
    {
        $user = User::factory()->create();
        Bill::factory()->count(10)->create([
            'user_id' => $user->id,
            'payment_method' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('api.bill.index') . '?page=1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'user_name',
                    'total',
                    'payment_method',
                    'status_array',
                    'status_payment_array'
                ],
            ],
        ]);
    }

    public function test_get_cart_detail_returns_details()
    {
        $this->seed(ProductSeeder::class);
        $user = User::factory()->create();
        $bill = Bill::factory()->create(['user_id' => $user->id]);
        $billDetails = BillDetail::factory()->count(3)->create(['bill_id' => $bill->id]);

        $billDetail = $billDetails->first();
        $response = $this->actingAs($user)->get(route('api.bill.getCartDetail', ['id' => $billDetail->id]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'bill_id',
                    'product_id'
                ],
            ],
        ]);
    }

    public function test_get_cart_detail_returns_details_NULL()
    {
        $this->seed(ProductSeeder::class);
        $user = User::factory()->create();
        $bill = Bill::factory()->create(['user_id' => $user->id]);
        BillDetail::factory()->count(3)->create(['bill_id' => $bill->id]);

        $response = $this->actingAs($user)->get(route('api.bill.getCartDetail', ['id' => ' ']));

        $response->assertStatus(200);

        $response->assertJson([
            'success' => false,
            'message' => 'Không được bỏ trống'
        ]);
    }
    public function test_update_cart_status_order_to_pack_chua_khong_phai_tk_admin()
    {
        $user = User::factory()->create();
        $bill = Bill::factory()->create(['user_id' => $user->id, 'payment_status' => '0']);

        $response = $this->actingAs($user)->get(route('admin.bill.edit', ['id' => $bill->id]), [
            'status' => '2'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Thành công'
        ]);

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'status' => '1'
        ]);
    }

    public function test_update_cart_status_invalid()
    {
        $this->seed(UserSeeder::class);
        $user = User::find(1);  
        $this->actingAs($user);
        $bill = Bill::factory()->create(['user_id' => $user->id, 'payment_status' => '0']);
        $response = $this->actingAs($user)->get(route('admin.bill.edit', ['id' => $bill->id, 'status' => "5"]));
        $response->assertStatus(500);
        $response->assertJson([
            'success' => true,
            'message' => 'Thành công'
        ]);
    }
    public function test_update_cart_status_order_to_pack()
    {
        $this->seed(UserSeeder::class);
        $user = User::find(1);
        $this->actingAs($user);
        $bill = Bill::factory()->create(['user_id' => $user->id, 'payment_status' => '0']);
        $response = $this->actingAs($user)->get(route('admin.bill.edit', ['id' => $bill->id, 'status' => "TRANSPORT"]));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Thành công'
        ]);
    }

   public function test_update_cart_status_khong_tontai_bill_id()
{
    $this->seed(UserSeeder::class);
    $user = User::find(1);
    $this->actingAs($user);

    // Gửi yêu cầu tới ID hóa đơn không tồn tại
    $response = $this->actingAs($user)->get(route('admin.bill.edit', ['id' => '6', 'status' => "TRANSPORT"]));
    // Kiểm tra mã trạng thái phản hồi
    $response->assertStatus(200);
    // Kiểm tra JSON phản hồi
    $response->assertJson([
        'success' => false,
        'message' => 'không tìm thấy bill này'
    ]);
}

}
