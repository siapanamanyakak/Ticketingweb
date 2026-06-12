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

    public function edit(Sla $sla)
    {
        $sla->load('priority');
        return view('supervisor.sla.edit', compact('sla'));
    }

    public function update(Request $request, Sla $sla)
    {
        $request->validate([
            'response_time'      => 'required|integer|min:1',
            'resolution_time'    => 'required|integer|min:1',
            'working_hours_only' => 'boolean',
        ]);

        $sla->update([
            'response_time'      => $request->response_time,
            'resolution_time'    => $request->resolution_time,
            'working_hours_only' => $request->boolean('working_hours_only'),
        ]);

        return redirect()->route('supervisor.sla.index')
            ->with('success', 'Pengaturan SLA berhasil diperbarui!');
    }
}
