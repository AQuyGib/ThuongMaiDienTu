<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $employees;

    public function __construct($employees)
    {
        $this->employees = $employees;
    }

    public function collection()
    {
        return $this->employees;
    }

    public function title(): string
    {
        return 'Danh sách nhân viên';
    }

    public function headings(): array
    {
        return [
            ['BÁO CÁO DANH SÁCH NHÂN VIÊN HỆ THỐNG'], // Dòng tiêu đề lớn
            ['Ngày xuất báo cáo: ' . now()->format('d/m/Y H:i:s')], // Dòng ngày xuất
            [], // Dòng trống
            [
                'Mã NV',
                'Họ tên',
                'Email',
                'Số điện thoại',
                'Vai trò',
                'Trạng thái',
                'Ngày tham gia'
            ]
        ];
    }

    public function map($employee): array
    {
        $statusLabel = $employee->status === 'Active' ? 'Đang làm việc' : 'Tạm dừng';
        $roleName = $employee->role?->name ?? 'N/A';
        $createdAt = $employee->created_at ? $employee->created_at->format('d/m/Y H:i') : '';

        return [
            'EMP-' . $employee->user_id,
            $employee->full_name,
            $employee->email,
            $employee->phone_number ?? '',
            $roleName,
            $statusLabel,
            $createdAt
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Gộp ô cho dòng tiêu đề lớn
        $sheet->mergeCells('A1:G1');
        
        // Gộp ô cho dòng ngày xuất
        $sheet->mergeCells('A2:G2');

        // Định dạng tiêu đề lớn
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4F46E5'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Định dạng dòng ngày xuất
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('6B7280'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Định dạng phần tiêu đề bảng ở dòng 4
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // Indigo-600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '312E81'], // Indigo dark border
                ],
            ],
        ];

        $sheet->getStyle('A4:G4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Định dạng toàn bộ dòng dữ liệu (bắt đầu từ dòng 5)
        $totalRows = count($this->employees) + 4;
        if ($totalRows >= 5) {
            $sheet->getStyle('A5:G' . $totalRows)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            
            // Kẻ viền mỏng và canh chỉnh dữ liệu
            $sheet->getStyle('A5:G' . $totalRows)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'], // Gray border
                    ],
                ],
            ]);

            // Căn giữa mã nhân viên, số điện thoại, trạng thái và ngày tham gia
            $sheet->getStyle('A5:A' . $totalRows)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D5:D' . $totalRows)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F5:G' . $totalRows)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Tô màu xen kẽ cho các hàng (Zebra Striping)
            for ($row = 5; $row <= $totalRows; $row++) {
                $sheet->getRowDimension($row)->setRowHeight(22);
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':G' . $row)->getFill()->applyFromArray([
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB'],
                    ]);
                }

                // Định dạng màu chữ trạng thái: Xanh cho đang làm việc, đỏ cho tạm dừng
                $statusVal = $sheet->getCell('F' . $row)->getValue();
                if ($statusVal === 'Đang làm việc') {
                    $sheet->getStyle('F' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('059669'))->setBold(true);
                } elseif ($statusVal === 'Tạm dừng') {
                    $sheet->getStyle('F' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('DC2626'))->setBold(true);
                }
            }
        }

        return [];
    }
}
