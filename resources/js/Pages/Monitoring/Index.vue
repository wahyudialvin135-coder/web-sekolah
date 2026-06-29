<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    schools: Array,
    districts: Array,
    filters: Object,
});

const page = usePage();
const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');
const district = ref(props.filters.district || '');
const grade = ref(props.filters.grade || '');
const status = ref(props.filters.status || '');
const priority = ref(props.filters.priority || '');

// Debounce filtering
let filterTimeout;
const applyFilters = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get(
            route('monitoring.index'),
            {
                search: search.value,
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
    }, 300);
};

watch([search, type, district, grade, status, priority], () => {
    applyFilters();
});

const resetFilters = () => {
    search.value = '';
    type.value = '';
    district.value = '';
    grade.value = '';
    status.value = '';
    priority.value = '';
};

const togglePriority = (schoolId) => {
    router.post(route('monitoring.priority', schoolId), {}, {
        preserveScroll: true,
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'Aktif': 'bg-green-100 text-green-800 font-bold',
        'Habis dalam 12 Bulan': 'bg-yellow-100 text-yellow-800 font-bold',
        'Habis dalam 6 Bulan': 'bg-red-100 text-red-800 font-bold',
        'Kadaluarsa': 'bg-slate-200 text-slate-800 font-bold',
        'Belum Terakreditasi': 'bg-slate-100 text-slate-500 font-bold',
    };
    return classes[status] || 'bg-gray-150 text-gray-800';
};
</script>

<template>
    <Head title="Monitoring Akreditasi" />

    <AuthenticatedLayout>
        <!-- Header Banner -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Tabel Monitoring Akreditasi</h1>
                <p class="text-slate-500 text-sm mt-1">Daftar status akreditasi dan masa berlaku sekolah se-Kabupaten Bojonegoro.</p>
            </div>
            <div v-if="!$page.props.auth.user.is_operator" class="flex gap-2">
                <Link :href="route('reports.index')" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Menu Laporan
                </Link>
                <a :href="route('reports.export')" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-md transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Ekspor Excel
                </a>
            </div>
        </div>

        <!-- Filters Box (Hidden for Operator school-specific views) -->
        <div v-if="!$page.props.auth.user.is_operator" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 mb-8">
            <div class="flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z" />
                </svg>
                <h3 class="font-bold text-slate-800 text-sm">Filter & Pencarian Sekolah</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Search Input -->
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cari NPSN / Nama</label>
                    <input v-model="search" type="text" placeholder="Masukkan kata kunci..." class="w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-lg text-xs py-2 px-3 transition-all" />
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jenjang</label>
                    <select v-model="type" class="w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-lg text-xs py-2 px-3 transition-all">
                        <option value="">Semua Jenjang</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                    </select>
                </div>

                <!-- District -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kecamatan</label>
                    <select v-model="district" class="w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-lg text-xs py-2 px-3 transition-all">
                        <option value="">Semua Kecamatan</option>
                        <option v-for="d in districts" :key="d" :value="d">{{ d }}</option>
                    </select>
                </div>

                <!-- Grade -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Akreditasi</label>
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
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Masa Berlaku</label>
                    <select v-model="status" class="w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-lg text-xs py-2 px-3 transition-all">
                        <option value="">Semua Status</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Habis dalam 12 Bulan">Habis dalam 12 Bulan</option>
                        <option value="Habis dalam 6 Bulan">Habis dalam 6 Bulan</option>
                        <option value="Kadaluarsa">Kadaluarsa</option>
                    </select>
                </div>
            </div>

            <!-- Priority & Reset button -->
            <div class="flex justify-between items-center mt-5 pt-4 border-t border-slate-100">
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-600">
                        <input type="radio" v-model="priority" value="" class="text-blue-600 focus:ring-blue-500" />
                        Semua Pembinaan
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-600">
                        <input type="radio" v-model="priority" value="yes" class="text-blue-600 focus:ring-blue-500" />
                        <span class="text-amber-500 font-bold">&#9733;</span> Prioritas Dinas
                    </label>
                </div>
                <button @click="resetFilters" class="text-xs text-red-600 hover:text-red-800 font-bold flex items-center gap-1.5 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Reset Filter
                </button>
            </div>
        </div>

        <!-- Monitoring Data Table -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900 text-white text-[11px] uppercase tracking-wider font-bold">
                            <th class="py-4 px-6 w-12 text-center" v-if="!$page.props.auth.user.is_operator">Prio</th>
                            <th class="py-4 px-6 w-28">NPSN</th>
                            <th class="py-4 px-6">Nama Sekolah</th>
                            <th class="py-4 px-6 w-24">Jenjang</th>
                            <th class="py-4 px-6 w-36">Kecamatan</th>
                            <th class="py-4 px-6 w-24 text-center">Predikat</th>
                            <th class="py-4 px-6 w-36">Tanggal Habis SK</th>
                            <th class="py-4 px-6 w-44 text-center">Status</th>
                            <th class="py-4 px-6 w-28 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 text-xs">
                        <tr v-if="schools.length === 0">
                            <td colspan="9" class="py-12 text-center text-slate-400 font-medium bg-slate-50/50">
                                Tidak ada data monitoring sekolah yang sesuai dengan filter.
                            </td>
                        </tr>
                        <tr v-else v-for="school in schools" :key="school.id" class="hover:bg-slate-50/70 transition-all">
                            <!-- Priority Star Toggle -->
                            <td class="py-3 px-6 text-center" v-if="!$page.props.auth.user.is_operator">
                                <button v-if="$page.props.auth.user.is_admin" @click="togglePriority(school.id)" class="text-lg transition-all focus:outline-none" :title="school.is_priority ? 'Hapus dari prioritas' : 'Tandai sebagai prioritas'">
                                    <span v-if="school.is_priority" class="text-amber-500 hover:text-amber-600">&#9733;</span>
                                    <span v-else class="text-slate-300 hover:text-slate-400">&#9734;</span>
                                </button>
                                <span v-else class="text-lg">
                                    <span v-if="school.is_priority" class="text-amber-500">&#9733;</span>
                                    <span v-else class="text-slate-200">&#9734;</span>
                                </span>
                            </td>

                            <!-- NPSN -->
                            <td class="py-3 px-6 font-mono font-semibold">{{ school.npsn }}</td>

                            <!-- Nama Sekolah -->
                            <td class="py-3 px-6">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800">{{ school.name }}</span>
                                    <span class="text-[10px] text-slate-400 truncate max-w-[200px] mt-0.5" :title="school.address">
                                        {{ school.address }}
                                    </span>
                                </div>
                            </td>

                            <!-- Jenjang -->
                            <td class="py-3 px-6">
                                <span :class="school.type === 'SD' ? 'bg-blue-50 text-blue-700 border-blue-150' : 'bg-emerald-50 text-emerald-700 border-emerald-150'" class="px-2 py-0.5 rounded text-[10px] font-bold border">
                                    {{ school.type }}
                                </span>
                            </td>

                            <!-- Kecamatan -->
                            <td class="py-3 px-6 font-medium text-slate-600">{{ school.district }}</td>

                            <!-- Predikat -->
                            <td class="py-3 px-6 text-center">
                                <span class="text-sm font-black text-slate-800">
                                    {{ school.latest_accreditation ? school.latest_accreditation.grade : 'TT' }}
                                </span>
                            </td>

                            <!-- Tanggal Habis SK -->
                            <td class="py-3 px-6 font-medium text-slate-600">
                                {{ school.latest_accreditation ? school.latest_accreditation.expiry_date.split('T')[0] : '-' }}
                            </td>

                            <!-- Status Badge -->
                            <td class="py-3 px-6 text-center">
                                <span :class="getStatusBadgeClass(school.monitoring_status)" class="px-2.5 py-1 rounded-full text-[10px] uppercase tracking-wider block text-center w-full max-w-[150px] mx-auto">
                                    {{ school.monitoring_status }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-6 text-center">
                                <Link :href="route('monitoring.show', school.id)" class="inline-block bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 hover:border-blue-600 transition-all shadow-sm">
                                    Detail
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
