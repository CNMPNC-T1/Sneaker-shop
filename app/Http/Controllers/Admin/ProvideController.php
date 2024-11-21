<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseTrait;
use App\Http\Requests\Provide\StoreRequest;
use App\Http\Requests\Provide\UpdateRequest;
use App\Models\Provider;
use App\Models\Product;
use App\Models\Provide;
use Illuminate\Http\Request;

class ProvideController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        return view('admin.provide.index');
    }

    public function getPaginate(Request $request)
    {
        $page = $request->input('page', 1);

        // Query dữ liệu và include các liên kết với Provider và Product
        $provides = Provide::with(['provider', 'product'])->paginate(5);

        if ($page > $provides->lastPage()) {
            return redirect()->route('api.provide.index', ['page' => $provides->lastPage()]);
        }

        // Chuyển đổi dữ liệu để bao gồm tên thay vì ID
        $provides = $provides->map(function ($provide) {
            return [
                'id' => $provide->id,
                'provider_id' => $provide->provider_id,
                'product_id' => $provide->product_id,
                'provider_name' => $provide->provider_name,
                'product_name' => $provide->product_name,
                'status' => $provide->status,
            ];
        });

        return response()->json(['data' => $provides]);
    }

    public function create()
    {
        $providers = Provider::all();
        $products = Product::all();
        $statuss = [
            ['id' => 1, 'name' => 'True'],
            ['id' => 0, 'name' => 'False']
        ];
        return view('admin.provide.create', compact('providers', 'products', 'statuss'));
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $provides = Provide::query()
            ->where('provider_id', $data['provider'])
            ->where('product_id', $data['product'])
            ->first();

        if (!is_null($provides)) {
            return redirect()->back()->withErrors(['msg' => 'Đã có sản phẩm này rồi.']);
        }

        Provide::create([
            'provider_id' => $data['provider'],
            'product_id' => $data['product'],
            'status' => $data['status']
        ]);

        return $this->successResponse(message: 'Thành công!');
    }

    public function edit($provider_id, $product_id)
    {
        $provide = Provide::query()
            ->with(['provider', 'product'])
            ->where('provider_id', $provider_id)
            ->where('product_id', $product_id)
            ->firstOrFail();

        return view('admin.provide.edit', [
            'provide' => $provide,
            'provider_name' => $provide->provider->name,
            'product_name' => $provide->product->name,
        ]);
    }


    public function update(UpdateRequest $request, $provider_id, $product_id)
    {
        $data = $request->validated();

        $provide = Provide::where('provider_id', $provider_id)
            ->where('product_id', $product_id)
            ->firstOrFail();

        $provide->status = $data['status'];

        $provide->save();

        return $this->successResponse(message: 'Thành công!');
    }

    public function destroy($provider_id, $product_id)
    {
        try {
            $provide = Provide::query()
                ->where('provider_id', $provider_id)
                ->where('product_id', $product_id)
                ->firstOrFail();
            $provide->delete();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return $this->errorResponse("Không thành công!");
        }

        return $this->successResponse('', 'Xóa thành công!');
    }
}
