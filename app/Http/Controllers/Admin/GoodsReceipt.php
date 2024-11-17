<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Providers\StoreRequest;
use App\Http\Requests\Providers\UpdateRequest;
use Illuminate\Http\Request;
use App\Models\Provider;
use App\Models\Provide;
use App\Models\Product;
use App\Models\goods_receipt;
use App\Models\receipt_detail;
use Illuminate\Support\Facades\DB;

class GoodsReceipt extends Controller
{
    public function index()
    {
        return view('admin.orders.index');
    }

    public function getPaginate(Request $request)
    {
        $page = $request->input('page', 1);

        $goodsReceipts = goods_receipt::with('provider')->paginate(5);
        if ($page > $goodsReceipts->lastPage()) {
            return redirect()->route('api.GoodsReceipt.index', ['page' => $goodsReceipts->lastPage()]);
        }

        return response()->json($goodsReceipts);
    }

    public function create()
    {
        $providers = Provider::all();
        return view('admin.orders.create', compact('providers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'date' => 'required|date',
            'sum' => 'required|numeric',
        ]);

        // Tạo goods_receipt mới
        $goodsReceipt = goods_receipt::create([
            'provider_id' => $request->provider_id,
            'date' => $request->date,
            'sum' => $request->sum,
        ]);

        $productData = json_decode($request->input('goods_receipt'), true);
        foreach ($productData as $product) {
            $existingProduct = Product::find($product['productId']);
            if ($existingProduct) {
                $existingProduct->stock_quantity += $product['amount'];

                $existingProduct->price = $product['price'] * 1.1;

                $existingProduct->save();
            }

            receipt_detail::create([
                'goods_receipt_id' => $goodsReceipt->id,
                'product_id' => $product['productId'],
                'amount' => $product['amount'],
                'price' => $product['price'],
            ]);
        }

        return redirect()->route('admin.GoodsReceipt.index')->with('success', 'Lưu thành công vào database');
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'provider_id' => 'required|exists:providers,id',
    //         'date' => 'required|date',
    //         'sum' => 'required|numeric',
    //     ]);

    //     // Tạo goods_receipt mới
    //     $goodsReceipt = goods_receipt::create([
    //         'provider_id' => $request->provider_id,
    //         'date' => $request->date,
    //         'sum' => $request->sum,
    //     ]);

    //     $productData = json_decode($request->input('goods_receipt'), true);
    //     foreach ($productData as $product) {
    //         $product::update(['quantity' => $product['amount']])->where('id', $product['productId'])->first();
    //         $product->save();
    //         receipt_detail::create([
    //             'goods_receipt_id' => $goodsReceipt->id,
    //             'product_id' => $product['productId'],
    //             'amount' => $product['amount'],
    //             'price' => $product['price'],
    //         ]);
    //     }

    //     return redirect()->route('admin.GoodsReceipt.index')->with('success', 'Lưu thành công vào database');
    // }


    public function getProductsByProvider($providerId)
    {
        $products = Provide::where('provider_id', $providerId)
            ->with('product')
            ->get()
            ->pluck('product');

        $options = '';
        foreach ($products as $product) {
            $options .= '<option value="' . $product->id . '" data-price="' . $product->price . '">' . $product->name . '</option>';
        }

        return $options;
    }

    public function getReceiptDetail($id)
    {
        $receiptDetails = receipt_detail::with('product')
            ->where('goods_receipt_id', $id)
            ->get();

        if ($receiptDetails->isEmpty()) {
            return response()->json([], 404);
        }

        return response()->json($receiptDetails);
    }
}
