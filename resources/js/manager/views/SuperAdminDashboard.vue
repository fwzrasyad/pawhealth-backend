<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../composables/useApi.js';
import { auth } from '../firebase.js';
import { signOut } from 'firebase/auth';

const router = useRouter();
const api    = useApi();

const clinics   = ref([]);
const loading   = ref(true);
const approving = ref(null);   // clinic_id being approved
const rejecting = ref(null);   // clinic_id being rejected

// Toast
const toast = ref({ show: false, message: '', type: 'success' });
let toastTimer = null;

function showToast(message, type = 'success') {
    if (toastTimer) clearTimeout(toastTimer);
    toast.value = { show: true, message, type };
    toastTimer = setTimeout(() => { toast.value.show = false; }, 4000);
}

const user = computed(() => auth.currentUser);

onMounted(async () => {
    loading.value = true;
    const { data, error } = await api.absGet('/api/admin/pending-clinics');
    if (data?.data) clinics.value = data.data;
    if (error) showToast(error, 'error');
    loading.value = false;
});

async function approveClinic(clinicId) {
    approving.value = clinicId;
    const { error } = await api.absPatch(`/api/admin/clinics/${clinicId}/approve`);
    if (!error) {
        clinics.value = clinics.value.filter(c => c.clinic_id !== clinicId);
        showToast('Clinic approved successfully!');
    } else {
        showToast(error, 'error');
    }
    approving.value = null;
}

async function rejectClinic(clinicId) {
    rejecting.value = clinicId;
    const { error } = await api.absPatch(`/api/admin/clinics/${clinicId}/reject`);
    if (!error) {
        clinics.value = clinics.value.filter(c => c.clinic_id !== clinicId);
        showToast('Clinic rejected.');
    } else {
        showToast(error, 'error');
    }
    rejecting.value = null;
}

function formatDate(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
    });
}

