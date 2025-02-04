<?php
namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use App\Http\Resources\MenuResource;
use App\Http\Requests\MenuRequest;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::with('category');

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by availability
        if ($request->has('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort by price
        if ($request->has('sort_price')) {
            $direction = $request->sort_price === 'desc' ? 'desc' : 'asc';
            $query->orderBy('price', $direction);
        }

        $menus = $query->paginate($request->per_page ?? 10);
        return MenuResource::collection($menus);
    }

    public function store(MenuRequest $request)
    {
        try {
            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request);
            }

            $menu = Menu::create($data);
            return new MenuResource($menu);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan menu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Menu $menu)
    {
        return new MenuResource($menu->load('category'));
    }

    public function update(MenuRequest $request, Menu $menu)
    {
        try {
            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                $this->deleteImage($menu->image);
                $data['image'] = $this->uploadImage($request);
            }

            $menu->update($data);
            return new MenuResource($menu);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengupdate menu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Menu $menu)
    {
        try {
            // Delete image if exists
            $this->deleteImage($menu->image);

            $menu->delete();
            return response()->json([
                'message' => 'Menu berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus menu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function uploadImage(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('menu', $filename, 'public');
            return $path;
        }
        return null;
    }

    private function deleteImage($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    // Additional Methods

    public function toggleAvailability(Menu $menu)
    {
        $menu->update([
            'is_available' => !$menu->is_available
        ]);

        return new MenuResource($menu);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:menus,id'
        ]);

        $menus = Menu::whereIn('id', $request->ids)->get();

        foreach ($menus as $menu) {
            $this->deleteImage($menu->image);
        }

        Menu::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Menu berhasil dihapus'
        ]);
    }
}
