<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { Chart } from 'chart.js/auto';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    isOperator: Boolean,
    hasSchool: Boolean,
    school: Object, // Filled if Operator
    stats: Object,  // Filled if Admin/Pimpinan
    notifications: Array, // Filled if Admin/Pimpinan
    mapSchools: Array,
    chartKecamatan: Array,
    chartJenjang: Object,
    chartStatus: Object,
    chartTrend: Object,
});

// Canvas references for Chart.js
const chartJenjangCanvas = ref(null);
const chartStatusCanvas = ref(null);
const chartTrendCanvas = ref(null);
const chartKecamatanCanvas = ref(null);

onMounted(() => {
    // 1. Initialize Map if Admin/Pimpinan and there is data
    if (!props.isOperator && props.stats && props.mapSchools) {
        initMapWithSchools(props.mapSchools);
    }

    // 2. Initialize Chart.js
    if (!props.isOperator && props.stats) {
        // Chart Status Akreditasi (Doughnut)
        new Chart(chartStatusCanvas.value, {
            type: 'doughnut',
            data: {
                labels: ['Aktif', 'Habis 12 Bln', 'Habis 6 Bln', 'Kadaluarsa'],
                datasets: [{
                    data: [
                        props.chartStatus['Aktif'],
                        props.chartStatus['Habis 12 Bulan'],
                        props.chartStatus['Habis 6 Bulan'],
                        props.chartStatus['Kadaluarsa']
                    ],
                    backgroundColor: ['#22c55e', '#eab308', '#ef4444', '#64748b'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Chart Akreditasi per Jenjang (Bar)
        new Chart(chartJenjangCanvas.value, {
            type: 'bar',
            data: {
                labels: ['A (Unggul)', 'B (Baik Sekali)', 'C (Baik)', 'Belum/TT'],
                datasets: [
                    {
                        label: 'SD',
                        data: [
                            props.chartJenjang['SD']['A'],
                            props.chartJenjang['SD']['B'],
                            props.chartJenjang['SD']['C'],
                            props.chartJenjang['SD']['TT']
                        ],
                        backgroundColor: '#3b82f6'
                    },
                    {
                        label: 'SMP',
                        data: [
                            props.chartJenjang['SMP']['A'],
                            props.chartJenjang['SMP']['B'],
                            props.chartJenjang['SMP']['C'],
                            props.chartJenjang['SMP']['TT']
                        ],
                        backgroundColor: '#10b981'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });

        // Chart Tren Habis Akreditasi (Line)
        new Chart(chartTrendCanvas.value, {
            type: 'line',
            data: {
                labels: props.chartTrend.labels,
                datasets: [{
                    label: 'Jumlah Sekolah Habis SK',
                    data: props.chartTrend.data,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });

        // Chart Akreditasi per Kecamatan (Stacked Bar)
        const kecamatenLabels = props.chartKecamatan.map(k => k.kecamatan);
        const dataA = props.chartKecamatan.map(k => k.A);
        const dataB = props.chartKecamatan.map(k => k.B);
        const dataC = props.chartKecamatan.map(k => k.C);
        const dataTT = props.chartKecamatan.map(k => k.TT);

        new Chart(chartKecamatanCanvas.value, {
            type: 'bar',
            data: {
                labels: kecamatenLabels,
                datasets: [
                    { label: 'A', data: dataA, backgroundColor: '#22c55e' },
                    { label: 'B', data: dataB, backgroundColor: '#eab308' },
                    { label: 'C', data: dataC, backgroundColor: '#3b82f6' },
                    { label: 'TT', data: dataTT, backgroundColor: '#64748b' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }
});

// Render map markers using dynamic property
const initMapWithSchools = (mapSchools) => {
    const map = L.map('map-dashboard', {
        zoomControl: true,
        scrollWheelZoom: false
    }).setView([-7.190, 111.900], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    mapSchools.forEach(school => {
        if (school.latitude && school.longitude) {
            let fillColor = '#22c55e'; // Green
            if (school.monitoring_status === 'Habis dalam 6 Bulan') fillColor = '#ef4444'; // Red
            else if (school.monitoring_status === 'Habis dalam 12 Bulan') fillColor = '#eab308'; // Yellow
            else if (school.monitoring_status === 'Kadaluarsa') fillColor = '#374151'; // Gray/Black

            const marker = L.circleMarker([school.latitude, school.longitude], {
                radius: 8,
                fillColor: fillColor,
                color: '#ffffff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.85
            }).addTo(map);

            marker.bindPopup(`
                <div class="p-1 font-sans">
                    <p class="font-bold text-xs text-slate-800">${school.name}</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">NPSN: ${school.npsn} | ${school.type}</p>
                    <p class="text-[10px] text-slate-500">Predikat: <strong>${school.grade}</strong></p>
                    <p class="text-[10px] text-slate-500">Status: <strong style="color: ${fillColor}">${school.monitoring_status}</strong></p>
                    <a href="/monitoring/${school.id}" class="text-[10px] font-bold text-blue-600 hover:underline block mt-1">Detail Selengkapnya &rarr;</a>
                </div>
            `);
        }
    });
};


const getStatusBadgeClass = (status) => {
    return {
        'Aktif': 'bg-green-100 text-green-800 font-semibold',
        'Habis dalam 12 Bulan': 'bg-yellow-100 text-yellow-800 font-semibold',
        'Habis dalam 6 Bulan': 'bg-red-100 text-red-800 font-semibold',
        'Kadaluarsa': 'bg-slate-100 text-slate-800 font-semibold',
        'Belum Terakreditasi': 'bg-slate-100 text-slate-500 font-semibold',
    }[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Dashboard Monitoring" />

    <AuthenticatedLayout>
        <!-- Title Banner -->
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">
                Selamat Datang, {{ $page.props.auth.user.name }}
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Sistem Informasi Monitoring Akreditasi SD dan SMP Dinas Pendidikan Kabupaten Bojonegoro.
            </p>
        </div>

        <!-- -------------------- OPERATOR VIEW -------------------- -->
        <div v-if="isOperator">
            <!-- If operator doesn't have school assigned -->
            <div v-if="!hasSchool" class="bg-white border border-slate-200 shadow-md rounded-2xl p-8 text-center max-w-lg mx-auto mt-12">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Akun Belum Terhubung</h3>
                <p class="text-slate-500 text-sm mt-2">
                    Akun operator Anda belum terhubung dengan sekolah manapun. Harap hubungi Admin Dinas Pendidikan untuk mengaitkan akun Anda dengan data sekolah.
                </p>
            </div>

            <!-- Operator school details -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- School details card -->
                <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md transition-all overflow-hidden">
                    <div class="p-6 bg-slate-900 text-white flex justify-between items-start">
                        <div>
                            <span class="text-xs uppercase bg-blue-600 px-2 py-0.5 rounded font-bold tracking-wider">
                                Profil Sekolah Saya
                            </span>
                            <h2 class="text-xl font-bold mt-2">{{ school.name }}</h2>
                            <p class="text-slate-300 text-xs mt-0.5">NPSN: {{ school.npsn }} | Jenjang: {{ school.type }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-400 block">Kecamatan</span>
                            <span class="font-bold text-sm text-blue-400">{{ school.district }}</span>
                        </div>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Alamat Sekolah</span>
                                <p class="text-slate-700 text-sm font-medium mt-1 leading-relaxed">{{ school.address }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Status Akreditasi</span>
                                <span :class="getStatusBadgeClass(school.monitoring_status)" class="inline-block px-3 py-1 text-xs rounded-full mt-1.5 uppercase tracking-wide">
                                    {{ school.monitoring_status }}
                                </span>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-6 grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50/50 p-4 rounded-xl">
                            <div>
                                <span class="text-[10px] font-semibold text-slate-400 block uppercase">Predikat</span>
                                <span class="text-2xl font-black text-blue-600 mt-1 block">
                                    {{ school.latest_accreditation ? school.latest_accreditation.grade : 'TT' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-slate-400 block uppercase">No. SK</span>
                                <span class="text-xs font-bold text-slate-700 mt-1.5 block truncate">
                                    {{ school.latest_accreditation ? school.latest_accreditation.certificate_number : '-' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-slate-400 block uppercase">Tgl Terbit SK</span>
                                <span class="text-xs font-bold text-slate-700 mt-1.5 block">
                                    {{ school.latest_accreditation ? school.latest_accreditation.issue_date.split('T')[0] : '-' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-slate-400 block uppercase">Tgl Habis SK</span>
                                <span class="text-xs font-bold text-slate-700 mt-1.5 block">
                                    {{ school.latest_accreditation ? school.latest_accreditation.expiry_date.split('T')[0] : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dinas notes / info card -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md transition-all p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-blue-600 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <h3 class="font-bold text-slate-800">Catatan Pembinaan Dinas</h3>
                        </div>
                        <div class="bg-slate-50 border-l-4 border-blue-500 rounded-r-lg p-4">
                            <p class="text-xs text-slate-600 leading-relaxed italic">
                                "{{ school.notes || 'Belum ada catatan pembinaan khusus untuk sekolah Anda.' }}"
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-6 mt-6 flex flex-col gap-2">
                        <Link :href="route('monitoring.show', school.id)" class="w-full bg-blue-600 text-white hover:bg-blue-700 text-center font-semibold text-xs py-2.5 rounded-lg shadow transition-all block">
                            Lihat Detail Riwayat & Peta
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- -------------------- ADMIN / PIMPINAN VIEW -------------------- -->
        <div v-else>
            <!-- 1. Statistics Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
                <!-- SD -->
                <div class="bg-white p-5 border border-slate-200 rounded-2xl shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                    <div class="min-w-0">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Sekolah SD</span>
                        <span class="text-2xl md:text-3xl font-extrabold text-slate-800 mt-1 block">{{ stats.totalSD }}</span>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>

                <!-- SMP -->
                <div class="bg-white p-5 border border-slate-200 rounded-2xl shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                    <div class="min-w-0">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Sekolah SMP</span>
                        <span class="text-2xl md:text-3xl font-extrabold text-slate-800 mt-1 block">{{ stats.totalSMP }}</span>
                    </div>
                    <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>

                <!-- Habis dalam 12 Bulan -->
                <div class="bg-white p-5 border border-slate-200 rounded-2xl shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                    <div class="min-w-0">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Hampir Habis (12 Bln)</span>
                        <span class="text-2xl md:text-3xl font-extrabold text-amber-600 mt-1 block">
                            {{ stats.expiring12m + stats.expiring6m }}
                        </span>
                    </div>
                    <div class="p-3 bg-amber-50 rounded-xl text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>

                <!-- Kadaluarsa -->
                <div class="bg-white p-5 border border-slate-200 rounded-2xl shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                    <div class="min-w-0">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Status Kadaluarsa</span>
                        <span class="text-2xl md:text-3xl font-extrabold text-red-600 mt-1 block">{{ stats.expired }}</span>
                    </div>
                    <div class="p-3 bg-red-50 rounded-xl text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Grade Distributions Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- A -->
                <div class="bg-slate-900 p-4 rounded-xl text-white shadow-sm hover:shadow transition-all">
                    <span class="text-[10px] text-blue-400 font-bold uppercase tracking-wider">Akreditasi A / Unggul</span>
                    <p class="text-2xl font-black mt-0.5">{{ stats.countA }} <span class="text-xs font-normal text-slate-400">Sekolah</span></p>
                </div>
                <!-- B -->
                <div class="bg-slate-900 p-4 rounded-xl text-white shadow-sm hover:shadow transition-all">
                    <span class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider">Akreditasi B / Baik Sekali</span>
                    <p class="text-2xl font-black mt-0.5">{{ stats.countB }} <span class="text-xs font-normal text-slate-400">Sekolah</span></p>
                </div>
                <!-- C -->
                <div class="bg-slate-900 p-4 rounded-xl text-white shadow-sm hover:shadow transition-all">
                    <span class="text-[10px] text-amber-400 font-bold uppercase tracking-wider">Akreditasi C / Baik</span>
                    <p class="text-2xl font-black mt-0.5">{{ stats.countC }} <span class="text-xs font-normal text-slate-400">Sekolah</span></p>
                </div>
                <!-- TT -->
                <div class="bg-slate-900 p-4 rounded-xl text-white shadow-sm hover:shadow transition-all">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Belum Terakreditasi</span>
                    <p class="text-2xl font-black mt-0.5">{{ stats.countTT }} <span class="text-xs font-normal text-slate-400">Sekolah</span></p>
                </div>
            </div>

            <!-- 2. Mid Section: Map & Notifications Box -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Leaflet Map -->
                <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm p-6 flex flex-col">
                    <h3 class="font-bold text-slate-800 text-md mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Peta Sebaran Sekolah Akreditasi Bojonegoro
                    </h3>
                    <div class="relative flex-1 min-h-[300px]">
                        <div id="map-dashboard" class="absolute inset-0 rounded-xl shadow-inner bg-slate-100 z-0"></div>
                    </div>
                </div>

                <!-- Notifications Box -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 flex flex-col justify-between max-h-[390px]">
                    <div>
                        <h3 class="font-bold text-slate-800 text-md mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Daftar Sekolah Kritis
                        </h3>
                        <div class="space-y-3 overflow-y-auto max-h-[240px] pr-2">
                            <div v-if="notifications.length === 0" class="text-center text-xs text-slate-400 py-12">
                                Tidak ada data sekolah yang membutuhkan perhatian mendesak saat ini.
                            </div>
                            <Link v-else v-for="notif in notifications" :key="notif.id" :href="route('monitoring.show', notif.id)" class="block p-3 rounded-xl border border-slate-150 hover:bg-slate-50 transition-all">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-bold text-xs text-slate-800 truncate max-w-[150px]">{{ notif.name }}</h4>
                                    <span :class="[
                                        notif.color === 'red' ? 'bg-red-100 text-red-800' : notif.color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 'bg-slate-100 text-slate-800',
                                        'text-[9px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wide'
                                    ]">{{ notif.status }}</span>
                                </div>
                                <div class="flex justify-between items-center text-[10px] text-slate-500 mt-2">
                                    <span>NPSN: {{ notif.npsn }}</span>
                                    <span>Habis SK: <strong class="text-slate-700">{{ notif.expiry_date }}</strong></span>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-4 text-center mt-3">
                        <Link :href="route('monitoring.index')" class="text-xs text-blue-600 hover:text-blue-800 font-bold">
                            Lihat Semua Sekolah Monitoring &rarr;
                        </Link>
                    </div>
                </div>
            </div>

            <!-- 3. Visual Charts Section (Grid) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Status Akreditasi Doughnut -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 flex flex-col h-80">
                    <h4 class="font-bold text-slate-800 text-sm mb-4">Status Masa Berlaku Akreditasi</h4>
                    <div class="flex-1 relative min-h-0">
                        <canvas ref="chartStatusCanvas"></canvas>
                    </div>
                </div>

                <!-- Akreditasi per Jenjang Bar -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 flex flex-col h-80">
                    <h4 class="font-bold text-slate-800 text-sm mb-4">Perbandingan Predikat Akreditasi SD vs SMP</h4>
                    <div class="flex-1 relative min-h-0">
                        <canvas ref="chartJenjangCanvas"></canvas>
                    </div>
                </div>

                <!-- Tren Expiring Line Chart -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 flex flex-col h-80">
                    <h4 class="font-bold text-slate-800 text-sm mb-4">Tren Rencana Masa Habis Akreditasi (12 Bulan Kedepan)</h4>
                    <div class="flex-1 relative min-h-0">
                        <canvas ref="chartTrendCanvas"></canvas>
                    </div>
                </div>

                <!-- Stacked Kecamatan Bar Chart -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 flex flex-col h-80">
                    <h4 class="font-bold text-slate-800 text-sm mb-4">Distribusi Predikat Akreditasi per Kecamatan</h4>
                    <div class="flex-1 relative min-h-0">
                        <canvas ref="chartKecamatanCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Ensure leaflet controls and popups are rendered above background, and align font styling */
.leaflet-popup-content-wrapper {
    border-radius: 12px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
.leaflet-container {
    font-family: inherit;
}
</style>
