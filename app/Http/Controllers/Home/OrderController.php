<?php

namespace App\Http\Controllers\Home;

use App\Mail\SendMailNotification;


use App\Enums\BillStatusEnum;
use App\Enums\CartStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\ShoppingCart;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


use App\Models\Voucher;
use App\Models\Voucher_bills;



class OrderController extends Controller
{

    public function checkout()
    {
        $user = auth()->user();
        $cart = new ShoppingCart();



        return view('home.order.checkout', compact('user', 'cart'));
    }


    public function update(Request $request)
    {
        $request->validate([
            'firtsname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $user->update($request->only('firtsname', 'lastname', 'phone', 'address'));

        $cart = new ShoppingCart();
        $subtotal =  $cart->getTotalPrice();

        $order = new Bill();
        $order->user_id = auth()->id();
        $order->total = $subtotal; // Chúng ta sẽ cập nhật total sau khi tính discount
        $order->delivery_date = now();
        $order->payment_status = 0;
        $order->payment_method = 0;
        $order->status = BillStatusEnum::ORDER;
        $order->save();

        $orderDetails = [];
        foreach ($cart->items as $item) {
            $orderDetails[] = BillDetail::create([
                'bill_id' => $order->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        $discount = 0;
        if ($request->voucher) {
            $voucher = Voucher::where('value', $request->voucher)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();
            if (!$voucher) {
                return redirect()->back()->with('error', 'Voucher không tồn tại hoặc đã hết hạn.');
            } else {
                if ($voucher->type === 'percent') {
                    $discount = ($subtotal * $voucher->amount) / 100;
                } elseif ($voucher->type === 'fixed') {
                    $discount = $voucher->amount;
                }
                Voucher_bills::create([
                    'bill_id' => $order->id,
                    'vouchers_id' => $voucher->id_voucher
                ]);
            }
        }

        $totalPrice = $subtotal - $discount;
        $order->total = $totalPrice;
        $order->save();

        Mail::to($user->email)->send(new SendMailNotification($user, $order));
        $cart->clearCart();

        return redirect()->route('bill', ["order_id" => $order->id])->with([
            'discount' => $discount,
            'subtotal' => $subtotal,
            'totalPrice' => $totalPrice,
            'success' => 'Order placed successfully.'
        ]);
    }

    public function checkVoucher(Request $request)
    {
        $request->validate([
            'voucher' => 'required|string',
            'subtotal' => 'required|numeric|min:0'
        ]);

        $voucherCode = $request->input('voucher');
        $subtotal = $request->input('subtotal');

        $voucher = Voucher::where('value', $voucherCode)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher không tồn tại hoặc đã hết hạn.'
            ]);
        }

        $discount = 0;
        if ($voucher->type === 'percent') {
            $discount = ($subtotal * $voucher->amount) / 100;
        } elseif ($voucher->type === 'fixed') {
            $discount = $voucher->amount;
        }

        // Lưu discount vào session để validate khi submit form
        session(['voucher_discount' => $discount]);

        return response()->json([
            'success' => true,
            'discount' => $discount,
            'message' => 'Áp dụng voucher thành công!'
        ]);
    }

    public function Bill(Request $request)
    {
        $request = $request->all();
        $request["vnp_ResponseCode"] = $request["vnp_ResponseCode"] ?? '-1-1';
        $user = auth()->user();

        $order = Bill::query()
            ->with('details.product')
            ->where('id', $request["order_id"])
            ->where('user_id', auth()->user()->id)
            ->first();
        $order->status = BillStatusEnum::getKey($order->status);
        $order->payment_method = __("homepage." . PaymentMethodEnum::getKey($order->payment_method));

        if (!$order)
            return back();
        // Quy định vnp_ResponseCode mã trả lời 00 ứng với kết quả Thành công cho tất cả các API
        if ($request["vnp_ResponseCode"] == '00') {
            $order->update([
                "status" => BillStatusEnum::getValue($order->status),
                "payment_method" => PaymentMethodEnum::VNPAY,
                "payment_status" => PaymentStatusEnum::PAID
            ]);
        }


        return view('home.order.check_status', compact('user', 'order'));
    }


    public function vnpayment(Request $request)
    {
        $request = $request->all();
        $order_id = $request['id_order'];

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = url('/') . "/bill?order_id=$order_id";
        $vnp_TmnCode = env("vnp_TmnCode");
        $vnp_HashSecret = env("vnp_HashSecret");

        $vnp_TxnRef =   $order_id;
        $vnp_OrderInfo = 'Thanh toán đơn hàng';
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $request['total_order'] * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = 'NCB';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
            $inputData['vnp_Bill_State'] = $vnp_Bill_State;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        $returnData = array(
            'code' => '00',
            'message' => 'success',
            'data' => $vnp_Url
        );
        if (isset($_POST['redirect'])) {
            header('Location: ' . $vnp_Url);
            die();
        } else {
            echo json_encode($returnData);
        }
    }
    public function cancelOrder($id)
    {
        $user = auth()->user();
        $order = Bill::where('id', $id)->where('user_id', $user->id)->first();
        if ($order) {
            $order->status = BillStatusEnum::DESTROY;
            $order->save();
            return redirect()->route('show-user')->with('success', 'Order cancelled successfully.');
        }

        return redirect()->route('show-user')->with('error', 'Order not found or you are not authorized to cancel this order.');
    }
}
