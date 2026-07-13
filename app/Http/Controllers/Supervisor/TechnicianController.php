<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\UsernameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TechnicianController extends Controller
{
    public function index()
    {
        $technicians = User::with('department')
            ->where('role', 'it_support')
            ->latest()
            ->paginate(10);

        return view('supervisor.technicians.index', compact('technicians'));
    }

    public function store(Request $request, UsernameService $usernameService)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => [
                'required', 'string', 'alpha_dash', 'max:50',
                Rule::unique('users', 'username')->whereNull('deleted_at'),
            ],
            'email'         => [
                'nullable', 'email',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password'      => 'required|min:8|confirmed',
            'id_staff'      => [
                'required', 'string',
                Rule::unique('users', 'id_staff')->whereNull('deleted_at'),
            ],
            'department_id' => [
                                    'required',
                                    Rule::exists('departments', 'id')->whereNull('deleted_at'),
                                ],
        ]);

        // Cek tong sampah by id_staff
        $existing = User::withTrashed()
            ->where('id_staff', $request->id_staff)
            ->first();

        if ($existing && $existing->trashed()) {
            $existing->restore();
            $existing->update([
                'name'          => $request->name,
                'username'      => strtolower($request->username),
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'department_id' => $request->department_id,
                'is_active'     => true,
                'role'          => 'it_support',
            ]);
            return back()->with('success', 'Technician account restored successfully!');
        }

        User::create([
            'name'          => $request->name,
            'username'      => strtolower($request->username),
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'id_staff'      => $request->id_staff,
            'department_id' => $request->department_id,
            'role'          => 'it_support',
            'is_active'     => true,
        ]);

        return back()->with('success', 'Technician account created successfully!');
    }

    public function edit(User $user)
    {
        $departments = Department::all();
        return view('supervisor.technicians.edit', compact('user', 'departments'));
    }

    public function update(Request $request, User $technician)
    {
        $request->validate([
            'name'          => 'required|string|max:255', // Validasi nama yang benar
            'username'      => [
                'required', 'string', 'alpha_dash', 'max:50',
                Rule::unique('users', 'username')->ignore($technician->id)->whereNull('deleted_at'),
            ],
            'email'         => [
                'nullable', 'email', // Boleh kosong
                Rule::unique('users', 'email')->ignore($technician->id)->whereNull('deleted_at'),
            ],
            'id_staff'      => [
                'required', 'string',
                Rule::unique('users', 'id_staff')->ignore($technician->id)->whereNull('deleted_at'), // Jangan lupa ignore-nya!
            ],
            'department_id' => [
                'required',
                Rule::exists('departments', 'id')->whereNull('deleted_at'),
            ],
            'password'      => 'nullable|min:8|confirmed',
        ]);

        $updateData = [
            'name'          => $request->name,
            'email'         => $request->email,
            'id_staff'      => $request->id_staff,
            'department_id' => $request->department_id,
            // Hapus fungsi generate, langsung ambil hasil inputan form
            'username'      => strtolower($request->username),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $technician->update($updateData);

        return redirect()->route('supervisor.technicians.index')
            ->with('success', 'Technician data updated successfully!');
    }

    public function destroy(User $technician)
    {
        if ($technician->tickets()->whereNotIn('status', ['closed'])->count() > 0) {
            return back()->with('error', 'Cannot delete technician who still has active tickets!');
        }

        $technician->delete();
        return back()->with('success', 'Technician deleted successfully!');
    }

    public function toggle(User $technician)
    {
        $technician->update(['is_active' => !$technician->is_active]);
        $status = $technician->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Account {$technician->name} has been {$status}!");
    }
    public function importTemplate()
{
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Template Import Technician');

    $headers = ['Full Name', 'NIK', 'Department', 'Email (Optional)'];
    foreach ($headers as $i => $header) {
        $col = chr(65 + $i);
        $sheet->setCellValue($col . '1', $header);
        $sheet->getStyle($col . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                       'startColor' => ['rgb' => '1d4ed8']],
        ]);
    }

    $sheet->setCellValue('A2', 'Budi Santoso');
    $sheet->setCellValue('B2', 'STF-010');
    $sheet->setCellValue('C2', 'IT');
    $sheet->setCellValue('D2', 'budi@ktushipyard.com');

    foreach (['A', 'B', 'C', 'D'] as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $filename = 'template-import-teknisi.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

public function import(Request $request, UsernameService $usernameService)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls|max:2048',
    ]);

    $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
    $sheet       = $spreadsheet->getActiveSheet();
    $rows        = $sheet->toArray();

    array_shift($rows);

    $imported = 0;
    $skipped  = 0;
    $errors   = [];

    foreach ($rows as $index => $row) {
        $rowNum  = $index + 2;

        if (empty($row[0]) && empty($row[1])) continue;

        $name    = trim($row[0] ?? '');
        $idStaff = trim($row[1] ?? '');
        $deptName= trim($row[2] ?? '');
        $email   = trim($row[3] ?? '') ?: null;

        if (empty($name) || empty($idStaff)) {
            $errors[] = "Row {$rowNum}: Full Name and NIK are required.";
            $skipped++;
            continue;
        }

        if (User::where('id_staff', $idStaff)->exists()) {
            $errors[] = "Row {$rowNum}: NIK '{$idStaff}' is already registered.";
            $skipped++;
            continue;
        }

        if ($email && User::where('email', $email)->exists()) {
            $errors[] = "Row {$rowNum}: Email '{$email}' is already registered.";
            $skipped++;
            continue;
        }

        $department = Department::where('name', 'like', '%' . $deptName . '%')
                                            ->where('is_active', true)
                                            ->first();

        if (!$department && !empty($deptName)) {
            $errors[] = "Row {$rowNum}: Department '{$deptName}' not found.";
        }

        User::create([
            'name'          => $name,
            'username'      => $usernameService->generate($name),
            'email'         => $email,
            'password'      => Hash::make('password123'),
            'id_staff'      => $idStaff,
            'department_id' => $department?->id,
            'role'          => 'it_support',
            'is_active'     => true,
        ]);

        $imported++;
    }

    $message = "Import completed: {$imported} successful, {$skipped} skipped.";

    return back()->with('success', $message)->with('import_errors', $errors);
}
}
