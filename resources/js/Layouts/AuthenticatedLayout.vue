<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const showingMobileMenu = ref(false);
const showingProfileDropdown = ref(false);
const showingNotificationDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};

const getRoleLabel = (role) => {
    const labels = {
        'admin': 'Admin Dinas',
        'pimpinan': 'Kepala Dinas / Pimpinan',
        'operator': 'Operator Sekolah',
    };
    return labels[role] || 'User';
};

const getRoleBadgeClass = (role) => {
    const classes = {
        'admin': 'bg-red-500 text-white text-xs px-2 py-0.5 rounded font-semibold',
        'pimpinan': 'bg-purple-600 text-white text-xs px-2 py-0.5 rounded font-semibold',
        'operator': 'bg-green-600 text-white text-xs px-2 py-0.5 rounded font-semibold',
    };
    return classes[role] || 'bg-gray-500 text-white text-xs px-2 py-0.5 rounded font-semibold';
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">
        <!-- Sidebar for Desktop -->
        <aside class="hidden md:flex flex-col w-64 bg-slate-900 text-slate-100 flex-shrink-0 border-r border-slate-800 shadow-xl">
            <!-- Sidebar Header / Logo -->
            <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800 gap-3">
                <!-- Dinas Logo representation in CSS/SVG -->
                <div class="p-2 bg-blue-600 rounded-lg text-white shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-xs uppercase tracking-wider text-blue-400">Dinas Pendidikan</span>
                    <span class="text-xs font-semibold text-slate-300">Kab. Bojonegoro</span>
                </div>
            </div>

            <!-- Sidebar Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                <Link :href="route('dashboard')"
                    :class="[
                        route().current('dashboard') 
                            ? 'bg-blue-600 text-white font-medium shadow-md shadow-blue-900/20' 
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white transition-all',
                        'flex items-center gap-3 px-4 py-3 rounded-lg text-sm'
                    ]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                    </svg>
                    Dashboard
                </Link>

                <Link :href="route('monitoring.index')"
                    :class="[
                        route().current('monitoring.*') 
                            ? 'bg-blue-600 text-white font-medium shadow-md shadow-blue-900/20' 
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white transition-all',
                        'flex items-center gap-3 px-4 py-3 rounded-lg text-sm'
                    ]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 00-2 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Monitoring Akreditasi
                </Link>

                <Link :href="route('map.index')"
                    :class="[
                        route().current('map.*') 
                            ? 'bg-blue-600 text-white font-medium shadow-md shadow-blue-900/20' 
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white transition-all',
                        'flex items-center gap-3 px-4 py-3 rounded-lg text-sm'
                    ]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    Peta Sekolah
                </Link>

                <Link v-if="!$page.props.auth.user.is_operator" :href="route('reports.index')"
                    :class="[
                        route().current('reports.*') 
                            ? 'bg-blue-600 text-white font-medium shadow-md shadow-blue-900/20' 
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white transition-all',
                        'flex items-center gap-3 px-4 py-3 rounded-lg text-sm'
                    ]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Cetak Laporan
                </Link>

                <Link :href="route('profile.edit')"
                    :class="[
                        route().current('profile.*') 
                            ? 'bg-blue-600 text-white font-medium shadow-md shadow-blue-900/20' 
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white transition-all',
                        'flex items-center gap-3 px-4 py-3 rounded-lg text-sm'
                    ]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profil Saya
                </Link>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-slate-800 bg-slate-950 flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-sm font-semibold text-blue-400 border border-slate-700">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-medium truncate text-slate-200">{{ $page.props.auth.user.name }}</span>
                        <span :class="getRoleBadgeClass($page.props.auth.user.role)" class="w-max text-[10px] mt-0.5">
                            {{ getRoleLabel($page.props.auth.user.role) }}
                        </span>
                    </div>
                </div>
                <button @click="logout" class="mt-2 w-full text-left text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-850 px-3 py-2 rounded-md transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Log Out
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header Top Bar -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shadow-sm z-10">
                <!-- Mobile Navigation Toggle -->
                <div class="flex items-center gap-4">
                    <button @click="showingMobileMenu = !showingMobileMenu" class="md:hidden p-2 text-slate-500 hover:bg-slate-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <!-- Header Title -->
                    <div class="hidden md:flex flex-col">
                        <span class="text-lg font-bold text-slate-800">Sistem Monitoring Akreditasi Sekolah (SiMONA)</span>
                        <span class="text-xs text-slate-500 font-medium">Kabupaten Bojonegoro</span>
                    </div>
                    <div class="md:hidden flex flex-col">
                        <span class="text-sm font-bold text-slate-800">SiMONA Bojonegoro</span>
                    </div>
                </div>

                <!-- Top bar User Menu & Notifications -->
                <div class="flex items-center gap-4">
                    <!-- Notifications Dropdown -->
                    <div class="relative">
                        <button @click="showingNotificationDropdown = !showingNotificationDropdown" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg relative transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span v-if="$page.props.globalNotificationCount > 0" class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-[10px] font-bold border-2 border-white">
                                {{ $page.props.globalNotificationCount }}
                            </span>
                        </button>

                        <!-- Notification Dropdown Menu -->
                        <div v-if="showingNotificationDropdown" @click.outside="showingNotificationDropdown = false" class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-50">
                            <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                                <span class="font-bold text-sm text-slate-800">Notifikasi Akreditasi</span>
                                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-semibold">
                                    {{ $page.props.globalNotificationCount }} Penting
                                </span>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <div v-if="$page.props.globalNotifications.length === 0" class="px-4 py-6 text-center text-xs text-slate-400">
                                    Tidak ada sekolah dengan status kritis atau habis masa akreditasi.
                                </div>
                                <Link v-else v-for="notif in $page.props.globalNotifications" :key="notif.id" :href="route('monitoring.show', notif.id)" @click="showingNotificationDropdown = false" class="block px-4 py-3 hover:bg-slate-50 transition-all border-b border-slate-50 last:border-0">
                                    <div class="flex items-start gap-2.5">
                                        <span :class="[
                                            notif.color === 'red' ? 'bg-red-500' : notif.color === 'yellow' ? 'bg-yellow-500' : 'bg-slate-500',
                                            'w-2 h-2 rounded-full mt-1.5 flex-shrink-0'
                                        ]"></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-slate-800 truncate">{{ notif.name }}</p>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Status: <span class="font-semibold text-slate-700">{{ notif.status }}</span></p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">Habis SK: {{ notif.expiry_date }}</p>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                            <div class="px-4 py-2 border-t border-slate-100 text-center">
                                <Link :href="route('monitoring.index', { status: 'Kadaluarsa' })" @click="showingNotificationDropdown = false" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">
                                    Lihat Semua Data Sekolah
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Dropdown (Top right) -->
                    <div class="relative">
                        <button @click="showingProfileDropdown = !showingProfileDropdown" class="flex items-center gap-2 hover:bg-slate-50 p-2 rounded-lg transition-all">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <span class="hidden md:inline text-sm font-semibold text-slate-700">{{ $page.props.auth.user.name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div v-if="showingProfileDropdown" @click.outside="showingProfileDropdown = false" class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-50">
                            <div class="px-4 py-2 border-b border-slate-100 md:hidden">
                                <p class="text-sm font-bold text-slate-800">{{ $page.props.auth.user.name }}</p>
                                <span :class="getRoleBadgeClass($page.props.auth.user.role)" class="text-[10px] inline-block mt-1">
                                    {{ getRoleLabel($page.props.auth.user.role) }}
                                </span>
                            </div>
                            <Link :href="route('profile.edit')" @click="showingProfileDropdown = false" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                Profil Saya
                            </Link>
                            <button @click="logout" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700">
                                Log Out
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Mobile Sidebar Menu Overlay -->
            <div v-if="showingMobileMenu" class="md:hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40" @click="showingMobileMenu = false"></div>
            <aside v-if="showingMobileMenu" class="md:hidden fixed inset-y-0 left-0 w-64 bg-slate-900 text-slate-100 flex flex-col z-50 shadow-2xl">
                <div class="h-16 flex items-center justify-between px-6 bg-slate-950 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-600 rounded-lg text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-xs uppercase tracking-wider text-blue-400">Dinas Pendidikan</span>
                            <span class="text-xs font-semibold text-slate-300">Kab. Bojonegoro</span>
                        </div>
                    </div>
                    <button @click="showingMobileMenu = false" class="p-1 hover:bg-slate-800 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400 hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                    <Link :href="route('dashboard')" @click="showingMobileMenu = false"
                        :class="[
                            route().current('dashboard') ? 'bg-blue-600 text-white font-medium' : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                            'flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition-all'
                        ]">
                        Dashboard
                    </Link>
                    <Link :href="route('monitoring.index')" @click="showingMobileMenu = false"
                        :class="[
                            route().current('monitoring.*') ? 'bg-blue-600 text-white font-medium' : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                            'flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition-all'
                        ]">
                        Monitoring Akreditasi
                    </Link>
                    <Link :href="route('map.index')" @click="showingMobileMenu = false"
                        :class="[
                            route().current('map.*') ? 'bg-blue-600 text-white font-medium' : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                            'flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition-all'
                        ]">
                        Peta Sekolah
                    </Link>
                    <Link v-if="!$page.props.auth.user.is_operator" :href="route('reports.index')" @click="showingMobileMenu = false"
                        :class="[
                            route().current('reports.*') ? 'bg-blue-600 text-white font-medium' : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                            'flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition-all'
                        ]">
                        Cetak Laporan
                    </Link>
                    <Link :href="route('profile.edit')" @click="showingMobileMenu = false"
                        :class="[
                            route().current('profile.*') ? 'bg-blue-600 text-white font-medium' : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                            'flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition-all'
                        ]">
                        Profil Saya
                    </Link>
                </nav>

                <div class="p-4 border-t border-slate-800 bg-slate-950">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-sm font-bold text-blue-400">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-sm font-medium truncate text-slate-200">{{ $page.props.auth.user.name }}</span>
                            <span :class="getRoleBadgeClass($page.props.auth.user.role)" class="w-max text-[10px] mt-0.5">
                                {{ getRoleLabel($page.props.auth.user.role) }}
                            </span>
                        </div>
                    </div>
                    <button @click="logout" class="mt-4 w-full text-left text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-850 px-3 py-2 rounded-md transition-all flex items-center gap-2">
                        Log Out
                    </button>
                </div>
            </aside>

            <!-- Page Heading (Optional) -->
            <div class="bg-white border-b border-slate-200 px-6 py-4 md:hidden flex flex-col gap-1 shadow-sm">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">SiMONA Dinas Pendidikan</span>
                <span class="text-md font-bold text-slate-800">Kabupaten Bojonegoro</span>
            </div>

            <!-- Page Content Slot -->
            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
