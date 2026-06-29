<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Profil Pengguna</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola data informasi akun dan kata sandi login Anda.</p>
        </div>

        <div class="space-y-6">
            <!-- Account Info Card -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-bold text-2xl">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">{{ $page.props.auth.user.name }}</h2>
                        <p class="text-slate-500 text-xs mt-0.5">{{ $page.props.auth.user.email }}</p>
                        
                        <div class="flex items-center gap-2 mt-2">
                            <span :class="[
                                $page.props.auth.user.role === 'admin' ? 'bg-red-100 text-red-800 border-red-200' :
                                $page.props.auth.user.role === 'pimpinan' ? 'bg-purple-100 text-purple-800 border-purple-200' :
                                'bg-green-100 text-green-800 border-green-200',
                                'text-[10px] px-2 py-0.5 rounded font-bold uppercase border tracking-wider'
                            ]">
                                {{ $page.props.auth.user.role === 'admin' ? 'Admin Dinas' : $page.props.auth.user.role === 'pimpinan' ? 'Kepala Dinas / Pimpinan' : 'Operator Sekolah' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-100 text-xs md:max-w-sm flex-1 md:flex-none" v-if="$page.props.auth.user.is_operator && $page.props.auth.user.school_id">
                    <span class="text-[9px] font-bold text-slate-400 block uppercase tracking-wider">Sekolah Terkait</span>
                    <span class="font-bold text-slate-800 mt-1 block">Operator di: {{ $page.props.auth.user.school_id ? 'Sekolah Terkoneksi' : '-' }}</span>
                    <span class="text-slate-500 text-[10px] mt-0.5 block">Akun Anda dikaitkan khusus untuk melihat status monitoring sekolah Anda sendiri.</span>
                </div>
            </div>

            <!-- Profile Info forms -->
            <div class="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div v-if="$page.props.auth.user.is_admin" class="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
