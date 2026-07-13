<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Sla;
use Illuminate\Http\Request;

class SlaController extends Controller
{
    public function index()
    {
        $slas = Sla::with('priority')->get();
        return view('supervisor.sla.index', compact('slas'));
    }

    public function update(Request $request, Sla $sla)
    {
        $request->validate([
            'response_time'      => 'required|integer|min:1',
            'resolution_time'    => 'required|integer|min:1',
            'working_hours_only' => 'required|in:0,1',
        ]);

        $sla->update([
            'response_time'      => $request->response_time,
            'resolution_time'    => $request->resolution_time,
            'working_hours_only' => $request->working_hours_only,
        ]);

        return back()->with('success', 'SLA settings updated successfully!');
    }
}
