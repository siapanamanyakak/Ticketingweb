<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Services\UsernameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmployeeImportController extends Controller
{
    public function template()
    {
        // Download template Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Karyawan');

        // Header
        $headers = ['Nama Lengkap', 'ID Staff', 'Departemen', 'Email (Opsional)'];
        foreach ($headers as $i => $header) {
            $col = chr(65 + $i); // A, B, C, D
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                           'startColor' => ['rgb' => '0F2044']],
            ]);
        }

        // Contoh data
        $sheet->setCellValue('A2', 'John Doe');
        $sheet->setCellValue('B2', 'STF-100');
        $sheet->setCellValue('C2', 'Finance');
        $sheet->setCellValue('D2', 'john@email.com');

        // Auto width
        foreach (['A', 'B', 'C', 'D'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'template-import-karyawan.xlsx';
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

        // Skip header row
        array_shift($rows);

        $imported  = 0;
        $skipped   = 0;
        $errors    = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2 karena skip header

            // Skip baris kosong
            if (empty($row[0]) && empty($row[1])) continue;

            $name       = trim($row[0] ?? '');
            $idStaff    = trim($row[1] ?? '');
            $deptName   = trim($row[2] ?? '');
            $email      = trim($row[3] ?? '') ?: null;

            // Validasi nama & id_staff wajib
            if (empty($name) || empty($idStaff)) {
                $errors[] = "Baris {$rowNum}: Nama dan ID Staff wajib diisi.";
                $skipped++;
                continue;
            }

            // Cek duplikat id_staff
            if (User::where('id_staff', $idStaff)->exists()) {
                $errors[] = "Baris {$rowNum}: ID Staff '{$idStaff}' sudah terdaftar.";
                $skipped++;
                continue;
            }

            // Cek duplikat email
            if ($email && User::where('email', $email)->exists()) {
                $errors[] = "Baris {$rowNum}: Email '{$email}' sudah terdaftar.";
                $skipped++;
                continue;
            }

            // Cari department
            $department = Department::where('name', 'like', '%' . $deptName . '%')
                                    ->where('is_active', true)
                                    ->first();

            if (!$department && !empty($deptName)) {
                $errors[] = "Baris {$rowNum}: Departemen '{$deptName}' tidak ditemukan. Karyawan tetap dibuat tanpa departemen.";
            }

            // Buat user
            User::create([
                'name'          => $name,
                'username'      => $usernameService->generate($name),
                'email'         => $email,
                'password'      => Hash::make('password123'),
                'id_staff'      => $idStaff,
                'department_id' => $department?->id,
                'role'          => 'user',
                'is_active'     => true,
            ]);

            $imported++;
        }

        $message = "Import selesai: {$imported} berhasil, {$skipped} dilewati.";

        return back()
            ->with('success', $message)
            ->with('import_errors', $errors);
    }
}
