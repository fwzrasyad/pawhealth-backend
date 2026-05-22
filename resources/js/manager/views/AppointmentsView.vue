<script setup>
import { ref, computed, onMounted } from 'vue';
import ManagerLayout from '../layouts/ManagerLayout.vue';
import TableSkeleton from '../components/TableSkeleton.vue';
import { useApi }    from '../composables/useApi.js';

const api          = useApi();
const appointments = ref([]);
const loading      = ref(true);
const search       = ref('');
const activeTab    = ref('pending');   // 'pending' | 'confirmed' | 'all'

// Track appointment currently being updated
const updatingId = ref(null);

// Toast state
const toast = ref({ show: false, message: '', type: 'success' });
let toastTimer = null;

function showToast(message, type = 'success') {
    if (toastTimer) clearTimeout(toastTimer);
    toast.value = { show: true, message, type };
    toastTimer = setTimeout(() => {
        toast.value.show = false;
    }, 4000);
}

// Filtered & tabbed appointments
const filteredAppointments = computed(() => {
    let list = appointments.value;

    // Tab filter
    if (activeTab.value === 'pending') {
        list = list.filter(a => a.status === 'pending');
    } else if (activeTab.value === 'confirmed') {
        list = list.filter(a => a.status === 'confirmed');
    }

    // Search filter
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(a =>
            a.pet_name?.toLowerCase().includes(q) ||
            a.vet_name?.toLowerCase().includes(q) ||
            a.reason?.toLowerCase().includes(q) ||
            a.appointment_id?.toLowerCase().includes(q)
        );
    }

    return list;
});

// Tab counts
const pendingCount   = computed(() => appointments.value.filter(a => a.status === 'pending').length);
const confirmedCount = computed(() => appointments.value.filter(a => a.status === 'confirmed').length);
const totalCount     = computed(() => appointments.value.length);

onMounted(async () => {
    loading.value = true;
    await fetchAppointments();
    loading.value = false;
});

async function fetchAppointments() {
    const { data } = await api.get('/appointments');
    if (data?.data) appointments.value = data.data;
}

async function updateAppointmentStatus(appointmentId, newStatus) {
    updatingId.value = appointmentId;

    const { data, error } = await api.absPut(`/api/appointments/${appointmentId}`, {
        status: newStatus
    });

    if (!error && data) {
        // Update local state
        const idx = appointments.value.findIndex(a => a.appointment_id === appointmentId);
        if (idx !== -1) {
            const updated = data.data || data;
            appointments.value[idx] = {
                ...appointments.value[idx],
                status: updated.status ?? newStatus,
            };
        }
        showToast(`Appointment status updated to ${newStatus}.`);
    } else {
        showToast(error || 'Failed to update appointment status.', 'error');
    }

    updatingId.value = null;
}

function formatDate(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('en-US', {
        year:  'numeric',
        month: 'short',
        day:   'numeric',
    });
}

function formatTime(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleTimeString('en-US', {
        hour:   '2-digit',
        minute: '2-digit',
    });
}

function statusColor(status) {
    switch (status) {
        case 'pending':   return 'bg-amber-50 text-amber-700 ring-amber-600/10';
        case 'confirmed': return 'bg-emerald-50 text-emerald-700 ring-emerald-600/10';
        case 'completed': return 'bg-slate-100 text-slate-600 ring-slate-500/10';
        case 'cancelled': return 'bg-red-50 text-red-600 ring-red-500/10';
        default:          return 'bg-slate-50 text-slate-700 ring-slate-600/10';
    }
}

function statusDotColor(status) {
    switch (status) {
        case 'pending':   return 'bg-amber-500';
        case 'confirmed': return 'bg-emerald-500';
        case 'completed': return 'bg-slate-400';
        case 'cancelled': return 'bg-red-500';
        default:          return 'bg-slate-400';
    }
}
</script>

