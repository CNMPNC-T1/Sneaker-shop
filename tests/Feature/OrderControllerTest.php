<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Enums\BillStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Home\OrderController;
use App\Models\Bill;
use App\Models\ShoppingCart;
use Illuminate\Http\Request;
use Mockery;

class OrderControllerTest extends TestCase
{
    protected $orderController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderController = new OrderController();
    }

    // Test hiển thị trang checkout
    public function test_checkout_page(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('checkout'));
        $response->assertStatus(200);
        $response->assertViewIs('home.order.checkout');
        $response->assertViewHas('user', $user);
    }

    // Test cập nhật đơn hàng với dữ liệu không hợp lệ
    public function test_update_order_with_invalid_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = new Request([
            'firtsname' => '',
            'lastname' => 'Van A',
            'phone' => '0123456789',
            'address' => '123 Đường ABC, TP.HCM',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->orderController->update($request);
    }

    // Test cập nhật đơn hàng với dữ liệu không hợp lệ (các trường hợp khác nhau)

    public function test_update_order_with_invalid_data_abnormal(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Tạo yêu cầu với tên trống
        $request = new Request([
            'firtsname' => '',
            'lastname' => 'Van A',
            'phone' => '0123456789',
            'address' => '123 Đường ABC, TP.HCM',
        ]);

        // Gọi phương thức update và kiểm tra không có dữ liệu nào được lưu
        $this->orderController->update($request);
        $this->assertDatabaseMissing('users', [
            'firtsname' => '',
            'lastname' => 'Van A',
            'phone' => '0123456789',
            'address' => '123 Đường ABC, TP.HCM',
        ]);

        // Tạo yêu cầu với họ trống
        $request->merge(['firtsname' => 'John', 'lastname' => '']);
        $this->orderController->update($request);
        $this->assertDatabaseMissing('users', [
            'firtsname' => 'John',
            'lastname' => '',
            'phone' => '0123456789',
            'address' => '123 Đường ABC, TP.HCM',
        ]);

        // Tạo yêu cầu với số điện thoại không hợp lệ
        $request->merge(['lastname' => 'Doe', 'phone' => 'invalid_phone']);
        $this->orderController->update($request);
        $this->assertDatabaseMissing('users', [
            'firtsname' => 'John',
            'lastname' => 'Doe',
            'phone' => 'invalid_phone',
            'address' => '123 Đường ABC, TP.HCM',
        ]);

        // Tạo yêu cầu với địa chỉ rỗng
        $request->merge(['phone' => '0123456789', 'address' => '']);
        $this->orderController->update($request);
        $this->assertDatabaseMissing('users', [
            'firtsname' => 'John',
            'lastname' => 'Doe',
            'phone' => '0123456789',
            'address' => '',
        ]);
    }


    // Test cập nhật đơn hàng với dữ liệu hợp lệ
    public function test_update_order_with_valid_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'firtsname' => 'John',
            'lastname' => 'Doe',
            'phone' => '123456789',
            'address' => '123 Street Name',
        ];

        $response = $this->post(route('checkout.update'), $data);
        $user->refresh();

        $this->assertEquals('John', $user->firtsname);
        $this->assertEquals('Doe', $user->lastname);
        $this->assertEquals('123456789', $user->phone);
        $this->assertEquals('123 Street Name', $user->address);
    }

    // Test xử lý giỏ hàng và tạo hóa đơn
    public function test_process_cart_creates_bill_and_clears_cart()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cart = Mockery::mock(ShoppingCart::class);
        $cart->items = [
            ['id' => 1, 'quantity' => 2, 'price' => 100],
            ['id' => 2, 'quantity' => 1, 'price' => 200],
        ];

        $cart->shouldReceive('getTotalPrice')->once()->andReturn(400);
        $cart->shouldReceive('clearCart')->once();

        $order = $this->orderController->processCart($cart);

        $this->assertDatabaseHas('bills', [
            'user_id' => $user->id,
            'total' => 400,
            'status' => BillStatusEnum::ORDER,
        ]);

        $this->assertDatabaseHas('bill_details', [
            'bill_id' => $order->id,
            'product_id' => 1,
            'quantity' => 2,
            'price' => 100,
        ]);

        $this->assertDatabaseHas('bill_details', [
            'bill_id' => $order->id,
            'product_id' => 2,
            'quantity' => 1,
            'price' => 200,
        ]);
    }

    // Test xử lý giỏ hàng với sản phẩm không hợp lệ
    public function test_process_cart_with_invalid_product()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cart = Mockery::mock(ShoppingCart::class);
        $cart->items = [
            ['id' => 999, 'quantity' => 2, 'price' => 100],
        ];

        $cart->shouldReceive('getTotalPrice')->once()->andReturn(200);
        $cart->shouldReceive('clearCart')->once();

        $order = $this->orderController->processCart($cart);

        $this->assertDatabaseMissing('bills', [
            'user_id' => $user->id,
            'total' => 200,
        ]);
    }
    //test số lượng âm
        public function test_process_cart_with_negative_quantity()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cart = Mockery::mock(ShoppingCart::class);
        $cart->items = [
            ['id' => 1, 'quantity' => -3, 'price' => 100],
            ['id'=>2,'quantity'=>0,'price'=>200],
        ];

        $cart->shouldReceive('getTotalPrice')->once()->andReturn(-300);
        $cart->shouldReceive('clearCart')->once();

        $order = $this->orderController->processCart($cart);
        $this->assertNotNull($order, 'Order was created even with negative quantity!');
        $this->assertDatabaseMissing('bills', [
            'user_id' => $user->id,
            'total' => -300,
        ],
    );
    }
    //test với số lượng bằng 0
    public function test_process_cart_with_zero()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cart = Mockery::mock(ShoppingCart::class);
        $cart->items = [
            ['id'=>2,'quantity'=>0,'price'=>200],
        ];

        $cart->shouldReceive('getTotalPrice')->once()->andReturn(0);
        $cart->shouldReceive('clearCart')->once();

        $order = $this->orderController->processCart($cart);
        $this->assertNotNull($order, 'Order was created even with zero quantity!');
        $this->assertDatabaseMissing('bills', [
            'user_id' => $user->id,
            'total' => 0,
        ],
    );
    }


    // Test cập nhật đơn hàng với các trường rỗng
    public function test_update_order_with_empty_fields()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'firtsname' => '',
            'lastname' => '',
            'phone' => '',
            'address' => '',
        ];

        $response = $this->post(route('checkout.update'), $data);
        $this->assertDatabaseMissing('users', [
            'firtsname' => '',
            'lastname' => '',
            'phone' => '',
            'address' => '',
        ]);

        $response->assertRedirect();
    }

    // Test xử lý giỏ hàng trống
    public function test_process_cart_empty_cart()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cart = Mockery::mock(ShoppingCart::class);
        $cart->items = [];
        $cart->shouldReceive('getTotalPrice')->once()->andReturn(0);
        $cart->shouldReceive('clearCart')->once();

        $order = $this->orderController->processCart($cart);
        $this->assertNull($order);
    }

    public function test_bill_view_after_failed_vnpay_payment()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Bill::factory()->create([
            'user_id' => $user->id,
            'status' => BillStatusEnum::ORDER,
            'payment_status' => PaymentStatusEnum::UNPAID,
        ]);

        $request = new Request([
            'order_id' => $order->id,
            'vnp_ResponseCode' => '01',
        ]);

        $response = $this->orderController->Bill($request);

        $this->assertDatabaseHas('bills', [
            'id' => $order->id,
            'payment_status' => PaymentStatusEnum::UNPAID,
        ]);
    }
    public function test_bill_view_after_successful_vnpay_payment()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Bill::factory()->create([
            'user_id' => $user->id,
            'status' => BillStatusEnum::ORDER,
            'payment_status' => PaymentStatusEnum::UNPAID,
        ]);

        $request = new Request([
            'order_id' => $order->id,
            'vnp_ResponseCode' => '00',
        ]);

        $response = $this->orderController->Bill($request);

        $this->assertDatabaseHas('bills', [
            'id' => $order->id,
            'payment_status' => PaymentStatusEnum::PAID,
            'payment_method' => PaymentMethodEnum::VNPAY,
        ]);
    }
// Test hủy đơn hàng thành công
public function test_cancel_order_successfully()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $order = Bill::factory()->create([
        'user_id' => $user->id,
        'status' => BillStatusEnum::ORDER,
    ]);

    $response = $this->orderController->cancelOrder($order->id);

    $this->assertDatabaseHas('bills', [
        'id' => $order->id,
        'status' => BillStatusEnum::DESTROY,
    ]);

    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    $this->assertEquals(route('show-user'), $response->getTargetUrl());
}

// Test hủy đơn hàng không tồn tại
public function test_cancel_order_non_existent()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->orderController->cancelOrder(999);

    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    $this->assertEquals(route('show-user'), $response->getTargetUrl());
    $this->assertDatabaseMissing('bills', [
        'id' => 999,
    ]);
}

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
