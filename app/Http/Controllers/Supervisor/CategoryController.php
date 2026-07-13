<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryKeyword;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')->whereNull('deleted_at'),
            ],
            'description'   => 'nullable|string',
            'base_priority' => 'required|in:low,medium,high,critical',
            'max_priority'  => 'required|in:low,medium,high,critical',
        ]);

        $priorityOrder = ['low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];
        if ($priorityOrder[$request->base_priority] > $priorityOrder[$request->max_priority]) {
            return back()->withErrors([
                'base_priority' => 'Base priority cannot be higher than max priority!'
            ])->withInput();
        }

        // Cek tong sampah
        $existing = Category::withTrashed()
            ->where('name', $request->name)
            ->first();

        if ($existing && $existing->trashed()) {
            $existing->restore();
            $existing->update([
                'description'   => $request->description,
                'base_priority' => $request->base_priority,
                'max_priority'  => $request->max_priority,
                'is_active'     => true,
            ]);
            return back()->with('success', 'Category restored successfully!');
        }

        Category::create([
            'name'          => $request->name,
            'description'   => $request->description,
            'base_priority' => $request->base_priority,
            'max_priority'  => $request->max_priority,
            'is_active'     => true,
        ]);

        return back()->with('success', 'Category added successfully!');
    }


    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'          => [
                                'required', 'string', 'max:255',
                                Rule::unique('categories', 'name')
                                    ->ignore($category->id)
                                    ->whereNull('deleted_at'),
                                ],
            'description'   => 'nullable|string',
            'base_priority' => 'required|in:low,medium,high,critical',
            'max_priority'  => 'required|in:low,medium,high,critical',
        ]);

        // Validasi base <= max
        $priorityOrder = ['low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];
        if ($priorityOrder[$request->base_priority] > $priorityOrder[$request->max_priority]) {
            return back()->withErrors([
                'base_priority' => 'Base priority cannot be higher than max priority!'
            ])->withInput();
        }

        $category->update([
            'name'          => $request->name,
            'description'   => $request->description,
            'base_priority' => $request->base_priority,
            'max_priority'  => $request->max_priority,
        ]);

        return back()->with('success', 'Category updated successfully!');
    }

    public function toggle(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        $status = $category->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Category {$category->name} successfully {$status}!");
    }

    public function destroy(Category $category)
    {
        if ($category->tickets()->whereNotIn('status', ['closed'])->count() > 0) {
            return back()->with('error', 'Cannot delete category that still has active tickets!');
        }

        $category->keywords()->delete();
        $category->delete();
        return back()->with('success', 'Category successfully deleted!');
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
            return back()->with('error', 'Keyword already exists!');
        }

        CategoryKeyword::create([
            'category_id' => $category->id,
            'keyword'     => strtolower(trim($request->keyword)),
            'weight'      => $request->weight,
        ]);

        return back()->with('success', 'Keyword added successfully!');
    }

    public function destroyKeyword(CategoryKeyword $keyword)
    {
        $keyword->delete();
        return back()->with('success', 'Keyword deleted successfully!');
    }
}