<template>
    <ManagerLayout>
        <div class="space-y-6">
            <!-- Toast Notification -->
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
                        toast.type === 'success'
                            ? 'bg-emerald-600/95 text-white'
                            : 'bg-red-600/95 text-white',
                    ]"
                >
                    <!-- Success icon -->
                    <svg v-if="toast.type === 'success'" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- Error icon -->
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

            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Appointments</h2>
                    <p class="mt-1 text-sm text-slate-500">Review incoming requests and update appointment statuses.</p>
                </div>

                <!-- Summary badges -->
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/10">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        {{ pendingCount }} Pending
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        {{ confirmedCount }} Confirmed
                    </span>
                </div>
            </div>

            <!-- Tab Bar + Search -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <!-- Tabs -->
                <div class="flex rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
                    <button
                        v-for="tab in [
                            { key: 'pending',   label: 'Pending',   count: pendingCount },
                            { key: 'confirmed', label: 'Confirmed', count: confirmedCount },
                            { key: 'all',       label: 'All',       count: totalCount },
                        ]"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200',
                            activeTab === tab.key
                                ? 'bg-violet-600 text-white shadow-sm'
                                : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700',
                        ]"
                    >
                        {{ tab.label }}
                        <span
                            :class="[
                                'inline-flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-xs font-bold',
                                activeTab === tab.key
                                    ? 'bg-white/20 text-white'
                                    : 'bg-slate-100 text-slate-500',
                            ]"
                        >
                            {{ tab.count }}
                        </span>
                    </button>
                </div>

                <!-- Search -->
                <div class="relative max-w-md">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by pet name, vet, or reason…"
                        class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none ring-1 ring-transparent transition-all placeholder:text-slate-400 focus:border-violet-300 focus:ring-violet-200"
                    />
                </div>
            </div>

            <!-- Loading Skeleton -->
            <TableSkeleton v-if="loading" :columns="6" :rows="6" />

            <!-- Data Table -->
            <div v-else class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <!-- Empty state -->
                <div v-if="filteredAppointments.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4">
                        <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-600">No {{ activeTab !== 'all' ? activeTab : '' }} appointments found</p>
                    <p class="mt-1 text-xs text-slate-400">{{ search ? 'Try adjusting your search.' : 'Appointments will appear here once booked by pet owners.' }}</p>
                </div>

                <!-- Table -->
                <table v-else class="w-full">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pet</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Date & Time</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Reason</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Veterinarian</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="appt in filteredAppointments"
                            :key="appt.appointment_id"
                            :class="[
                                'transition-colors',
                                appt.status === 'pending' ? 'bg-amber-50/30 hover:bg-amber-50/60' : 'hover:bg-slate-50/50',
                            ]"
                        >
                            <!-- Pet -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-sm font-bold text-white shadow-sm">
                                        {{ appt.pet_name?.charAt(0)?.toUpperCase() || '?' }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">{{ appt.pet_name || 'Unknown' }}</p>
                                        <p class="text-xs text-slate-400 font-mono">{{ appt.appointment_id?.slice(0, 8) }}…</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Date & Time -->
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-slate-700">{{ formatDate(appt.appointment_date) }}</p>
                                <p class="text-xs text-slate-400">{{ formatTime(appt.time_slot) }}</p>
                            </td>

                            <!-- Reason -->
                            <td class="px-6 py-4">
                                <p class="max-w-[200px] truncate text-sm text-slate-600" :title="appt.reason">
                                    {{ appt.reason || '—' }}
                                </p>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-6 py-4">
                                <span
                                    :class="statusColor(appt.status)"
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold capitalize ring-1 ring-inset"
                                >
                                    <span :class="statusDotColor(appt.status)" class="h-1.5 w-1.5 rounded-full"></span>
                                    {{ appt.status }}
                                </span>
                            </td>

                            <!-- Veterinarian -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-xs font-bold text-white">
                                        {{ appt.vet_name ? appt.vet_name.charAt(0).toUpperCase() : 'V' }}
                                    </div>
                                    <span class="text-sm font-medium text-slate-700">{{ appt.vet_name || '—' }}</span>
                                </div>
                            </td>

                            <!-- Action -->
                            <td class="px-6 py-4 text-right">
                                <!-- Pending Actions -->
                                <div v-if="appt.status === 'pending'" class="flex items-center justify-end gap-2">
                                    <button
                                        @click="updateAppointmentStatus(appt.appointment_id, 'cancelled')"
                                        :disabled="updatingId === appt.appointment_id"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors disabled:opacity-50"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        @click="updateAppointmentStatus(appt.appointment_id, 'confirmed')"
                                        :disabled="updatingId === appt.appointment_id"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-emerald-700 hover:shadow-md active:scale-95 transition-all disabled:opacity-60"
                                    >
                                        <div v-if="updatingId === appt.appointment_id" class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                                        <svg v-else class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        Confirm
                                    </button>
                                </div>

                                <!-- Confirmed Actions -->
                                <div v-else-if="appt.status === 'confirmed'" class="flex items-center justify-end gap-2">
                                    <button
                                        @click="updateAppointmentStatus(appt.appointment_id, 'completed')"
                                        :disabled="updatingId === appt.appointment_id"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 border border-slate-200 px-3.5 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-200 active:scale-95 transition-all disabled:opacity-60"
                                    >
                                        <div v-if="updatingId === appt.appointment_id" class="h-3 w-3 animate-spin rounded-full border-2 border-slate-500 border-t-transparent"></div>
                                        <svg v-else class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Mark Completed
                                    </button>
                                </div>

                                <!-- Other states just show text -->
                                <span v-else class="text-xs text-slate-400 capitalize">{{ appt.status }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Footer -->
                <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-3">
                    <p class="text-xs text-slate-400">
                        Showing {{ filteredAppointments.length }} of {{ appointments.length }} appointment{{ appointments.length !== 1 ? 's' : '' }}
                    </p>
                </div>
            </div>
        </div>
    </ManagerLayout>
</template>
