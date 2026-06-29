<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    schools: Array,
    districts: Array,
    filters: Object,
});

const type = ref(props.filters.type || '');
const district = ref(props.filters.district || '');
const grade = ref(props.filters.grade || '');
const status = ref(props.filters.status || '');
const priority = ref(props.filters.priority || '');

const applyFilters = () => {
    router.get(
        route('reports.index'),
        {
            type: type.value,
            district: district.value,
            grade: grade.value,
            status: status.value,
            priority: priority.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

watch([type, district, grade, status, priority], () => {
    applyFilters();
});

const resetFilters = () => {
    type.value = '';
    district.value = '';
    grade.value = '';
    status.value = '';
    priority.value = '';
};

// Export to Excel (CSV) with current filters
const triggerExport = () => {
    const params = new URLSearchParams({
        type: type.value,
        district: district.value,
        grade: grade.value,
        status: status.value,
        priority: priority.value,
    });
    window.location.href = route('reports.export') + '?' + params.toString();
};

// Open print friendly page in new tab
const triggerPrint = () => {
    const params = new URLSearchParams({
        type: type.value,
        district: district.value,
        grade: grade.value,
        status: status.value,
        priority: priority.value,
    });
    const printUrl = route('reports.print') + '?' + params.toString();
    window.open(printUrl, '_blank');
};

const getStatusColorName = (status) => {
    const colors = {
        'Aktif': 'text-green-600',
        'Habis dalam 12 Bulan': 'text-yellow-600 font-bold',
        'Habis dalam 6 Bulan': 'text-red-600 font-bold',
        'Kadaluarsa': 'text-slate-500 font-bold',
        'Belum Terakreditasi': 'text-slate-400 font-bold',
    };
    return colors[status] || 'text-slate-700';
};
</script>

<template>
    <Head title="Cetak Laporan Monitoring" />

    <AuthenticatedLayout>
        <!-- Header Banner -->
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Cetak & Ekspor Laporan</h1>
            <p class="text-slate-500 text-sm mt-1">Ekspor rekapitulasi data monitoring akreditasi sekolah Kabupaten Bojonegoro ke Excel atau PDF.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left 1 Column: Filter Panel & Actions -->
            <div class="space-y-6">
                <!-- Filter Card -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
                    <h3 class="font-bold text-slate-800 text-sm mb-4 border-b border-slate-100 pb-2">Filter Data Laporan</h3>
                    
                    <div class="space-y-4">
                        <!-- Type -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jenjang</label>
                            <select v-model="type" class="w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-lg text-xs py-2 px-3 transition-all">
                                <option value="">Semua Jenjang</option>
                                <option value="SD">SD</option>
                                <option value="SMP">SMP</option>
                            </select>
                        </div>

                        <!-- District -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kecamatan</label>
                            <select v-model="district" class="w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-lg text-xs py-2 px-3 transition-all">
                                <option value="">Semua Kecamatan</option>
                                <option v-for="d in districts" :key="d" :value="d">{{ d }}</option>
                            </select>
                        </div>

                        <!-- Grade -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Predikat Akreditasi</label>
                            <select v-model="grade" class="w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-lg text-xs py-2 px-3 transition-all">
                                <option value="">Semua Predikat</option>
                                <option value="A">A / Unggul</option>
                                <option value="B">B / Baik Sekali</option>
                                <option value="C">C / Baik</option>
                                <option value="TT">Belum Terakreditasi</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status Monitoring</label>
                            <select v-model="status" class="w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-lg text-xs py-2 px-3 transition-all">
                                <option value="">Semua Status</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Habis dalam 12 Bulan">Habis dalam 12 Bulan</option>
                                <option value="Habis dalam 6 Bulan">Habis dalam 6 Bulan</option>
                                <option value="Kadaluarsa">Kadaluarsa</option>
                            </select>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Prioritas Pembinaan</label>
                            <select v-model="priority" class="w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-lg text-xs py-2 px-3 transition-all">
                                <option value="">Semua Pembinaan</option>
                                <option value="yes">Hanya Prioritas Dinas</option>
                            </select>
                        </div>
                    </div>

                    <button @click="resetFilters" class="text-xs text-red-600 hover:text-red-800 font-bold block mt-4 transition-all">
                        Reset Filter
                    </button>
                </div>

                <!-- Export Actions -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-4">
                    <h3 class="font-bold text-slate-800 text-sm mb-2">Tindakan Ekspor Laporan</h3>
                    
                    <button @click="triggerExport" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-3 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Unduh Rekap Excel (CSV)
                    </button>

                    <button @click="triggerPrint" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-3 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-3a2 2 0 00-2-2H9a2 2 0 00-2 2v3a2 2 0 002 2zm5-12V7a3 3 0 116 0v4" />
                        </svg>
                        Cetak Laporan PDF (Browser)
                    </button>

                    <div class="text-[10px] text-slate-400 bg-slate-50 p-3 rounded-lg border border-slate-100 italic leading-relaxed">
                        * Catatan: Laporan PDF memicu print layout browser resmi. Gunakan menu "Save to PDF" pada pilihan printer browser Anda untuk mengunduh laporan PDF berkualitas tinggi.
                    </div>
                </div>
            </div>

            <!-- Right 2 Columns: Data Preview -->
            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-5 bg-slate-900 text-white border-b border-slate-800 flex justify-between items-center">
                        <h3 class="font-bold text-sm">Pratinjau Data Laporan</h3>
                        <span class="text-xs bg-slate-800 text-slate-300 px-3 py-1 rounded border border-slate-700 font-mono">
                            {{ schools.length }} Sekolah Terpilih
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-[10px] text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                                    <th class="py-3 px-6">NPSN</th>
                                    <th class="py-3 px-6">Nama Sekolah</th>
                                    <th class="py-3 px-6">Kecamatan</th>
                                    <th class="py-3 px-6 text-center">Predikat</th>
                                    <th class="py-3 px-6 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs text-slate-700 divide-y divide-slate-100">
                                <tr v-if="schools.length === 0">
                                    <td colspan="5" class="py-12 text-center text-slate-400 font-semibold italic bg-slate-50/50">
                                        Tidak ada data monitoring sekolah yang sesuai untuk laporan.
                                    </td>
                                </tr>
                                <tr v-else v-for="school in schools" :key="school.id" class="hover:bg-slate-50/50">
                                    <td class="py-3 px-6 font-mono font-semibold text-slate-500">{{ school.npsn }}</td>
                                    <td class="py-3 px-6">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-800">{{ school.name }}</span>
                                            <span class="text-[10px] text-slate-400 mt-0.5 truncate max-w-[200px]">{{ school.address }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-6 font-medium">{{ school.district }}</td>
                                    <td class="py-3 px-6 text-center font-bold">
                                        {{ school.latest_accreditation ? school.latest_accreditation.grade : 'TT' }}
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <span :class="getStatusColorName(school.monitoring_status)" class="font-semibold">
                                            {{ school.monitoring_status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-100 bg-slate-50 text-right text-xs text-slate-400 italic">
                    Menampilkan {{ schools.length }} dari {{ schools.length }} data hasil monitoring.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
