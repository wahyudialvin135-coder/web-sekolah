<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    school: Object,
});

const form = useForm({
    notes: props.school.notes || '',
});

const submitNotes = () => {
    form.post(route('monitoring.notes', props.school.id), {
        preserveScroll: true,
        onSuccess: () => {
            alert('Catatan pembinaan berhasil diperbarui!');
        }
    });
};

const togglePriority = () => {
    router.post(route('monitoring.priority', props.school.id), {}, {
        preserveScroll: true,
    });
};

onMounted(() => {
    // Initialize Leaflet Map for school coordinate
    if (props.school.latitude && props.school.longitude) {
        const map = L.map('school-detail-map', {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([props.school.latitude, props.school.longitude], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let fillColor = '#22c55e'; // Green
        if (props.school.monitoring_status === 'Habis dalam 6 Bulan') fillColor = '#ef4444'; // Red
        else if (props.school.monitoring_status === 'Habis dalam 12 Bulan') fillColor = '#eab308'; // Yellow
        else if (props.school.monitoring_status === 'Kadaluarsa') fillColor = '#374151'; // Gray/Black

        const marker = L.circleMarker([props.school.latitude, props.school.longitude], {
            radius: 10,
            fillColor: fillColor,
            color: '#ffffff',
            weight: 2.5,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map);

        marker.bindPopup(`
            <div class="font-sans p-0.5 text-center">
                <span class="font-bold text-xs text-slate-800">${props.school.name}</span>
            </div>
        `).openPopup();
    }
});

const getStatusBadgeClass = (status) => {
    const classes = {
        'Aktif': 'bg-green-100 text-green-800 border-green-200 font-bold',
        'Habis dalam 12 Bulan': 'bg-yellow-100 text-yellow-800 border-yellow-200 font-bold',
        'Habis dalam 6 Bulan': 'bg-red-100 text-red-800 border-red-200 font-bold',
        'Kadaluarsa': 'bg-slate-200 text-slate-800 border-slate-300 font-bold',
        'Belum Terakreditasi': 'bg-slate-100 text-slate-500 border-slate-200 font-bold',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 border-gray-200';
};
</script>

<template>
    <Head :title="'Detail - ' + school.name" />

    <AuthenticatedLayout>
        <!-- Back Link & Title Banner -->
        <div class="mb-8">
            <Link :href="route('monitoring.index')" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1 mb-3 transition-all w-max">
                &larr; Kembali ke Tabel Monitoring
            </Link>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight truncate max-w-xl">{{ school.name }}</h1>
                        <span :class="school.type === 'SD' ? 'bg-blue-50 text-blue-700 border-blue-150' : 'bg-emerald-50 text-emerald-700 border-emerald-150'" class="px-2 py-0.5 rounded text-[10px] font-bold border block mt-0.5">
                            {{ school.type }}
                        </span>
                        <span v-if="school.is_priority" class="bg-amber-100 text-amber-800 border border-amber-200 px-2 py-0.5 rounded text-[10px] font-bold flex items-center gap-0.5 shadow-sm">
                            &#9733; Prioritas Pembinaan
                        </span>
                    </div>
                    <p class="text-slate-500 text-sm mt-1">Profil Lengkap, Riwayat Akreditasi, Log Aktivitas, dan Catatan Dinas Pendidikan.</p>
                </div>

                <!-- Admin Action Priority Toggle -->
                <div v-if="$page.props.auth.user.is_admin" class="flex-shrink-0">
                    <button @click="togglePriority" :class="school.is_priority ? 'bg-amber-550 hover:bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50 text-slate-700'" class="text-xs font-bold px-4 py-2.5 rounded-lg shadow-sm transition-all flex items-center gap-2">
                        <span class="text-base">&#9733;</span>
                        {{ school.is_priority ? 'Hapus dari Prioritas' : 'Tandai Prioritas Pembinaan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Details Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- 1. School Information (Left 2 Columns) -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Profile details -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 bg-slate-900 text-white flex justify-between items-center border-b border-slate-800">
                        <h3 class="font-bold text-sm">Informasi Profil Sekolah</h3>
                        <span class="font-mono text-xs font-bold text-slate-400">NPSN: {{ school.npsn }}</span>
                    </div>
                    <div class="p-6 divide-y divide-slate-100">
                        <div class="py-3.5 grid grid-cols-3 gap-4">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Sekolah</span>
                            <span class="text-xs font-bold text-slate-800 col-span-2">{{ school.name }}</span>
                        </div>
                        <div class="py-3.5 grid grid-cols-3 gap-4">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">NPSN</span>
                            <span class="text-xs font-bold text-slate-800 font-mono col-span-2">{{ school.npsn }}</span>
                        </div>
                        <div class="py-3.5 grid grid-cols-3 gap-4">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jenjang Pendidikan</span>
                            <span class="text-xs font-bold text-slate-800 col-span-2">{{ school.type === 'SD' ? 'Sekolah Dasar (SD)' : 'Sekolah Menengah Pertama (SMP)' }}</span>
                        </div>
                        <div class="py-3.5 grid grid-cols-3 gap-4">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kecamatan</span>
                            <span class="text-xs font-bold text-slate-800 col-span-2">{{ school.district }}</span>
                        </div>
                        <div class="py-3.5 grid grid-cols-3 gap-4">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Alamat Lengkap</span>
                            <span class="text-xs font-medium text-slate-700 col-span-2 leading-relaxed">{{ school.address }}</span>
                        </div>
                    </div>
                </div>

                <!-- Accreditation History -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 bg-slate-900 text-white border-b border-slate-800">
                        <h3 class="font-bold text-sm">Riwayat Sertifikat Akreditasi</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-[10px] text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                                    <th class="py-3 px-6">Predikat</th>
                                    <th class="py-3 px-6">Nomor SK Sertifikat</th>
                                    <th class="py-3 px-6">Tanggal Terbit</th>
                                    <th class="py-3 px-6">Tanggal Kadaluarsa</th>
                                    <th class="py-3 px-6">Status Log</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs text-slate-700 divide-y divide-slate-100">
                                <tr v-if="school.accreditations.length === 0">
                                    <td colspan="5" class="py-8 text-center text-slate-400 bg-slate-50/20 italic">
                                        Sekolah ini belum memiliki riwayat akreditasi resmi (Belum Terakreditasi).
                                    </td>
                                </tr>
                                <tr v-else v-for="acc in school.accreditations" :key="acc.id" class="hover:bg-slate-50/50">
                                    <td class="py-3.5 px-6 font-black text-blue-600 text-sm">{{ acc.grade }}</td>
                                    <td class="py-3.5 px-6 font-semibold">{{ acc.certificate_number }}</td>
                                    <td class="py-3.5 px-6 font-medium text-slate-500">{{ acc.issue_date.split('T')[0] }}</td>
                                    <td class="py-3.5 px-6 font-semibold text-slate-700">{{ acc.expiry_date.split('T')[0] }}</td>
                                    <td class="py-3.5 px-6">
                                        <span v-if="new Date(acc.expiry_date) > new Date()" class="text-[10px] bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded font-bold uppercase">
                                            Aktif / Berlaku
                                        </span>
                                        <span v-else class="text-[10px] bg-slate-100 text-slate-500 border border-slate-200 px-2 py-0.5 rounded font-semibold uppercase">
                                            Kadaluarsa
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Interaction Logs -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 bg-slate-900 text-white border-b border-slate-800">
                        <h3 class="font-bold text-sm">Log Aktivitas Monitoring Sekolah</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-[10px] text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                                    <th class="py-3 px-6">Tanggal & Waktu</th>
                                    <th class="py-3 px-6 w-36">Pengguna</th>
                                    <th class="py-3 px-6 w-44">Tindakan</th>
                                    <th class="py-3 px-6">Detail / Catatan Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs text-slate-700 divide-y divide-slate-100">
                                <tr v-if="school.logs.length === 0">
                                    <td colspan="4" class="py-8 text-center text-slate-400 bg-slate-50/20 italic">
                                        Belum ada aktivitas log monitoring tercatat untuk sekolah ini.
                                    </td>
                                </tr>
                                <tr v-else v-for="log in school.logs" :key="log.id" class="hover:bg-slate-50/50">
                                    <td class="py-3 px-6 font-mono text-slate-500 text-[11px]">{{ new Date(log.created_at).toLocaleString('id-ID') }}</td>
                                    <td class="py-3 px-6 font-bold text-slate-800">{{ log.user.name }}</td>
                                    <td class="py-3 px-6">
                                        <span class="bg-slate-100 text-slate-700 font-semibold px-2 py-0.5 rounded text-[10px] uppercase border border-slate-200">
                                            {{ log.action }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-6 font-medium text-slate-600 leading-relaxed">{{ log.notes }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. School Position Map & Dinas Notes Form (Right Column) -->
            <div class="space-y-8">
                <!-- Status & Expiry countdown card -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 flex flex-col items-center text-center">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Status Akreditasi</span>
                    <span :class="getStatusBadgeClass(school.monitoring_status)" class="px-4 py-1.5 text-xs rounded-full uppercase tracking-wider border block w-full mb-6">
                        {{ school.monitoring_status }}
                    </span>

                    <div class="w-20 h-20 rounded-full bg-blue-50 border border-blue-150 flex items-center justify-center mb-4">
                        <span class="text-4xl font-black text-blue-600">
                            {{ school.accreditations.length > 0 ? school.accreditations[0].grade : 'TT' }}
                        </span>
                    </div>

                    <div class="text-xs font-semibold text-slate-500" v-if="school.accreditations.length > 0">
                        Masa Berlaku SK Sampai:
                        <span class="text-slate-800 font-bold block text-sm mt-0.5">
                            {{ new Date(school.accreditations[0].expiry_date).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </span>
                    </div>
                    <div class="text-xs font-semibold text-slate-500" v-else>
                        Sekolah belum terakreditasi resmi.
                    </div>
                </div>

                <!-- Leaflet Location Map -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 flex flex-col h-72">
                    <h3 class="font-bold text-slate-800 text-xs mb-3 uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Lokasi Sekolah (Peta)
                    </h3>
                    <div class="relative flex-1 min-h-0">
                        <div id="school-detail-map" class="absolute inset-0 rounded-xl shadow-inner bg-slate-100 z-0"></div>
                    </div>
                </div>

                <!-- Admin Notes update form -->
                <div v-if="$page.props.auth.user.is_admin" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
                    <h3 class="font-bold text-slate-800 text-sm mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Catatan Pembinaan Dinas
                    </h3>
                    
                    <form @submit.prevent="submitNotes" class="space-y-4">
                        <div>
                            <textarea v-model="form.notes" rows="4" placeholder="Tulis catatan, evaluasi sarpras, kendala, atau arahan pembinaan di sini..." class="w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-lg text-xs py-2 px-3 transition-all placeholder:text-slate-400"></textarea>
                        </div>
                        <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-1">
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan Catatan Dinas</span>
                        </button>
                    </form>
                </div>
                <!-- Readonly Notes view for Operator & Pimpinan -->
                <div v-else class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
                    <h3 class="font-bold text-slate-800 text-sm mb-3 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Catatan Pembinaan Dinas
                    </h3>
                    <div class="bg-slate-50 border-l-4 border-blue-500 rounded-r-lg p-4">
                        <p class="text-xs text-slate-600 leading-relaxed italic">
                            "{{ school.notes || 'Belum ada catatan pembinaan khusus tercatat untuk sekolah ini.' }}"
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
#school-detail-map {
    height: 100%;
}
.leaflet-popup-content-wrapper {
    border-radius: 8px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
</style>
