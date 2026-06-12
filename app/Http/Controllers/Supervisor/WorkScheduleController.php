<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    public function index()
    {
        $schedules = WorkSchedule::orderBy('day_of_week')->get();
        return view('supervisor.work-schedules.index', compact('schedules'));
    }

    public function update(Request $request, WorkSchedule $workSchedule)
    {
        $request->validate([
            'start_time'     => 'required|date_format:H:i',
            'end_time'       => 'required|date_format:H:i|after:start_time',
            'is_working_day' => 'boolean',
        ]);

        $workSchedule->update([
            'start_time'     => $request->start_time,
            'end_time'       => $request->end_time,
            'is_working_day' => $request->boolean('is_working_day'),
        ]);

        return back()->with('success', 'Jadwal kerja berhasil diperbarui!');
    }
}
