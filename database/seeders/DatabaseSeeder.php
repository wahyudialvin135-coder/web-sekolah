<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use App\Models\Accreditation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 28 Kecamatan coordinates in Bojonegoro
        $kecCoords = [
            'MARGOMULYO' => [-7.310, 111.521],
            'NGRAHO' => [-7.261, 111.581],
            'TAMBAKREJO' => [-7.211, 111.641],
            'NGAMBON' => [-7.265, 111.695],
            'SEKAR' => [-7.352, 111.771],
            'BUBULAN' => [-7.284, 111.821],
            'GONDANG' => [-7.321, 111.865],
            'TEMAYANG' => [-7.291, 111.931],
            'SUGIHWARAS' => [-7.251, 111.984],
            'KEDUNGADEM' => [-7.258, 112.052],
            'KEPOH BARU' => [-7.212, 112.082],
            'BAURENO' => [-7.151, 112.115],
            'KANOR' => [-7.118, 112.022],
            'SUMBEREJO' => [-7.171, 112.021],
            'BALEN' => [-7.185, 111.967],
            'SUKOSEWU' => [-7.215, 111.954],
            'KAPAS' => [-7.172, 111.912],
            'BOJONEGORO' => [-7.150, 111.880],
            'TRUCUK' => [-7.135, 111.875],
            'DANDER' => [-7.221, 111.874],
            'NGASEM' => [-7.241, 111.774],
            'GAYAM' => [-7.195, 111.712],
            'KALITIDU' => [-7.141, 111.784],
            'MALO' => [-7.114, 111.745],
            'PURWOSARI' => [-7.214, 111.664],
            'PADANGAN' => [-7.162, 111.621],
            'KASIMAN' => [-7.112, 111.674],
            'KEDEWAN' => [-7.098, 111.662],
        ];

        // Fetch 400 real villages in Bojonegoro from melonku database
        $villages = DB::table('melonku.villages')
            ->join('melonku.kecamatan', 'melonku.villages.districts_id', '=', 'melonku.kecamatan.id')
            ->where('melonku.kecamatan.kabupaten_id', 3522)
            ->select('melonku.villages.nama as desa_name', 'melonku.kecamatan.nama as kec_name')
            ->get()
            ->toArray();

        if (empty($villages)) {
            throw new \Exception("Kecamatan/Desa data from melonku database is empty!");
        }

        // --- 1. Loop to generate 774 SD schools ---
        $targetSD = 774;
        $npsnSDStart = 20500001;

        for ($i = 0; $i < $targetSD; $i++) {
            $vIndex = $i % count($villages);
            $village = $villages[$vIndex];
            
            $desaClean = ucwords(strtolower(trim($village->desa_name)));
            $kecClean = ucwords(strtolower(trim($village->kec_name)));
            $isSecond = ($i >= count($villages));
            
            // Randomly select SD type (85% Negeri, 15% Swasta)
            $isSwasta = (mt_rand(1, 100) > 85);
            if ($isSwasta) {
                $swastaTypes = ['SD Islam', 'SD Muhammadiyah', 'SD Kristen', 'SDS'];
                $typePrefix = $swastaTypes[array_rand($swastaTypes)];
                $name = $typePrefix . ' ' . $desaClean;
            } else {
                $suffix = $isSecond ? ' II' : ' I';
                $name = 'SD Negeri ' . $desaClean . $suffix;
            }
            
            $npsn = (string)($npsnSDStart + $i);
            
            // Coordinates offset around district center
            $kecUpper = strtoupper(trim($village->kec_name));
            $baseCoords = $kecCoords[$kecUpper] ?? [-7.150, 111.880];
            $lat = $baseCoords[0] + (mt_rand(-2000, 2000) / 100000.0);
            $lng = $baseCoords[1] + (mt_rand(-2000, 2000) / 100000.0);
            
            $isPriority = (mt_rand(1, 100) <= 5); // 5% schools priority
            $notes = $isPriority ? 'Sekolah prioritas pendampingan berkas akreditasi.' : null;
            
            $school = School::create([
                'npsn' => $npsn,
                'name' => $name,
                'type' => 'SD',
                'district' => $kecClean,
                'address' => 'Jl. Raya ' . $desaClean . ', Kec. ' . $kecClean . ', Kab. Bojonegoro',
                'latitude' => $lat,
                'longitude' => $lng,
                'is_priority' => $isPriority,
                'notes' => $notes
            ]);
            
            // Create accreditation (100% accredited: A=60%, B=30%, C=10%)
            $randGrade = mt_rand(1, 100);
            $grade = ($randGrade <= 60) ? 'A' : (($randGrade <= 90) ? 'B' : 'C');
            
            // Random expiry date distribution (2024 to 2029)
            $randYear = mt_rand(1, 100);
            if ($randYear <= 15) {
                $expiryYear = 2024;
            } elseif ($randYear <= 30) {
                $expiryYear = 2025;
            } elseif ($randYear <= 55) {
                $expiryYear = 2026;
            } elseif ($randYear <= 70) {
                $expiryYear = 2027;
            } elseif ($randYear <= 85) {
                $expiryYear = 2028;
            } else {
                $expiryYear = 2029;
            }
            
            $expiryMonth = mt_rand(1, 12);
            $expiryDay = mt_rand(1, 28);
            $expiryDate = sprintf('%04d-%02d-%02d', $expiryYear, $expiryMonth, $expiryDay);
            
            $issueYear = $expiryYear - 5;
            $issueDate = sprintf('%04d-%02d-%02d', $issueYear, $expiryMonth, $expiryDay);
            
            $skNumber = sprintf('%d/BAN-SM/SK/%d', mt_rand(100, 2000), $issueYear);
            
            Accreditation::create([
                'school_id' => $school->id,
                'certificate_number' => $skNumber,
                'grade' => $grade,
                'issue_date' => $issueDate,
                'expiry_date' => $expiryDate,
            ]);
        }

        // --- 2. Loop to generate 108 SMP schools ---
        $targetSMP = 108;
        $npsnSMPStart = 20580001;

        for ($i = 0; $i < $targetSMP; $i++) {
            $vIndex = ($i * 3) % count($villages);
            $village = $villages[$vIndex];
            
            $desaClean = ucwords(strtolower(trim($village->desa_name)));
            $kecClean = ucwords(strtolower(trim($village->kec_name)));
            
            // Randomly select SMP type (50% Negeri, 50% Swasta)
            $isSwasta = (mt_rand(1, 100) > 50);
            if ($isSwasta) {
                $swastaTypes = ['SMP Islam', 'SMP Muhammadiyah', 'SMP PGRI', 'SMPS'];
                $typePrefix = $swastaTypes[array_rand($swastaTypes)];
                $name = $typePrefix . ' ' . $kecClean . ' ' . (int)(($i / 28) + 1);
            } else {
                $existingNegeriCount = School::where('type', 'SMP')
                    ->where('district', $kecClean)
                    ->where('name', 'LIKE', 'SMP Negeri%')
                    ->count();
                $name = 'SMP Negeri ' . ($existingNegeriCount + 1) . ' ' . $kecClean;
            }
            
            $npsn = (string)($npsnSMPStart + $i);
            
            // Coordinates offset around district center
            $kecUpper = strtoupper(trim($village->kec_name));
            $baseCoords = $kecCoords[$kecUpper] ?? [-7.150, 111.880];
            $lat = $baseCoords[0] + (mt_rand(-2000, 2000) / 100000.0);
            $lng = $baseCoords[1] + (mt_rand(-2000, 2000) / 100000.0);
            
            $isPriority = (mt_rand(1, 100) <= 5);
            $notes = $isPriority ? 'Sekolah prioritas pendampingan berkas akreditasi.' : null;
            
            $school = School::create([
                'npsn' => $npsn,
                'name' => $name,
                'type' => 'SMP',
                'district' => $kecClean,
                'address' => 'Jl. Raya ' . $desaClean . ', Kec. ' . $kecClean . ', Kab. Bojonegoro',
                'latitude' => $lat,
                'longitude' => $lng,
                'is_priority' => $isPriority,
                'notes' => $notes
            ]);
            
            // Create accreditation (100% accredited: A=65%, B=27%, C=8%)
            $randGrade = mt_rand(1, 100);
            $grade = ($randGrade <= 65) ? 'A' : (($randGrade <= 92) ? 'B' : 'C');
            
            // Random expiry date distribution (2024 to 2029)
            $randYear = mt_rand(1, 100);
            if ($randYear <= 15) {
                $expiryYear = 2024;
            } elseif ($randYear <= 30) {
                $expiryYear = 2025;
            } elseif ($randYear <= 55) {
                $expiryYear = 2026;
            } elseif ($randYear <= 70) {
                $expiryYear = 2027;
            } elseif ($randYear <= 85) {
                $expiryYear = 2028;
            } else {
                $expiryYear = 2029;
            }
            
            $expiryMonth = mt_rand(1, 12);
            $expiryDay = mt_rand(1, 28);
            $expiryDate = sprintf('%04d-%02d-%02d', $expiryYear, $expiryMonth, $expiryDay);
            
            $issueYear = $expiryYear - 5;
            $issueDate = sprintf('%04d-%02d-%02d', $issueYear, $expiryMonth, $expiryDay);
            
            $skNumber = sprintf('%d/BAN-SM/SK/%d', mt_rand(100, 2000), $issueYear);
            
            Accreditation::create([
                'school_id' => $school->id,
                'certificate_number' => $skNumber,
                'grade' => $grade,
                'issue_date' => $issueDate,
                'expiry_date' => $expiryDate,
            ]);
        }

        // --- 3. Create Users ---
        // Admin Dinas
        User::create([
            'name' => 'Admin Dinas Pendidikan',
            'email' => 'admin@bojonegoro.go.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'school_id' => null,
        ]);

        // Kepala Dinas (Pimpinan)
        User::create([
            'name' => 'Kepala Dinas Pendidikan',
            'email' => 'kaddin@bojonegoro.go.id',
            'password' => Hash::make('password'),
            'role' => 'pimpinan',
            'school_id' => null,
        ]);

        // Find schools for operator accounts
        $sdSchool = School::where('type', 'SD')->first();
        $smpSchool = School::where('type', 'SMP')->first();

        // Operator 1 (SD School)
        if ($sdSchool) {
            User::create([
                'name' => 'Operator ' . $sdSchool->name,
                'email' => 'operator_sd1@bojonegoro.go.id',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'school_id' => $sdSchool->id,
            ]);
        }

        // Operator 2 (SMP School)
        if ($smpSchool) {
            User::create([
                'name' => 'Operator ' . $smpSchool->name,
                'email' => 'operator_smp1@bojonegoro.go.id',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'school_id' => $smpSchool->id,
            ]);
        }
    }
}
