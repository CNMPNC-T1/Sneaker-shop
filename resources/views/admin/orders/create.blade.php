@extends('admin.layouts.master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Hyper</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">eCommerce</a></li>
                    <li class="breadcrumb-item active">Add Goods Receipt</li>
                </ol>
            </div>
            <h4 class="page-title">Add Goods Receipt</h4>
        </div>
    </div>
</div>

<form action="{{ route('admin.GoodsReceipt.store') }}" method="POST" id="goods-receipt-form">
    @csrf
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="provider_id">Provider</label>
                        <select id="provider_id" class="form-control @error('provider_id') is-invalid @enderror" name="provider_id" required>
                            <option value="">Select Provider</option>
                            @foreach($providers as $provider)
                            <option value="{{ $provider->id }}" {{ old('provider_id') == $provider->id ? 'selected' : '' }}>{{ $provider->name }}</option>
                            @endforeach
                        </select>
                        @error('provider_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="product_id">Product</label>
                        <select id="product_id" class="form-control @error('product_id') is-invalid @enderror" name="product_id" required>
                            <option value="">Select Product</option>
                        </select>
                        @error('product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="date">Date</label>
                        <input type="date" id="date" class="form-control @error('date') is-invalid @enderror" name="date" value="{{ now()->format('Y-m-d') }}" readonly>
                        @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="sum">Total</label>
                        <input type="number" step="0.01" id="sum" class="form-control @error('sum') is-invalid @enderror" name="sum" value="{{ old('sum') }}" readonly>
                        @error('sum')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ route('admin.GoodsReceipt.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered" id="receipt-details-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row template for adding items -->
                                @foreach(session()->get('goods_receipt', []) as $data)
                                @foreach($data['products'] as $productData)
                                <tr>
                                    <td>{{ $productData['product_name'] }}</td>
                                    <td>{{ $productData['amount'] }}</td>
                                    <td>{{ $productData['price'] }}</td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-product-btn">Remove</button></td>
                                </tr>
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-success" id="add-product-btn">Add Product</button>

                    <!-- Hidden field to store the product data -->
                    <input type="hidden" name="goods_receipt" id="goods-receipt-data">

                </div>
            </div> <!-- end card -->
        </div> <!-- end col -->
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const providerSelect = document.getElementById('provider_id');
        const productSelect = document.getElementById('product_id');
        const sumInput = document.getElementById('sum');
        const receiptDetailsTable = document.getElementById('receipt-details-table').querySelector('tbody');
        const addProductBtn = document.getElementById('add-product-btn');
        const goodsReceiptDataInput = document.getElementById('goods-receipt-data');
        const providerHiddenInput = document.createElement('input'); // Tạo input ẩn cho provider_id
        providerHiddenInput.type = 'hidden';
        providerHiddenInput.name = 'provider_id';
        providerHiddenInput.id = 'provider_id_hidden';

        let totalSum = 0;
        const productsInCart = []; // Mảng lưu các sản phẩm trong giỏ hàng

        // Fetch products by provider
        providerSelect.addEventListener('change', function() {
            const providerId = this.value;
            productSelect.innerHTML = '<option value="">Select Product</option>';

            fetch(`/api/getProductsByProvider/${providerId}`)
                .then(response => response.text())
                .then(data => {
                    productSelect.innerHTML += data;
                })
                .catch(error => console.error('Error fetching products:', error));
        });

        // Add product row to the table
        addProductBtn.addEventListener('click', function() {
            const productId = productSelect.value;
            if (!productId) {
                alert('Chưa chọn sản phẩm');
                return;
            }

            const productName = productSelect.options[productSelect.selectedIndex].text;
            const price = parseFloat(productSelect.options[productSelect.selectedIndex].getAttribute('data-price'));
            const amount = 1;

            // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
            let existingRow = null;
            Array.from(receiptDetailsTable.rows).forEach(row => {
                if (row.cells[0].textContent === productName) {
                    existingRow = row;
                }
            });

            if (existingRow) {
                // Nếu sản phẩm đã có, cập nhật số lượng và giá trị
                const existingAmount = parseInt(existingRow.cells[1].querySelector('input').value);
                const newAmount = existingAmount + 1;
                existingRow.cells[1].querySelector('input').value = newAmount;
                existingRow.cells[2].querySelector('input').value = (price * newAmount).toFixed(2);

                // Cập nhật lại tổng giá trị
                totalSum += price;
                sumInput.value = totalSum.toFixed(2);

                // Cập nhật mảng sản phẩm
                const productInCart = productsInCart.find(p => p.productId === productId);
                if (productInCart) {
                    productInCart.amount = newAmount;
                    productInCart.totalPrice = newAmount * price;
                }
            } else {
                // Nếu sản phẩm chưa có, thêm mới vào bảng
                const row = receiptDetailsTable.insertRow();
                row.innerHTML = `
                <td>${productName}</td>
                <td><input type="number" min="1" class="form-control amount-input" value="1"></td>
                <td><input type="number" step="0.01" class="form-control price-input" value="${price.toFixed(2)}"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-product-btn">Remove</button></td>
            `;
                totalSum += price;
                sumInput.value = totalSum.toFixed(2);

                // Thêm sản phẩm vào mảng
                productsInCart.push({
                    productId: productId,
                    productName: productName,
                    price: price,
                    amount: 1,
                    totalPrice: price,
                });

                // Thêm sự kiện cho nút xóa sản phẩm
                row.querySelector('.remove-product-btn').addEventListener('click', function() {
                    const rowAmount = parseInt(row.querySelector('.amount-input').value);
                    const rowTotal = parseFloat(row.querySelector('.price-input').value) * rowAmount;
                    totalSum -= rowTotal;
                    sumInput.value = totalSum.toFixed(2);
                    row.remove();

                    // Xóa sản phẩm khỏi mảng
                    const productIndex = productsInCart.findIndex(p => p.productId === productId);
                    if (productIndex !== -1) {
                        productsInCart.splice(productIndex, 1);
                    }
                });

                // Cập nhật lại tổng giá trị khi thay đổi giá
                row.querySelector('.price-input').addEventListener('change', function() {
                    const newPrice = parseFloat(this.value);
                    const newAmount = parseInt(row.querySelector('.amount-input').value);
                    if (isNaN(newPrice) || newPrice <= 0) {
                        alert('Giá không hợp lệ');
                        return;
                    }

                    const productInCart = productsInCart.find(p => p.productId === productId);
                    if (productInCart) {
                        totalSum -= productInCart.totalPrice;
                        productInCart.price = newPrice;
                        productInCart.totalPrice = newPrice * newAmount;
                        totalSum += productInCart.totalPrice;
                        sumInput.value = totalSum.toFixed(2);
                    }
                });

                // Cập nhật lại tổng giá trị khi thay đổi số lượng
                row.querySelector('.amount-input').addEventListener('change', function() {
                    const newAmount = parseInt(this.value);
                    const newPrice = parseFloat(row.querySelector('.price-input').value);
                    if (isNaN(newAmount) || newAmount <= 0) {
                        alert('Số lượng không hợp lệ');
                        return;
                    }

                    const productInCart = productsInCart.find(p => p.productId === productId);
                    if (productInCart) {
                        totalSum -= productInCart.totalPrice;
                        productInCart.amount = newAmount;
                        productInCart.totalPrice = newAmount * newPrice;
                        totalSum += productInCart.totalPrice;
                        sumInput.value = totalSum.toFixed(2);
                    }
                });
            }

            // Khi bấm "Add Product", khóa provider select và tạo input ẩn cho provider_id
            providerSelect.setAttribute('readonly', 'true');
            providerHiddenInput.value = providerSelect.value;
            document.getElementById('goods-receipt-form').appendChild(providerHiddenInput);
        });

        // Serialize the table data and update the hidden input before submitting the form
        document.getElementById('goods-receipt-form').addEventListener('submit', function(e) {
            // Lấy mảng sản phẩm trong giỏ hàng và chuyển thành JSON
            goodsReceiptDataInput.value = JSON.stringify(productsInCart);

            // Thêm trường ẩn vào form cho provider_id
            providerHiddenInput.value = providerSelect.value;
            document.getElementById('goods-receipt-form').appendChild(providerHiddenInput);
        });
    });
</script>

@endsection