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

</script>

<template>
    <ManagerLayout>
        <div class="space-y-[24px]">
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
                        'fixed right-6 top-6 z-50 flex items-center gap-3 rounded-[12px] px-5 py-3.5 text-sm font-medium',
                        toast.type === 'success'
                            ? 'bg-[#15803D] text-white'
                            : 'bg-[#B45309] text-white',
                    ]"
                >
                    <!-- Success icon -->
                    <i v-if="toast.type === 'success'" class="ti ti-check text-[20px]"></i>
                    <!-- Error icon -->
                    <i v-else class="ti ti-alert-circle text-[20px]"></i>
                    
                    {{ toast.message }}
                    <button @click="toast.show = false" class="ml-2 rounded-lg p-1 transition-colors hover:bg-white/20">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </transition>

            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-[24px] font-bold text-dark-text tracking-[-0.4px]">Appointments</h2>
                    <p class="mt-1 text-[13px] font-normal text-meta-text">Review incoming requests and update appointment statuses.</p>
                </div>

                <!-- Summary badges -->
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-[100px] bg-pending-bg px-3 py-1 text-xs font-semibold text-pending-text">
                        <span class="h-1.5 w-1.5 rounded-full bg-pending-text"></span>
                        {{ pendingCount }} Pending
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-[100px] bg-confirmed-bg px-3 py-1 text-xs font-semibold text-confirmed-text">
                        <span class="h-1.5 w-1.5 rounded-full bg-confirmed-text"></span>
                        {{ confirmedCount }} Confirmed
                    </span>
                </div>
            </div>

            <!-- Tab Bar + Search -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <!-- Tabs -->
                <div class="flex rounded-[10px] border border-card-border bg-white p-1">
                    <button
                        v-for="tab in [
                            { key: 'pending',   label: 'Pending',   count: pendingCount },
                            { key: 'confirmed', label: 'Confirmed', count: confirmedCount },
                            { key: 'all',       label: 'All',       count: totalCount },
                        ]"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'flex items-center gap-2 rounded-[8px] px-4 py-2 text-[13px] font-semibold transition-all duration-200',
                            activeTab === tab.key
                                ? 'bg-primary text-white'
                                : 'text-meta-text hover:bg-surface hover:text-dark-text',
                        ]"
                    >
                        {{ tab.label }}
                        <span
                            :class="[
                                'inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-[100px] px-1 text-[10px] font-bold',
                                activeTab === tab.key
                                    ? 'bg-white/20 text-white'
                                    : 'bg-surface text-meta-text',
                            ]"
                        >
                            {{ tab.count }}
                        </span>
                    </button>
                </div>

                <!-- Search -->
                <div class="relative max-w-md flex-1">
                    <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-meta-text text-[18px]"></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by pet name, vet, or reason…"
                        class="w-full rounded-[10px] border border-card-border bg-white py-[9px] pl-10 pr-4 text-[13px] text-dark-text outline-none transition-all placeholder:text-meta-text focus:border-primary"
                    />
                </div>
            </div>

            <!-- Loading Skeleton -->
            <TableSkeleton v-if="loading" :columns="6" :rows="6" />

            <!-- Data Table -->
            <div v-else class="overflow-hidden rounded-[16px] border border-card-border bg-white">
                <!-- Empty state -->
                <div v-if="filteredAppointments.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-surface mb-4">
                        <i class="ti ti-calendar text-[24px] text-meta-text"></i>
                    </div>
                    <p class="text-[14px] font-semibold text-dark-text">No {{ activeTab !== 'all' ? activeTab : '' }} appointments found</p>
                    <p class="mt-1 text-[13px] text-meta-text">{{ search ? 'Try adjusting your search.' : 'Appointments will appear here once booked by pet owners.' }}</p>
                </div>

                <!-- Table -->
                <table v-else class="w-full">
                    <thead>
                        <tr class="border-b border-surface bg-surface/30">
                            <th class="px-6 py-[14px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-meta-text">Pet</th>
                            <th class="px-6 py-[14px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-meta-text">Date & Time</th>
                            <th class="px-6 py-[14px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-meta-text">Reason</th>
                            <th class="px-6 py-[14px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-meta-text">Status</th>
                            <th class="px-6 py-[14px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-meta-text">Veterinarian</th>
                            <th class="px-6 py-[14px] text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-meta-text">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface">
                        <tr
                            v-for="appt in filteredAppointments"
                            :key="appt.appointment_id"
                            :class="[
                                'transition-colors hover:bg-surface/50',
                                appt.status === 'pending' ? 'bg-pending-bg/30' : '',
                            ]"
                        >
                            <!-- Pet -->
                            <td class="px-6 py-[16px]">
                                <div class="flex items-center gap-3">
                                    <div class="h-[7px] w-[7px] shrink-0 rounded-full bg-primary opacity-60"></div>
                                    <div class="flex h-[34px] w-[34px] overflow-hidden items-center justify-center rounded-full bg-chip-bg text-[11.5px] font-bold text-primary-dark">
                                        <img v-if="appt.pet?.profile_image_url" :src="appt.pet.profile_image_url" class="h-full w-full object-cover" alt="Pet Profile" />
                                        <span v-else>{{ appt.pet_name?.charAt(0)?.toUpperCase() || '?' }}</span>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-dark-text">{{ appt.pet_name || 'Unknown' }}</p>
                                        <p class="text-[11.5px] text-meta-text font-mono">{{ appt.appointment_id?.slice(0, 8) }}…</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Date & Time -->
                            <td class="px-6 py-[16px]">
                                <p class="text-[13px] font-medium text-dark-text">{{ formatTime(appt.time_slot) }}</p>
                                <p class="text-[11.5px] text-meta-text">{{ formatDate(appt.appointment_date) }}</p>
                            </td>

                            <!-- Reason -->
                            <td class="px-6 py-[16px]">
                                <p class="max-w-[200px] truncate text-[13px] text-dark-text" :title="appt.reason">
                                    {{ appt.reason || '—' }}
                                </p>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-6 py-[16px]">
                                <span 
                                    v-if="appt.status === 'confirmed'" 
                                    class="rounded-[100px] bg-confirmed-bg px-2 py-0.5 text-[11px] font-semibold text-confirmed-text capitalize"
                                >{{ appt.status }}</span>
                                <span 
                                    v-else-if="appt.status === 'completed'" 
                                    class="rounded-[100px] bg-completed-bg px-2 py-0.5 text-[11px] font-semibold text-completed-text capitalize"
                                >{{ appt.status }}</span>
                                <span 
                                    v-else-if="appt.status === 'pending'" 
                                    class="rounded-[100px] bg-pending-bg px-2 py-0.5 text-[11px] font-semibold text-pending-text capitalize"
                                >{{ appt.status }}</span>
                                <span 
                                    v-else 
                                    class="rounded-[100px] bg-surface px-2 py-0.5 text-[11px] font-semibold text-muted-text capitalize"
                                >{{ appt.status }}</span>
                            </td>

                            <!-- Veterinarian -->
                            <td class="px-6 py-[16px]">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-[28px] w-[28px] overflow-hidden items-center justify-center rounded-full bg-chip-bg text-[11px] font-bold text-primary-dark">
                                        <img v-if="appt.veterinarian?.profile_image_url" :src="appt.veterinarian.profile_image_url" class="h-full w-full object-cover" alt="Vet Profile" />
                                        <span v-else>{{ appt.vet_name ? appt.vet_name.charAt(0).toUpperCase() : 'V' }}</span>
                                    </div>
                                    <span class="text-[13px] font-medium text-dark-text">{{ appt.vet_name || '—' }}</span>
                                </div>
                            </td>

                            <!-- Action -->
                            <td class="px-6 py-[16px] text-right">
                                <!-- Pending Actions -->
                                <div v-if="appt.status === 'pending'" class="flex items-center justify-end gap-2">
                                    <button
                                        @click="updateAppointmentStatus(appt.appointment_id, 'cancelled')"
                                        :disabled="updatingId === appt.appointment_id"
                                        class="inline-flex items-center rounded-[10px] px-3 py-[7px] text-[11.5px] font-semibold text-[#B45309] hover:bg-[#FEF3C7] transition-colors disabled:opacity-50"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        @click="updateAppointmentStatus(appt.appointment_id, 'confirmed')"
                                        :disabled="updatingId === appt.appointment_id"
                                        class="inline-flex items-center rounded-[10px] bg-primary px-3 py-[7px] text-[11.5px] font-semibold text-white hover:bg-primary-dark transition-all disabled:opacity-60"
                                    >
                                        <div v-if="updatingId === appt.appointment_id" class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent mr-1"></div>
                                        <i v-else class="ti ti-check mr-1 text-[14px]"></i>
                                        Confirm
                                    </button>
                                </div>

                                <!-- Confirmed Actions -->
                                <div v-else-if="appt.status === 'confirmed'" class="flex items-center justify-end gap-2">
                                    <button
                                        @click="updateAppointmentStatus(appt.appointment_id, 'completed')"
                                        :disabled="updatingId === appt.appointment_id"
                                        class="inline-flex items-center rounded-[10px] bg-chip-bg px-3 py-[7px] text-[11.5px] font-semibold text-primary-dark hover:bg-card-border transition-all disabled:opacity-60"
                                    >
                                        <div v-if="updatingId === appt.appointment_id" class="h-3 w-3 animate-spin rounded-full border-2 border-primary-dark border-t-transparent mr-1"></div>
                                        <i v-else class="ti ti-check mr-1 text-[14px]"></i>
                                        Mark Completed
                                    </button>
                                </div>

                                <!-- Other states just show text -->
                                <span v-else class="text-[11.5px] text-meta-text capitalize">{{ appt.status }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Footer -->
                <div class="border-t border-surface bg-surface/30 px-6 py-4">
                    <p class="text-[12px] text-meta-text">
                        Showing {{ filteredAppointments.length }} of {{ appointments.length }} appointment{{ appointments.length !== 1 ? 's' : '' }}
                    </p>
                </div>
            </div>
        </div>
    </ManagerLayout>
</template>
