<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Accreditation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // If the user is an operator, they only see their own school's dashboard summary
        if ($user->isOperator()) {
            if (!$user->school_id) {
                return Inertia::render('Dashboard', [
                    'isOperator' => true,
                    'hasSchool' => false,
                    'school' => null
                ]);
            }

            $school = School::with('latestAccreditation')->find($user->school_id);
            return Inertia::render('Dashboard', [
                'isOperator' => true,
                'hasSchool' => true,
                'school' => $school
            ]);
        }

        // Admin & Pimpinan see the full dashboard monitoring
        $schools = School::with('latestAccreditation')->get();

        // 1. Calculations for Statistics Cards
        $totalSD = $schools->where('type', 'SD')->count();
        $totalSMP = $schools->where('type', 'SMP')->count();
        
        $countA = $schools->filter(fn($s) => $s->latestAccreditation?->grade === 'A')->count();
        $countB = $schools->filter(fn($s) => $s->latestAccreditation?->grade === 'B')->count();
        $countC = $schools->filter(fn($s) => $s->latestAccreditation?->grade === 'C')->count();
        $countTT = $schools->filter(fn($s) => !$s->latestAccreditation || $s->latestAccreditation->grade === 'TT')->count();
        
        $expiring12m = $schools->where('monitoring_status', 'Habis dalam 12 Bulan')->count();
        $expiring6m = $schools->where('monitoring_status', 'Habis dalam 6 Bulan')->count();
        $expired = $schools->where('monitoring_status', 'Kadaluarsa')->count();

        // 2. Notifications (Urgent attention needed)
        // Filter schools that are expired, or expiring in 6 / 12 months
        $notifications = $schools->filter(function($s) {
            return in_array($s->monitoring_status, ['Habis dalam 6 Bulan', 'Habis dalam 12 Bulan', 'Kadaluarsa']);
        })->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'npsn' => $s->npsn,
                'type' => $s->type,
                'status' => $s->monitoring_status,
                'color' => $s->monitoring_status_color,
                'expiry_date' => $s->latestAccreditation?->expiry_date?->format('d-m-Y') ?? '-',
            ];
        })->values();

        // 3. Chart Data: Akreditasi per Kecamatan
        $districts = $schools->pluck('district')->unique()->sort()->values();
        $chartKecamatan = [];
        foreach ($districts as $district) {
            $districtSchools = $schools->where('district', $district);
            $chartKecamatan[] = [
                'kecamatan' => $district,
                'A' => $districtSchools->filter(fn($s) => $s->latestAccreditation?->grade === 'A')->count(),
                'B' => $districtSchools->filter(fn($s) => $s->latestAccreditation?->grade === 'B')->count(),
                'C' => $districtSchools->filter(fn($s) => $s->latestAccreditation?->grade === 'C')->count(),
                'TT' => $districtSchools->filter(fn($s) => !$s->latestAccreditation || $s->latestAccreditation->grade === 'TT')->count(),
            ];
        }

        // 4. Chart Data: Akreditasi per Jenjang
        $chartJenjang = [
            'SD' => [
                'A' => $schools->where('type', 'SD')->filter(fn($s) => $s->latestAccreditation?->grade === 'A')->count(),
                'B' => $schools->where('type', 'SD')->filter(fn($s) => $s->latestAccreditation?->grade === 'B')->count(),
                'C' => $schools->where('type', 'SD')->filter(fn($s) => $s->latestAccreditation?->grade === 'C')->count(),
                'TT' => $schools->where('type', 'SD')->filter(fn($s) => !$s->latestAccreditation || $s->latestAccreditation->grade === 'TT')->count(),
            ],
            'SMP' => [
                'A' => $schools->where('type', 'SMP')->filter(fn($s) => $s->latestAccreditation?->grade === 'A')->count(),
                'B' => $schools->where('type', 'SMP')->filter(fn($s) => $s->latestAccreditation?->grade === 'B')->count(),
                'C' => $schools->where('type', 'SMP')->filter(fn($s) => $s->latestAccreditation?->grade === 'C')->count(),
                'TT' => $schools->where('type', 'SMP')->filter(fn($s) => !$s->latestAccreditation || $s->latestAccreditation->grade === 'TT')->count(),
            ]
        ];

        // 5. Chart Data: Status Monitoring (Aktif vs Hampir Habis vs Kadaluarsa)
        $chartStatus = [
            'Aktif' => $schools->where('monitoring_status', 'Aktif')->count(),
            'Habis 12 Bulan' => $expiring12m,
            'Habis 6 Bulan' => $expiring6m,
            'Kadaluarsa' => $expired,
        ];

        // 6. Chart Data: Tren habis akreditasi dalam 12 bulan kedepan (Bulanan)
        $monthsTrend = [];
        $countsTrend = [];
        for ($i = 0; $i < 12; $i++) {
            $monthDate = Carbon::now()->addMonths($i);
            $monthName = $monthDate->translatedFormat('F Y');
            $monthsTrend[] = $monthName;

            // Count schools expiring in this specific month
            $count = $schools->filter(function($s) use ($monthDate) {
                if (!$s->latestAccreditation) return false;
                $expiry = Carbon::parse($s->latestAccreditation->expiry_date);
                return $expiry->year === $monthDate->year && $expiry->month === $monthDate->month;
            })->count();

        }

        $chartTrend = [
            'labels' => $monthsTrend,
            'data' => $countsTrend
        ];

        $mapSchools = $schools->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'npsn' => $s->npsn,
                'type' => $s->type,
                'latitude' => $s->latitude,
                'longitude' => $s->longitude,
                'monitoring_status' => $s->monitoring_status,
                'grade' => $s->latestAccreditation?->grade ?? 'TT',
            ];
        })->values();

        return Inertia::render('Dashboard', [
            'isOperator' => false,
            'stats' => [
                'totalSD' => $totalSD,
                'totalSMP' => $totalSMP,
                'countA' => $countA,
                'countB' => $countB,
                'countC' => $countC,
                'countTT' => $countTT,
                'expiring12m' => $expiring12m,
                'expiring6m' => $expiring6m,
                'expired' => $expired,
            ],
            'notifications' => $notifications,
            'mapSchools' => $mapSchools,
            'chartKecamatan' => $chartKecamatan,
            'chartJenjang' => $chartJenjang,
            'chartStatus' => $chartStatus,
            'chartTrend' => $chartTrend,
        ]);
    }
}
