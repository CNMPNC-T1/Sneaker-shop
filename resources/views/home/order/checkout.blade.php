@extends('home.layouts.master')
@section('content')
<!--================Checkout Area =================-->
<section class="banner-area organic-breadcrumb">
    <div class="container">
        <div class="breadcrumb-banner d-flex flex-wrap align-items-center justify-content-end">
            <div class="col-first">
                <h1>Checkout</h1>
                <nav class="d-flex align-items-center">
                    <a href="index.html">Home<span class="lnr lnr-arrow-right"></span></a>
                    <a href="category.html">Checkout</a>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="checkout_area section_gap">
    <div class="container">
        @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif
        <div class="billing_details">
            <div class="row">
                <div class="col-lg-8">
                    <h3>Billing Details</h3>
                    <form class="row contact_form" action="{{ route('checkout.update') }}" method="post" id="form-checkout">
                        @csrf
                        <div class="col-md-6 form-group p_star">
                            <label for="FirstName">FirstName</label>
                            <input type="text" class="form-control" id="first" value="{{ $user->firtsname }}" name="firtsname" required>
                        </div>
                        <div class="col-md-6 form-group p_star">
                            <label for="LastName">LastName</label>
                            <input type="text" class="form-control" id="last" value="{{ $user->lastname }}" name="lastname" required>
                        </div>
                        <div class="col-md-6 form-group p_star">
                            <label for="Phone">Phone</label>
                            <input type="text" class="form-control" id="number" value="{{ $user->phone }}" name="phone" required>
                        </div>
                        <div class="col-md-6 form-group p_star">
                            <label for="address">address</label>
                            <input type="text" class="form-control" id="address" value="{{ $user->address }}" name="address" required>
                        </div>

                </div>
                <div class="col-lg-4">
                    <div class="order_box">
                        <h2>Your Order</h2>
                        <ul class="list">
                            <li><a href="#">Product <span>Total</span></a></li>
                            @foreach ($cart->items as $item)
                            <li><a href="#">{{ $item['name'] }} <span class="middle">x {{ $item['quantity'] }}</span> <span class="last">{{ formatCurrency($item['quantity'] * $item['price']) }}</span></a></li>
                            @endforeach
                        </ul>
                        <ul class="list list_2">
                            <li><a href="#">Subtotal <span>{{ formatCurrency(session('subtotal', $cart->totalPrice)) }}</span></a></li>
                            <li><a href="#">Shipping <span>Flat rate: {{ formatCurrency(20000) }}</span></a></li>
                            <!-- Phí vận chuyển cố định là 20000 -->
                            <li><a href="#">Discount <span id="discount">{{ formatCurrency(session('discount', 0)) }}</span></a></li>
                            <li><a href="#">Total <span id="total">{{ formatCurrency(session('totalPrice', $cart->totalPrice + 20000)) }}</span></a></li>
                            <!-- Tổng cộng bao gồm Subtotal, phí vận chuyển và trừ đi Discount -->
                        </ul>
                        <div class="form-group">
                            <label for="voucher">Voucher Code</label>
                            <input type="text" class="form-control" id="voucher" name="voucher" placeholder="Enter voucher code">
                        </div>
                        <button type="submit" name="submit" id="btn-submit" class="btn primary-btn" onclick="document.querySelector('#form-checkout').submit()">Đặt hàng</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="subtotal-value" value="{{ $cart->totalPrice }}">
    <!-- Thêm thẻ input ẩn để lưu giá trị subtotal -->
</section>
@endsection