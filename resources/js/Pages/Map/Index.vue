<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    schools: Array,
    districts: Array,
    filters: Object,
});

const type = ref(props.filters.type || '');
const district = ref(props.filters.district || '');
const grade = ref(props.filters.grade || '');
const status = ref(props.filters.status || '');

let mapInstance = null;
const markerGroup = ref(null);

const applyFilters = () => {
    router.get(
        route('map.index'),
        {
            type: type.value,
            district: district.value,
            grade: grade.value,
            status: status.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onSuccess: () => {
                updateMapMarkers();
            }
        }
    );
};

watch([type, district, grade, status], () => {
    applyFilters();
});

const resetFilters = () => {
    type.value = '';
    district.value = '';
    grade.value = '';
    status.value = '';
};

const initMap = () => {
    mapInstance = L.map('map-full', {
        zoomControl: true,
        scrollWheelZoom: true
    }).setView([-7.190, 111.900], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapInstance);

    markerGroup.value = L.layerGroup().addTo(mapInstance);
    updateMapMarkers();
};

const updateMapMarkers = () => {
    if (!mapInstance || !markerGroup.value) return;

    // Clear old markers
    markerGroup.value.clearLayers();

    // Plot new markers
    props.schools.forEach(school => {
        if (school.latitude && school.longitude) {
            let fillColor = '#22c55e'; // Green
            if (school.monitoring_status === 'Habis dalam 6 Bulan') fillColor = '#ef4444'; // Red
            else if (school.monitoring_status === 'Habis dalam 12 Bulan') fillColor = '#eab308'; // Yellow
            else if (school.monitoring_status === 'Kadaluarsa') fillColor = '#374151'; // Gray/Black

            const marker = L.circleMarker([school.latitude, school.longitude], {
                radius: 9,
                fillColor: fillColor,
                color: '#ffffff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.85
            });

            marker.bindPopup(`
                <div class="p-1 font-sans">
                    <p class="font-bold text-xs text-slate-800">${school.name}</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">NPSN: ${school.npsn} | ${school.type}</p>
                    <p class="text-[10px] text-slate-500 font-semibold">Predikat: ${school.latest_accreditation ? school.latest_accreditation.grade : 'TT'}</p>
                    <p class="text-[10px] text-slate-500">Kecamatan: <span class="font-bold text-slate-700">${school.district}</span></p>
                    <p class="text-[10px] text-slate-500">Status: <strong style="color: ${fillColor}">${school.monitoring_status}</strong></p>
                    <a href="/monitoring/${school.id}" class="text-[10px] font-bold text-blue-600 hover:underline block mt-2 text-center py-1 bg-slate-100 rounded border border-slate-200">Detail &rarr;</a>
                </div>
            `);

            markerGroup.value.addLayer(marker);
        }
    });

    // Auto fit map bounds to markers if schools exist
    if (props.schools.length > 0) {
        const bounds = [];
        props.schools.forEach(s => {
            if (s.latitude && s.longitude) bounds.push([s.latitude, s.longitude]);
        });
        if (bounds.length > 0) {
            mapInstance.fitBounds(bounds, { padding: [30, 30] });
        }
    }
};

onMounted(() => {
    initMap();
});

const focusSchoolOnMap = (school) => {
    if (mapInstance && school.latitude && school.longitude) {
        mapInstance.setView([school.latitude, school.longitude], 15);
        // Find and open popup for this coordinate
        markerGroup.value.eachLayer(layer => {
            const latlng = layer.getLatLng();
            if (latlng.lat === school.latitude && latlng.lng === school.longitude) {
                layer.openPopup();
            }
        });
    }
};
</script>

<template>
    <Head title="Peta Persebaran Sekolah" />

    <AuthenticatedLayout>
        <!-- Header Banner -->
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Peta Persebaran Sekolah</h1>
            <p class="text-slate-500 text-sm mt-1">Peta interaktif persebaran sekolah dan status monitoring akreditasi SD/SMP Bojonegoro.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-[calc(100vh-210px)] min-h-[500px]">
            <!-- Left Sidebar Filter & Schools list (1 Column) -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 flex flex-col justify-between h-full overflow-hidden">
                <div class="flex-1 flex flex-col min-h-0">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-4 border-b border-slate-150 pb-2 flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z" />
                        </svg>
                        Filter Peta
                    </h3>

                    <!-- Filter selects -->
                    <div class="space-y-4 mb-6">
                        <!-- Jenjang -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jenjang</label>
                            <select v-model="type" class="w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-lg text-xs py-2 px-3 transition-all">
                                <option value="">Semua Jenjang</option>
                                <option value="SD">SD</option>
                                <option value="SMP">SMP</option>
                            </select>
                        </div>
                        <!-- Kecamatan -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kecamatan</label>
                            <select v-model="district" class="w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-lg text-xs py-2 px-3 transition-all">
                                <option value="">Semua Kecamatan</option>
                                <option v-for="d in districts" :key="d" :value="d">{{ d }}</option>
                            </select>
                        </div>
                        <!-- Predikat -->
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
                    </div>

                    <!-- Schools sidebar list -->
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2 border-b border-slate-150 pb-2">
                        Daftar Hasil ({{ schools.length }} Sekolah)
                    </h3>
                    <div class="flex-1 overflow-y-auto pr-1 space-y-1.5 min-h-0">
                        <div v-if="schools.length === 0" class="text-center text-xs text-slate-400 py-12">
                            Sekolah tidak ditemukan.
                        </div>
                        <button v-else v-for="school in schools" :key="school.id" @click="focusSchoolOnMap(school)" class="w-full text-left p-2.5 rounded-lg border border-slate-150 hover:bg-slate-50 hover:border-slate-300 transition-all flex justify-between items-center gap-2">
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 truncate block">{{ school.name }}</span>
                                <span class="text-[10px] text-slate-500 font-mono block mt-0.5">NPSN: {{ school.npsn }} | Kec. {{ school.district }}</span>
                            </div>
                            <span :class="[
                                school.monitoring_status_color === 'green' ? 'bg-green-500' :
                                school.monitoring_status_color === 'yellow' ? 'bg-yellow-500' :
                                school.monitoring_status_color === 'red' ? 'bg-red-500' : 'bg-slate-500',
                                'w-2.5 h-2.5 rounded-full flex-shrink-0'
                            ]"></span>
                        </button>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3 mt-3 flex justify-between items-center">
                    <button @click="resetFilters" class="text-xs text-red-600 hover:text-red-800 font-bold flex items-center gap-1.5 transition-all">
                        Reset Filter
                    </button>
                    <div class="flex gap-1.5">
                        <div class="w-2 h-2 rounded-full bg-green-500" title="Aktif"></div>
                        <div class="w-2 h-2 rounded-full bg-yellow-500" title="Habis 12 Bln"></div>
                        <div class="w-2 h-2 rounded-full bg-red-500" title="Habis 6 Bln"></div>
                        <div class="w-2 h-2 rounded-full bg-slate-500" title="Kadaluarsa"></div>
                    </div>
                </div>
            </div>

            <!-- Map Container (3 Columns) -->
            <div class="lg:col-span-3 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden p-2 flex flex-col h-full">
                <div class="relative flex-1 min-h-[400px]">
                    <div id="map-full" class="absolute inset-0 rounded-xl shadow-inner bg-slate-100 z-0"></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
#map-full {
    height: 100%;
}
.leaflet-popup-content-wrapper {
    border-radius: 12px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
</style>