async function handleLogout() {
    await signOut(auth);
    router.push('/manager/login');
}
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-slate-50" style="font-family: 'Inter', sans-serif;">
        <!-- ── Sidebar ── -->
        <aside class="w-64 flex flex-col bg-gradient-to-b from-slate-900 to-slate-800 text-white">
            <!-- Brand -->
            <div class="flex h-16 items-center gap-3 px-5 border-b border-white/10">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500 shadow-lg shadow-amber-500/30">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
                <span class="text-base font-bold tracking-tight">
                    Paw<span class="text-amber-400">Health</span>
                    <span class="ml-1 text-xs font-normal text-slate-400">Admin</span>
                </span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4">
                <div class="rounded-lg bg-amber-500/20 px-3 py-2.5 text-sm font-medium text-amber-300 shadow-sm">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Pending Clinics</span>
                    </div>
                </div>
            </nav>

            <!-- Logout -->
            <div class="border-t border-white/10 px-3 py-3">
                <button
                    @click="handleLogout"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-400 transition-all hover:bg-white/5 hover:text-white"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                    Logout
                </button>
            </div>
        </aside>

        <!-- ── Main Content ── -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-6 shadow-sm">
                <div>
                    <h1 class="text-lg font-semibold text-slate-800">Super Manager Dashboard</h1>
                    <p class="text-xs text-slate-400">Review and approve clinic registrations</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white text-xs font-bold shadow-md">
                        {{ user?.email?.charAt(0)?.toUpperCase() || 'A' }}
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-sm font-medium text-slate-700">{{ user?.email || 'Admin' }}</p>
                        <p class="text-xs text-slate-400">Super Admin</p>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Toast -->
                <transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="translate-y-[-8px] opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="translate-y-[-8px] opacity-0"
                >
                    <div
                        v-if="toast.show"
                        :class="[
                            'fixed right-6 top-6 z-50 flex items-center gap-3 rounded-xl px-5 py-3.5 text-sm font-medium shadow-lg backdrop-blur-sm',
                            toast.type === 'success' ? 'bg-emerald-600/95 text-white' : 'bg-red-600/95 text-white',
                        ]"
                    >
                        <svg v-if="toast.type === 'success'" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        {{ toast.message }}
                        <button @click="toast.show = false" class="ml-2 rounded-lg p-1 transition-colors hover:bg-white/20">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </transition>

                <div class="space-y-6">
                    <!-- Page Header -->
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">Pending Clinic Approvals</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Review clinic registrations and verify their licenses before approval.
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/10">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            {{ clinics.length }} Pending
                        </span>
                    </div>

                    <!-- Loading Skeleton -->
                    <div v-if="loading" class="space-y-4">
                        <div v-for="n in 3" :key="n" class="h-24 rounded-2xl bg-slate-200 animate-pulse"></div>
                    </div>

                    <!-- Data Table -->
                    <div v-else class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                        <!-- Empty State -->
                        <div v-if="clinics.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 mb-5">
                                <svg class="h-10 w-10 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-base font-semibold text-slate-700">All caught up!</p>
                            <p class="mt-1 text-sm text-slate-400">There are no pending clinic registrations to review.</p>
                        </div>

                        <!-- Table -->
                        <table v-else class="w-full">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/50">
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Clinic</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Manager</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Location</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Date Applied</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">License</th>
                                    <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="clinic in clinics"
                                    :key="clinic.clinic_id"
                                    class="transition-colors hover:bg-slate-50/50"
                                >
                                    <!-- Clinic Name -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-violet-400 to-violet-600 text-sm font-bold text-white shadow-sm">
                                                {{ clinic.name?.charAt(0)?.toUpperCase() || '?' }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-slate-800">{{ clinic.name }}</p>
                                                <p class="text-xs text-slate-400">{{ clinic.phone || 'No phone' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Manager -->
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-slate-700">{{ clinic.manager_name }}</p>
                                        <p class="text-xs text-slate-400">{{ clinic.manager_email }}</p>
                                    </td>

                                    <!-- Location -->
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-slate-600">{{ clinic.city }}, {{ clinic.state }}</p>
                                        <p class="text-xs text-slate-400 max-w-[180px] truncate" :title="clinic.address">{{ clinic.address }}</p>
                                    </td>

                                    <!-- Date Applied -->
                                    <td class="px-6 py-4 text-sm text-slate-500">
                                        {{ formatDate(clinic.created_at) }}
                                    </td>

                                    <!-- License -->
                                    <td class="px-6 py-4">
                                        <a
                                            v-if="clinic.license_file_url"
                                            :href="clinic.license_file_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-violet-200 px-3 py-1.5 text-xs font-medium text-violet-600 transition-all hover:bg-violet-50 hover:border-violet-300 hover:shadow-sm"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                            </svg>
                                            View License
                                        </a>
                                        <span v-else class="text-xs text-slate-400 italic">No file</span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                @click="rejectClinic(clinic.clinic_id)"
                                                :disabled="rejecting === clinic.clinic_id || approving === clinic.clinic_id"
                                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors disabled:opacity-50"
                                            >
                                                <div v-if="rejecting === clinic.clinic_id" class="h-3 w-3 animate-spin rounded-full border-2 border-red-500 border-t-transparent"></div>
                                                <svg v-else class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Reject
                                            </button>
                                            <button
                                                @click="approveClinic(clinic.clinic_id)"
                                                :disabled="approving === clinic.clinic_id || rejecting === clinic.clinic_id"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-emerald-700 hover:shadow-md active:scale-95 transition-all disabled:opacity-60"
                                            >
                                                <div v-if="approving === clinic.clinic_id" class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                                                <svg v-else class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                                Approve
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Footer -->
                        <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-3">
                            <p class="text-xs text-slate-400">
                                {{ clinics.length }} pending clinic{{ clinics.length !== 1 ? 's' : '' }}
                            </p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
