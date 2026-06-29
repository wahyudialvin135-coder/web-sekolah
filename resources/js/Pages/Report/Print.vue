<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    schools: Array,
    printedAt: String,
});

onMounted(() => {
    // Wait a brief moment to ensure all tables render, then open print dialog
    setTimeout(() => {
        window.print();
    }, 1000);
});
</script>

<template>
    <Head title="Cetak Laporan - SiMONA Bojonegoro" />

    <div class="p-8 max-w-6xl mx-auto bg-white font-sans text-slate-800 text-xs leading-relaxed">
        <!-- 1. Government Kop Surat (Letterhead) -->
        <div class="flex items-center gap-6 border-b-4 border-double border-slate-900 pb-4 mb-6">
            <!-- Representing Dinas Logo -->
            <div class="w-16 h-20 flex-shrink-0 flex items-center justify-center border border-slate-350 p-1">
                <!-- Simple SVG representation of Bojonegoro / East Java badge -->
                <svg viewBox="0 0 100 120" class="w-full h-full text-slate-800 fill-current" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="50,5 95,30 95,90 50,115 5,90 5,30" stroke="currentColor" stroke-width="5" fill="none"/>
                    <circle cx="50" cy="55" r="25" stroke="currentColor" stroke-width="3" fill="none"/>
                    <path d="M50,10 L50,110 M10,60 L90,60" stroke="currentColor" stroke-width="1.5"/>
                    <text x="50" y="58" font-size="10" font-weight="bold" text-anchor="middle">PEMKAB</text>
                </svg>
            </div>
            
            <div class="flex-1 text-center font-serif">
                <h2 class="text-lg font-bold tracking-wide uppercase">Pemerintah Kabupaten Bojonegoro</h2>
                <h1 class="text-xl font-extrabold tracking-wider uppercase text-slate-950">Dinas Pendidikan</h1>
                <p class="text-[10px] text-slate-500 italic mt-0.5">
                    Jl. Patimura No. 26, Bojonegoro, Jawa Timur 62115
                </p>
                <p class="text-[9px] text-slate-500 mt-0.5">
                    Telepon: (0353) 881512 | Email: disdik@bojonegoro.go.id | Website: disdik.bojonegoro.go.id
                </p>
            </div>
        </div>

        <!-- 2. Document Title -->
        <div class="text-center mb-6">
            <h3 class="text-sm font-bold uppercase tracking-wider underline">Laporan Rekapitulasi Monitoring Akreditasi Sekolah</h3>
            <p class="text-[10px] text-slate-500 mt-1">Dicetak pada: {{ printedAt }} | Oleh: {{ $page.props.auth.user.name }}</p>
        </div>

        <!-- 3. Report Info -->
        <div class="mb-4 bg-slate-50 p-3 rounded border border-slate-200 grid grid-cols-2 gap-4">
            <div>
                <span class="text-[10px] font-bold text-slate-400 block uppercase">Wilayah Monitoring</span>
                <span class="font-bold text-slate-800 text-xs">Kabupaten Bojonegoro (Seluruh Kecamatan)</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 block uppercase">Jumlah Record Laporan</span>
                <span class="font-bold text-slate-800 text-xs">{{ schools.length }} Sekolah Terpilih</span>
            </div>
        </div>

        <!-- 4. Main Print Table -->
        <table class="w-full text-left border-collapse border border-slate-900 mb-8">
            <thead>
                <tr class="bg-slate-100 text-slate-900 font-bold uppercase border-b border-slate-900 text-[10px]">
                    <th class="py-2.5 px-3 border border-slate-900 w-8 text-center">No</th>
                    <th class="py-2.5 px-3 border border-slate-900 w-20">NPSN</th>
                    <th class="py-2.5 px-3 border border-slate-900">Nama Sekolah</th>
                    <th class="py-2.5 px-3 border border-slate-900 w-12 text-center">Tipe</th>
                    <th class="py-2.5 px-3 border border-slate-900 w-28">Kecamatan</th>
                    <th class="py-2.5 px-3 border border-slate-900 w-16 text-center">Predikat</th>
                    <th class="py-2.5 px-3 border border-slate-900 w-28">Nomor SK</th>
                    <th class="py-2.5 px-3 border border-slate-900 w-24">Tgl Habis SK</th>
                    <th class="py-2.5 px-3 border border-slate-900 w-28 text-center">Status</th>
                    <th class="py-2.5 px-3 border border-slate-900 w-12 text-center">Prio</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-400 text-[10px]">
                <tr v-if="schools.length === 0">
                    <td colspan="10" class="py-6 text-center italic text-slate-500 border border-slate-900">
                        Tidak ada data sekolah terlampir untuk kriteria cetak laporan ini.
                    </td>
                </tr>
                <tr v-else v-for="(school, idx) in schools" :key="school.id" class="page-break">
                    <td class="py-2 px-3 border border-slate-900 text-center font-mono">{{ idx + 1 }}</td>
                    <td class="py-2 px-3 border border-slate-900 font-mono font-semibold">{{ school.npsn }}</td>
                    <td class="py-2 px-3 border border-slate-900 font-bold">{{ school.name }}</td>
                    <td class="py-2 px-3 border border-slate-900 text-center font-bold">{{ school.type }}</td>
                    <td class="py-2 px-3 border border-slate-900">{{ school.district }}</td>
                    <td class="py-2 px-3 border border-slate-900 text-center font-bold">
                        {{ school.latest_accreditation ? school.latest_accreditation.grade : 'TT' }}
                    </td>
                    <td class="py-2 px-3 border border-slate-900 font-mono text-[9px]">
                        {{ school.latest_accreditation ? school.latest_accreditation.certificate_number : '-' }}
                    </td>
                    <td class="py-2 px-3 border border-slate-900 font-mono text-[9px] text-center">
                        {{ school.latest_accreditation ? school.latest_accreditation.expiry_date.split('T')[0] : '-' }}
                    </td>
                    <td class="py-2 px-3 border border-slate-900 text-center font-bold uppercase text-[9px]">
                        {{ school.monitoring_status }}
                    </td>
                    <td class="py-2 px-3 border border-slate-900 text-center">
                        {{ school.is_priority ? 'Ya' : 'Tidak' }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- 5. Signature Section (Tanda Tangan) -->
        <div class="mt-12 flex justify-end">
            <div class="text-center font-serif w-72">
                <p class="mb-1 text-[10px]">Bojonegoro, {{ printedAt.split(' ')[0] }} {{ printedAt.split(' ')[1] }} {{ printedAt.split(' ')[2] }}</p>
                <p class="font-bold text-[10px]">Mengetahui,</p>
                <p class="font-bold text-[10px] uppercase">Kepala Dinas Pendidikan</p>
                <p class="font-bold text-[10px] uppercase mb-16">Kabupaten Bojonegoro</p>
                
                <p class="font-extrabold text-[11px] underline">Drs. H. M. NUR SUHUD, M.Pd.</p>
                <p class="text-[9px] text-slate-500 font-sans mt-0.5">Pembina Utama Muda</p>
                <p class="text-[9px] text-slate-500 font-sans font-mono">NIP. 19681120 199303 1 005</p>
            </div>
        </div>
    </div>
</template>

<style>
/* CSS styles optimized for printing */
@media print {
    body {
        background-color: white !important;
        color: black !important;
        font-size: 9pt !important;
    }
    .page-break {
        page-break-inside: avoid;
    }
}
@page {
    size: A4 landscape;
    margin: 1.5cm;
}
</style>
