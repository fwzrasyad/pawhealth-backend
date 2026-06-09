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
const approving = ref(null);
const rejecting = ref(null);

// Toast
const toast = ref({ show: false, message: '', type: 'success' });
let toastTimer = null;

function showToast(message, type = 'success') {
    if (toastTimer) clearTimeout(toastTimer);
    toast.value = { show: true, message, type };
    toastTimer = setTimeout(() => { toast.value.show = false; }, 4000);
}

const user = computed(() => auth.currentUser);
const collapsed = ref(false);

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
    <div class="flex h-screen overflow-hidden bg-surface font-sans">
        <!-- ── Sidebar ── -->
        <aside
            :class="collapsed ? 'w-[72px]' : 'w-[228px]'"
            class="relative flex flex-col bg-sidebar-bg transition-all duration-300 ease-in-out"
        >
            <!-- Brand -->
            <div class="flex h-16 items-center gap-3 px-5 border-b border-primary/20 shrink-0">
                <div class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-[10px] bg-primary">
                    <img src="/pawhealth_logo.png" alt="Logo" class="h-5 w-5 object-contain" style="filter: brightness(0) invert(1);" />
                </div>
                <transition name="fade">
                    <span v-if="!collapsed" class="text-[17px] font-bold tracking-[-0.3px] text-white whitespace-nowrap">
                        PawHealth
                    </span>
                </transition>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-1.5 px-[14px] py-[18px] overflow-y-auto">
                <div class="flex items-center gap-3 rounded-[10px] bg-primary px-[12px] py-[9px] text-[13.5px] font-semibold text-white">
                    <i class="ti ti-shield-check text-[20px] shrink-0"></i>
                    <transition name="fade">
                        <span v-if="!collapsed" class="whitespace-nowrap">Pending Clinics</span>
                    </transition>
                </div>
            </nav>

            <!-- Collapse toggle -->
            <button
                @click="collapsed = !collapsed"
                class="flex h-10 items-center justify-center text-muted-text hover:text-white transition-colors"
            >
                <i v-if="collapsed" class="ti ti-chevron-right text-[18px]"></i>
                <i v-else class="ti ti-chevron-left text-[18px]"></i>
            </button>

            <!-- Footer Avatar -->
            <div class="flex items-center gap-3 px-[14px] py-[16px] border-t border-primary/20 shrink-0">
                <div class="flex h-[32px] w-[32px] shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-white">
                    {{ user?.email?.charAt(0)?.toUpperCase() || 'A' }}
                </div>
                <transition name="fade">
                    <div v-if="!collapsed" class="flex flex-1 items-center justify-between overflow-hidden">
                        <div class="flex flex-col truncate pr-2">
                            <span class="truncate text-[11.5px] text-nav-inactive">{{ user?.email || 'admin@pawhealth.com' }}</span>
                            <span class="text-[11px] text-muted-text">Super Admin</span>
                        </div>
                        <button @click="handleLogout" class="text-muted-text hover:text-white transition-colors shrink-0" title="Logout">
                            <i class="ti ti-logout text-[18px]"></i>
                        </button>
                    </div>
                </transition>
            </div>
        </aside>

        <!-- ── Main Content ── -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <main class="flex-1 overflow-y-auto p-[24px]">
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
                            'fixed right-6 top-6 z-50 flex items-center gap-3 rounded-[14px] px-5 py-3.5 text-[13px] font-semibold shadow-lg',
                            toast.type === 'success' ? 'bg-completed-text text-white' : 'bg-pending-text text-white',
                        ]"
                    >
                        <i :class="['ti text-[18px]', toast.type === 'success' ? 'ti-circle-check' : 'ti-alert-circle']"></i>
                        {{ toast.message }}
                        <button @click="toast.show = false" class="ml-2 rounded-[8px] p-1 transition-colors hover:bg-white/20">
                            <i class="ti ti-x text-[14px]"></i>
                        </button>
                    </div>
                </transition>

                <div class="space-y-[32px] pb-10">
                    <!-- Page Header -->
                    <div>
                        <h2 class="text-[24px] font-bold text-dark-text tracking-[-0.4px]">Pending Clinic Approvals</h2>
                        <p class="text-[13px] font-normal text-meta-text mt-1">Review clinic registrations and verify their licenses before approval.</p>
                    </div>

                    <!-- Stat Chip -->
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-[100px] bg-pending-bg px-3 py-1 text-[12px] font-semibold text-pending-text">
                            <span class="h-1.5 w-1.5 rounded-full bg-pending-text animate-pulse"></span>
                            {{ clinics.length }} Pending
                        </span>
                    </div>

                    <!-- Loading Skeleton -->
                    <div v-if="loading" class="space-y-4">
                        <div v-for="n in 3" :key="n" class="h-24 rounded-[14px] bg-chip-bg animate-pulse"></div>
                    </div>

                    <!-- Data Table -->
                    <div v-else class="overflow-hidden rounded-[16px] border border-card-border bg-white">
                        <!-- Empty State -->
                        <div v-if="clinics.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-chip-bg mb-5">
                                <i class="ti ti-circle-check text-[36px] text-primary"></i>
                            </div>
                            <p class="text-[14px] font-semibold text-dark-text">All caught up!</p>
                            <p class="mt-1 text-[12px] text-meta-text">There are no pending clinic registrations to review.</p>
                        </div>

                        <!-- Table -->
                        <table v-else class="w-full">
                            <thead>
                                <tr class="border-b border-card-border bg-surface/50">
                                    <th class="px-6 py-[12px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-muted-text">Clinic</th>
                                    <th class="px-6 py-[12px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-muted-text">Manager</th>
                                    <th class="px-6 py-[12px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-muted-text">Location</th>
                                    <th class="px-6 py-[12px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-muted-text">Date Applied</th>
                                    <th class="px-6 py-[12px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-muted-text">License</th>
                                    <th class="px-6 py-[12px] text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-muted-text">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-card-border">
                                <tr
                                    v-for="clinic in clinics"
                                    :key="clinic.clinic_id"
                                    class="transition-colors hover:bg-surface/50"
                                >
                                    <!-- Clinic Name -->
                                    <td class="px-6 py-[16px]">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-[36px] w-[36px] shrink-0 items-center justify-center rounded-full bg-chip-bg text-[13px] font-bold text-primary-dark">
                                                {{ clinic.name?.charAt(0)?.toUpperCase() || '?' }}
                                            </div>
                                            <div>
                                                <p class="text-[13px] font-semibold text-dark-text">{{ clinic.name }}</p>
                                                <p class="text-[11.5px] text-meta-text">{{ clinic.phone || 'No phone' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Manager -->
                                    <td class="px-6 py-[16px]">
                                        <p class="text-[13px] font-medium text-dark-text">{{ clinic.manager_name }}</p>
                                        <p class="text-[11.5px] text-meta-text">{{ clinic.manager_email }}</p>
                                    </td>

                                    <!-- Location -->
                                    <td class="px-6 py-[16px]">
                                        <p class="text-[13px] text-dark-text">{{ clinic.city }}, {{ clinic.state }}</p>
                                        <p class="text-[11.5px] text-meta-text max-w-[180px] truncate" :title="clinic.address">{{ clinic.address }}</p>
                                    </td>

                                    <!-- Date Applied -->
                                    <td class="px-6 py-[16px] text-[13px] text-muted-text">
                                        {{ formatDate(clinic.created_at) }}
                                    </td>

                                    <!-- License -->
                                    <td class="px-6 py-[16px]">
                                        <a
                                            v-if="clinic.license_file_url"
                                            :href="clinic.license_file_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 rounded-[10px] border border-card-border px-3 py-1.5 text-[12px] font-semibold text-primary transition-all hover:bg-chip-bg hover:border-primary"
                                        >
                                            <i class="ti ti-file-text text-[14px]"></i>
                                            View License
                                        </a>
                                        <span v-else class="text-[12px] text-meta-text italic">No file</span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-[16px] text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                @click="rejectClinic(clinic.clinic_id)"
                                                :disabled="rejecting === clinic.clinic_id || approving === clinic.clinic_id"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] bg-surface text-pending-text transition-colors hover:bg-pending-bg hover:text-pending-text disabled:opacity-50"
                                                title="Reject"
                                            >
                                                <div v-if="rejecting === clinic.clinic_id" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-pending-text border-t-transparent"></div>
                                                <i v-else class="ti ti-x text-[16px]"></i>
                                            </button>
                                            <button
                                                @click="approveClinic(clinic.clinic_id)"
                                                :disabled="approving === clinic.clinic_id || rejecting === clinic.clinic_id"
                                                class="inline-flex items-center gap-1.5 rounded-[10px] bg-primary px-3.5 py-[7px] text-[12px] font-semibold text-white hover:bg-primary-dark active:scale-[0.97] transition-all disabled:opacity-50"
                                            >
                                                <div v-if="approving === clinic.clinic_id" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                                                <i v-else class="ti ti-check text-[14px]"></i>
                                                Approve
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Footer -->
                        <div class="border-t border-card-border bg-surface/30 px-6 py-3">
                            <p class="text-[11.5px] text-meta-text">
                                {{ clinics.length }} pending clinic{{ clinics.length !== 1 ? 's' : '' }}
                            </p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
