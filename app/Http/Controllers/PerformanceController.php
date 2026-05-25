<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    /**
     * Display performance history page
     */
    public function index(Request $request)
    {
        // Sample data - replace with actual database query
        $performances = [
            (object)[
                'id' => 1,
                'date' => '02/25/2026',
                'semester' => 'First',
                'year' => '2026'
            ],
            (object)[
                'id' => 2,
                'date' => '11/25/2026',
                'semester' => 'Second',
                'year' => '2026'
            ]
        ];

        // Filter logic
        $selectedSemester = $request->get('semester', 'all');
        $selectedYear = $request->get('year', '2026');

        return view('performance', compact(
            'performances', 
            'selectedSemester', 
            'selectedYear'
        ));
    }

    /**
     * View specific performance record
     */
    public function show($id)
    {
        // Fetch and display specific performance record
        return view('performance', compact('id'));
    }

    /**
     * Download performance report
     */
    public function downloadReport(Request $request)
    {
        $semester = $request->get('semester', 'all');
        $year = $request->get('year', date('Y'));

        $reportPath = storage_path('app/reports/performance-report.pdf');
        if (!file_exists($reportPath)) {
            $dir = dirname($reportPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            // Generate a minimal valid PDF containing the performance metadata
            $dummyPdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >>\nendobj\n4 0 obj\n<< /Length 120 >>\nstream\nBT\n/F1 12 Tf\n70 700 Td\n(DSWD IPCRF Performance Report) Tj\n0 -20 Td\n(Semester: " . $semester . ") Tj\n0 -20 Td\n(Year: " . $year . ") Tj\nET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\n0000000201 00000 n\ntrailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n371\n%%EOF\n";
            file_put_contents($reportPath, $dummyPdfContent);
        }

        return response()->download(
            $reportPath,
            "Performance_Report_{$semester}_{$year}.pdf"
        );
    }
}