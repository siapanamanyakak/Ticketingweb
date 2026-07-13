<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::with('creator')->latest()->paginate(15);
        return view('support.news.index', compact('news'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'required|in:info,warning,maintenance',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
        ]);

        News::create([
            'title'       => $request->title,
            'description' => $request->description,
            'type'        => $request->type,
            'created_by'  => auth()->id(),
            'is_active'   => true,
            'starts_at'   => $request->starts_at,
            'ends_at'     => $request->ends_at,
        ]);

        return back()->with('success', 'News published successfully!');
    }

    public function update(Request $request, News $news)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'required|in:info,warning,maintenance',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
        ]);

        $news->update([
            'title'       => $request->title,
            'description' => $request->description,
            'type'        => $request->type,
            'starts_at'   => $request->starts_at,
            'ends_at'     => $request->ends_at,
        ]);

        return back()->with('success', 'News updated successfully!');
    }

    public function toggle(News $news)
    {
        $news->update(['is_active' => !$news->is_active]);
        $status = $news->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "News successfully {$status}!");
    }

    public function destroy(News $news)
    {
        $news->delete();
        return back()->with('success', 'News deleted successfully!');
    }
}
