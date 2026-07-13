<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use App\Models\PriorityKeyword;
use Illuminate\Http\Request;

class PriorityKeywordController extends Controller
{
    public function index()
    {
        $priorities = Priority::with('keywords')->get();
        return view('supervisor.priority-keywords.index', compact('priorities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'priority_id' => 'required|exists:priorities,id',
            'keyword'     => 'required|string|max:100',
            'weight'      => 'required|in:1,3,5,10',
        ]);

        $exists = PriorityKeyword::where('priority_id', $request->priority_id)
            ->where('keyword', strtolower(trim($request->keyword)))
            ->exists();

        if ($exists) {
            return back()->with('error', 'Keyword already exists for this priority!');
        }

        PriorityKeyword::create([
            'priority_id' => $request->priority_id,
            'keyword'     => strtolower(trim($request->keyword)),
            'weight'      => $request->weight,
        ]);

        return back()->with('success', 'Keyword added successfully!');
    }

    public function destroy(PriorityKeyword $priorityKeyword)
    {
        $priorityKeyword->delete();
        return back()->with('success', 'Keyword deleted successfully!');
    }
}
