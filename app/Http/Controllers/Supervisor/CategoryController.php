<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryKeyword;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('keywords')->latest()->paginate(15);
        return view('supervisor.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description'   => 'nullable|string',
        ]);

        Category::create([
            'name' => $request->name,
            'description'   => $request->description,
            'is_active'     => true,
        ]);
        $request->validate([
        'name' => 'required|string|max:255|unique:categories,name',
        'description'   => 'nullable|string',
        'base_priority' => 'required|in:low,medium,high,critical',
        'max_priority'  => 'required|in:low,medium,high,critical',
        ]);

        Category::create([
        'name' => $request->name,
        'description'   => $request->description,
        'base_priority' => $request->base_priority,
        'max_priority'  => $request->max_priority,
        'is_active'     => true,
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description'   => 'nullable|string',
        ]);

        $category->update([
            'name' => $request->name,
            'description'   => $request->description,
        ]);
        $request->validate([
        'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        'description'   => 'nullable|string',
        'base_priority' => 'required|in:low,medium,high,critical',
        'max_priority'  => 'required|in:low,medium,high,critical',
        ]);

        $category->update([
        'name' => $request->name,
        'description'   => $request->description,
        'base_priority' => $request->base_priority,
        'max_priority'  => $request->max_priority,
        ]);

        return back()->with('success', 'Kategori berhasil diperbarui!');
    }

    public function toggle(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Kategori {$category->name} berhasil {$status}!");
    }

    public function destroy(Category $category)
    {
        if ($category->tickets()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus kategori yang masih memiliki tiket!');
        }

        $category->keywords()->delete();
        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus!');
    }

    // ── Keyword Management ────────────────────────
    public function storeKeyword(Request $request, Category $category)
    {
        $request->validate([
            'keyword' => 'required|string|max:100',
            'weight'  => 'required|in:1,3,5,10',
        ]);

        $exists = CategoryKeyword::where('category_id', $category->id)
            ->where('keyword', strtolower(trim($request->keyword)))
            ->exists();

        if ($exists) {
            return back()->with('error', 'Keyword sudah ada!');
        }

        CategoryKeyword::create([
            'category_id' => $category->id,
            'keyword'     => strtolower(trim($request->keyword)),
            'weight'      => $request->weight,
        ]);

        return back()->with('success', 'Keyword berhasil ditambahkan!');
    }

    public function destroyKeyword(CategoryKeyword $keyword)
    {
        $keyword->delete();
        return back()->with('success', 'Keyword berhasil dihapus!');
    }
}
