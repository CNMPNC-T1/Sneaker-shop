<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseTrait;
use App\Http\Requests\Providers\StoreRequest;
use App\Http\Requests\Providers\UpdateRequest;
use Illuminate\Http\Request;
use App\Models\Provider;

class ProviderController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        return view('admin.providers.index');
    }

    public function getPaginate(Request $request)
    {
        $page = $request->input('page', 1);

        $providers = Provider::query()->paginate(5);
        if ($page > $providers->lastPage()) {
            return redirect()->route('api.providers.index', ['page' =>  $providers->lastPage()]);
        }
        return $providers;
    }

    public function create()
    {
        return view('admin.providers.create');
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $Provider = Provider::query()->where('name', $data['name'])->first();
        if (!is_null($Provider)) {
            return redirect()->back()->withErrors(['msg' => 'Tên nhà cung cấp đã tồn tại']);
        }

        Provider::create([
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => $data['phone']
        ]);

        return $this->successResponse(message: 'Thành công!');
    }

    public function edit(string $id)
    {
        $provider = Provider::query()->findOrFail($id);
        return view(
            'admin.providers.edit',
            [
                'provider' => $provider
            ]
        );
    }

    public function update(UpdateRequest $request, string $id)
    {
        $data = $request->validated();
        $Provider = Provider::query()->where('name', $data['name'])->first();
        try {
            $Provider = Provider::query()->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return redirect()->back()->withErrors(['msg' => 'Nhà cung cấp không tồn tại']);
        }

        $Provider->fill([
            'address' => $data['address'],
            'phone' => $data['phone']
        ]);
        $Provider->save();

        return $this->successResponse(message: 'Thành công!');
    }

    public function destroy(string $id)
    {
        try {
            Provider::destroy($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return $this->errorResponse("Không thành công!");
        }
        return $this->successResponse('', 'Thành công');
    }
}
