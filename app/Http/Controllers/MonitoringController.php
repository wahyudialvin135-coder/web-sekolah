<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Accreditation;
use App\Models\MonitoringLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    /**
     * Get list of schools with filters.
     */
    private function getFilteredSchools(Request $request, $user)
    {
        if ($user->isOperator()) {
            return School::with('latestAccreditation')
                ->where('id', $user->school_id)
                ->get();
        }

        $query = School::with('latestAccreditation');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('npsn', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        if ($request->filled('priority')) {
            $query->where('is_priority', $request->priority === 'yes');
        }

        $schools = $query->get();

        // Filter by Accreditation Grade (A, B, C, TT)
        if ($request->filled('grade')) {
            $grade = $request->grade;
            $schools = $schools->filter(function($school) use ($grade) {
                $latest = $school->latestAccreditation;
                if ($grade === 'TT') {
                    return !$latest || $latest->grade === 'TT';
                }
                return $latest && $latest->grade === $grade;
            });
        }

        // Filter by Monitoring Status
        if ($request->filled('status')) {
            $status = $request->status;
            $schools = $schools->where('monitoring_status', $status);
        }

        return $schools->values();
    }

    /**
     * List monitoring page.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $schools = $this->getFilteredSchools($request, $user);

        // List of all districts in Bojonegoro for filter dropdown
        $districts = School::pluck('district')->unique()->sort()->values();

        return Inertia::render('Monitoring/Index', [
            'schools' => $schools,
            'districts' => $districts,
            'filters' => $request->only(['search', 'type', 'district', 'grade', 'status', 'priority']),
        ]);
    }

    /**
     * Get single school details.
     */
    public function show(Request $request, School $school)
    {
        $user = $request->user();

        // Operator restriction
        if ($user->isOperator() && $user->school_id != $school->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk sekolah ini.');
        }

        $school->load(['accreditations' => function($q) {
            $q->orderBy('expiry_date', 'desc');
        }, 'logs.user']);

        return Inertia::render('Monitoring/Detail', [
            'school' => $school,
        ]);
    }

    /**
     * Toggle priority status for pembinaan.
     */
    public function togglePriority(Request $request, School $school)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            abort(403, 'Hanya Admin Dinas yang dapat mengubah status prioritas pembinaan.');
        }

        $school->is_priority = !$school->is_priority;
        $school->save();

        MonitoringLog::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'action' => $school->is_priority ? 'Menandai Prioritas' : 'Menghapus Prioritas',
            'notes' => 'Mengubah prioritas pembinaan sekolah.'
        ]);

        return redirect()->back();
    }

    /**
     * Update school notes/catatan pembinaan.
     */
    public function updateNotes(Request $request, School $school)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            abort(403, 'Hanya Admin Dinas yang dapat memperbarui catatan pembinaan.');
        }

        $request->validate([
            'notes' => 'nullable|string'
        ]);

        $school->notes = $request->notes;
        $school->save();

        MonitoringLog::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'action' => 'Memperbarui Catatan',
            'notes' => $request->notes ?? 'Catatan dikosongkan.'
        ]);

        return redirect()->back();
    }

    /**
     * Map view page.
     */
    public function mapView(Request $request)
    {
        $user = $request->user();
        $schools = $this->getFilteredSchools($request, $user);
        $districts = School::pluck('district')->unique()->sort()->values();

        return Inertia::render('Map/Index', [
            'schools' => $schools,
            'districts' => $districts,
            'filters' => $request->only(['type', 'district', 'grade', 'status']),
        ]);
    }

    /**
     * Laporan index page.
     */
    public function reportView(Request $request)
    {
        $user = $request->user();
        
        if ($user->isOperator()) {
            abort(403, 'Operator tidak memiliki hak akses halaman laporan.');
        }

        $schools = $this->getFilteredSchools($request, $user);
        $districts = School::pluck('district')->unique()->sort()->values();

        return Inertia::render('Report/Index', [
            'schools' => $schools,
            'districts' => $districts,
            'filters' => $request->only(['type', 'district', 'grade', 'status', 'priority']),
        ]);
    }

    /**
     * Print layout page.
     */
    public function printReport(Request $request)
    {
        $user = $request->user();
        
        if ($user->isOperator()) {
            abort(403, 'Operator tidak memiliki hak akses halaman cetak laporan.');
        }

        $schools = $this->getFilteredSchools($request, $user);

        return Inertia::render('Report/Print', [
            'schools' => $schools,
            'printedAt' => Carbon::now()->translatedFormat('d F Y H:i'),
        ]);
    }

    /**
     * Export to CSV Excel format.
     */
    public function exportCsv(Request $request)
    {
        $user = $request->user();

        if ($user->isOperator()) {
            abort(403, 'Operator tidak memiliki hak akses ekspor data.');
        }

        $schools = $this->getFilteredSchools($request, $user);

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=Laporan_Monitoring_Akreditasi_Bojonegoro_' . Carbon::now()->format('YmdHis') . '.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($schools) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility (crucial for Indonesian text/accents in Excel)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'NPSN',
                'Nama Sekolah',
                'Jenjang',
                'Kecamatan',
                'Alamat',
                'Predikat Akreditasi',
                'Nomor SK',
                'Tanggal Terbit',
                'Tanggal Habis Berlaku',
                'Status Monitoring',
                'Prioritas Pembinaan',
                'Catatan Pembinaan'
            ], ';'); // Use semicolon for direct excel parsing in Indonesian settings

            foreach ($schools as $school) {
                $latest = $school->latestAccreditation;
                fputcsv($file, [
                    $school->npsn,
                    $school->name,
                    $school->type,
                    $school->district,
                    $school->address,
                    $latest ? $latest->grade : 'TT',
                    $latest ? $latest->certificate_number : '-',
                    $latest ? ($latest->issue_date ? $latest->issue_date->format('d-m-Y') : '-') : '-',
                    $latest ? ($latest->expiry_date ? $latest->expiry_date->format('d-m-Y') : '-') : '-',
                    $school->monitoring_status,
                    $school->is_priority ? 'YA' : 'TIDAK',
                    $school->notes ?? '-'
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
