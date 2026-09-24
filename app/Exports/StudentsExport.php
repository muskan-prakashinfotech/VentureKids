<?php

namespace App\Exports;

use App\Models\Students;
use App\Models\Grade;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StudentsExport implements FromQuery, WithHeadings, WithMapping, 
                                ShouldAutoSize, WithStyles, WithChunkReading
{
    protected $schoolId;
    protected $gradesMap;

    public function __construct($schoolId)
    {
        $this->schoolId = $schoolId;
        $this->gradesMap = Grade::pluck('grade', 'id');
    }

    public function query()
    {
        return Students::withoutGlobalScopes()  
            ->with([
                'user',                     
                'getAssignedGrade', 
                'getAssignedBatch', 
            ])
            ->where('school_id', $this->schoolId);
    }

    public function headings(): array
    {
        return [
            'First Name',
            'Last Name',
            'Username',
            'Email',
            'Grade',
            'Batch',
            'Levels They Have Access To Currently',
        ];
    }

    public function map($student): array
    {
        $gradeIds = array_filter(explode(',', $student->grade_id ?? ''));

        $levels = collect($gradeIds)
            ->map(fn($id) => $this->gradesMap->get(trim($id)))
            ->filter()
            ->implode(', ');

        [$firstName, $lastName] = $this->resolveStudentNames($student);

        return [
            $firstName,
            $lastName,
            $student->user?->username           ?? 'N/A',
            $student->user?->email              ?? 'N/A',
            $student->getAssignedGrade?->name  ?? 'N/A',
            $student->getAssignedBatch?->batch_name  ?? 'N/A',
            $levels ?: 'N/A',
        ];
    }

    protected function resolveStudentNames($student): array
    {
        $firstName = trim((string) ($student->user?->first_name ?? ''));
        $lastName = trim((string) ($student->user?->last_name ?? ''));

        if ($firstName !== '' || $lastName !== '') {
            return [$firstName ?: 'N/A', $lastName ?: 'N/A'];
        }

        $fullName = trim((string) ($student->user?->name ?? $student->name ?? ''));

        if ($fullName === '') {
            return ['N/A', 'N/A'];
        }

        $nameParts = preg_split('/\s+/', $fullName, 2);

        return [
            $nameParts[0] ?? 'N/A',
            $nameParts[1] ?? 'N/A',
        ];
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 12,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
