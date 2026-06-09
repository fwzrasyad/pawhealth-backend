<script setup>
import { ref, onMounted, computed } from 'vue';
import ManagerLayout from '../layouts/ManagerLayout.vue';
import StatCard      from '../components/StatCard.vue';
import { useApi }    from '../composables/useApi.js';

const api     = useApi();
const stats   = ref(null);
const loading = ref(true);
const appointments = ref([]);
const vets = ref([]);

onMounted(async () => {
    loading.value = true;
    try {
        const [statsRes, apptsRes, vetsRes] = await Promise.all([
            api.get('/stats'),
            api.get('/appointments'),
            api.get('/veterinarians')
        ]);
        if (statsRes.data) stats.value = statsRes.data;
        if (apptsRes.data?.data) appointments.value = apptsRes.data.data;
        if (vetsRes.data?.data) vets.value = vetsRes.data.data;
    } catch (e) {
        console.error(e);
    }
    loading.value = false;
});

const nextAppt = computed(() => appointments.value.find(a => a.status === 'confirmed') || appointments.value[0]);
const upcomingAppts = computed(() => appointments.value.filter(a => a !== nextAppt.value).slice(0, 4));

function formatDate(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}
function formatTime(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <ManagerLayout>
        <div class="space-y-[32px] pb-10">
            <!-- Page Header -->
            <div>
                <h2 class="text-[24px] font-bold text-dark-text tracking-[-0.4px]">Dashboard Overview</h2>
                <p class="text-[13px] font-normal text-meta-text mt-1">Welcome back! Here's what's happening at your clinic today.</p>
            </div>

            <!-- Stat Cards Grid -->
            <div class="grid grid-cols-1 gap-[12px] sm:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    title="Clinic Patients"
                    :value="stats?.total_users ?? '—'"
                    subtitle="Your clinic's pet owners"
                    color="primary"
                    :loading="loading"
                >
                    <template #icon><i class="ti ti-users"></i></template>
                </StatCard>

                <StatCard
                    title="Clinic Veterinarians"
                    :value="stats?.total_vets ?? '—'"
                    :subtitle="stats ? `${stats.approved_vets ?? 0} approved, ${stats.pending_vets ?? 0} pending` : ''"
                    color="primary"
                    :loading="loading"
                >
                    <template #icon><i class="ti ti-stethoscope"></i></template>
                </StatCard>

                <StatCard
                    title="Clinic Pets"
                    :value="stats?.total_pets ?? '—'"
                    subtitle="Registered at your clinic"
                    color="primary"
                    :loading="loading"
                >
                    <template #icon><i class="ti ti-paw"></i></template>
                </StatCard>

                <StatCard
                    title="Pending Appointments"
                    :value="stats?.pending_appointments ?? '—'"
                    subtitle="Awaiting confirmation"
                    color="pending"
                    :loading="loading"
                >
                    <template #icon><i class="ti ti-calendar-clock"></i></template>
                </StatCard>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 gap-[10px] sm:grid-cols-3">
                <router-link
                    to="/manager/veterinarians"
                    class="group flex flex-col justify-between rounded-[14px] border border-card-border bg-white p-[16px] transition-all hover:border-primary hover:bg-[#FDFCFF]"
                >
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-[26px] font-bold text-dark-text tracking-[-0.5px]">{{ stats?.total_vets ?? 0 }}</span>
                        <i class="ti ti-stethoscope text-[22px] text-primary"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-dark-text">Manage Veterinarians</p>
                        <p class="text-[12px] text-meta-text mb-3">Review & approve profiles</p>
                        <div class="flex items-center text-[11.5px] font-semibold text-primary">
                            Review vets <i class="ti ti-arrow-right ml-1"></i>
                        </div>
                    </div>
                </router-link>

                <router-link
                    to="/manager/users"
                    class="group flex flex-col justify-between rounded-[14px] border border-card-border bg-white p-[16px] transition-all hover:border-primary hover:bg-[#FDFCFF]"
                >
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-[26px] font-bold text-dark-text tracking-[-0.5px]">{{ stats?.total_users ?? 0 }}</span>
                        <i class="ti ti-users text-[22px] text-primary"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-dark-text">View Users</p>
                        <p class="text-[12px] text-meta-text mb-3">Browse users & their pets</p>
                        <div class="flex items-center text-[11.5px] font-semibold text-primary">
                            See users <i class="ti ti-arrow-right ml-1"></i>
                        </div>
                    </div>
                </router-link>

                <router-link
                    to="/manager/appointments"
                    :class="[
                        'group flex flex-col justify-between rounded-[14px] border border-card-border bg-white p-[16px] transition-all',
                        stats?.pending_appointments > 0 ? 'hover:border-pending-text hover:bg-[#FDFCFF]' : 'hover:border-primary hover:bg-[#FDFCFF]'
                    ]"
                >
                    <div class="flex justify-between items-start mb-3">
                        <span :class="['text-[26px] font-bold tracking-[-0.5px]', stats?.pending_appointments > 0 ? 'text-pending-text' : 'text-dark-text']">
                            {{ stats?.pending_appointments ?? 0 }}
                        </span>
                        <i :class="['ti ti-calendar-event text-[22px]', stats?.pending_appointments > 0 ? 'text-pending-text' : 'text-primary']"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-dark-text">Manage Appointments</p>
                        <p class="text-[12px] text-meta-text mb-3">Assign vets to bookings</p>
                        <div :class="['flex items-center text-[11.5px] font-semibold', stats?.pending_appointments > 0 ? 'text-pending-text' : 'text-primary']">
                            View requests <i class="ti ti-arrow-right ml-1"></i>
                        </div>
                    </div>
                </router-link>
            </div>

            <!-- Bottom Panels -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-[12px]">
                
                <!-- Upcoming Appointments Panel -->
                <div class="rounded-[16px] border border-card-border bg-white p-[18px] flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[13.5px] font-bold text-dark-text">Upcoming Appointments</h3>
                        <span class="rounded-[100px] bg-pending-bg px-2 py-0.5 text-[11px] font-semibold text-pending-text">
                            {{ stats?.pending_appointments ?? 0 }} pending
                        </span>
                    </div>

                    <div v-if="loading" class="animate-pulse h-[140px] bg-slate-100 rounded-[12px]"></div>

                    <template v-else>
                        <!-- Hero Card -->
                        <div v-if="nextAppt" class="rounded-[12px] bg-dark-bg p-[14px_16px] mb-4">
                            <p class="text-[11px] uppercase text-nav-inactive mb-1">Next Appointment</p>
                            <p class="text-[17px] font-bold text-white">{{ nextAppt.pet_name || 'Unknown Pet' }}</p>
                            <p class="text-[12.5px] text-nav-inactive mb-3">
                                {{ nextAppt.reason || 'Checkup' }} • {{ formatDate(nextAppt.appointment_date) }} at {{ formatTime(nextAppt.time_slot) }}
                            </p>
                            
                            <div class="mt-[12px] flex items-center justify-between border-t border-white/20 pt-[10px]">
                                <div class="flex items-center gap-3 text-[12px] text-nav-inactive">
                                    <span class="flex items-center gap-1"><i class="ti ti-stethoscope"></i> {{ nextAppt.vet_name || 'Unassigned' }}</span>
                                    <!-- <span class="flex items-center gap-1"><i class="ti ti-user"></i> Owner Name</span> -->
                                </div>
                                <span class="rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-semibold text-[#EDE9FE] capitalize">
                                    {{ nextAppt.status }}
                                </span>
                            </div>
                        </div>

                        <!-- List Rows -->
                        <div class="flex flex-col">
                            <div 
                                v-for="(appt, index) in upcomingAppts" 
                                :key="appt.appointment_id"
                                :class="['flex items-center py-3', index !== upcomingAppts.length - 1 ? 'border-b border-surface' : '']"
                            >
                                <div class="h-[7px] w-[7px] shrink-0 rounded-full bg-primary mr-3 opacity-60"></div>
                                <div class="flex flex-1 flex-col">
                                    <span class="text-[13px] font-semibold text-dark-text">{{ appt.pet_name }}</span>
                                    <span class="text-[11.5px] text-meta-text">{{ appt.reason }} • {{ appt.vet_name || 'Unassigned' }}</span>
                                </div>
                                <div class="flex flex-col items-end mr-3">
                                    <span class="text-[11.5px] text-dark-text">{{ formatTime(appt.time_slot) }}</span>
                                    <span class="text-[10px] text-meta-text">{{ formatDate(appt.appointment_date) }}</span>
                                </div>
                                <span 
                                    v-if="appt.status === 'confirmed'" 
                                    class="rounded-full bg-confirmed-bg px-2 py-0.5 text-[10px] font-semibold text-confirmed-text capitalize"
                                >{{ appt.status }}</span>
                                <span 
                                    v-else-if="appt.status === 'completed'" 
                                    class="rounded-full bg-completed-bg px-2 py-0.5 text-[10px] font-semibold text-completed-text capitalize"
                                >{{ appt.status }}</span>
                                <span 
                                    v-else-if="appt.status === 'pending'" 
                                    class="rounded-full bg-pending-bg px-2 py-0.5 text-[10px] font-semibold text-pending-text capitalize"
                                >{{ appt.status }}</span>
                                <span 
                                    v-else 
                                    class="rounded-full bg-surface px-2 py-0.5 text-[10px] font-semibold text-muted-text capitalize"
                                >{{ appt.status }}</span>
                            </div>
                            <p v-if="upcomingAppts.length === 0" class="text-[12px] text-meta-text text-center py-4">No other upcoming appointments</p>
                        </div>
                    </template>
                </div>

                <!-- Veterinarians Panel -->
                <div class="rounded-[16px] border border-card-border bg-white p-[18px] flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[13.5px] font-bold text-dark-text">Veterinarians</h3>
                        <span class="rounded-[100px] bg-completed-bg px-2 py-0.5 text-[11px] font-semibold text-completed-text">
                            {{ stats?.approved_vets ?? 0 }} approved
                        </span>
                    </div>

                    <div v-if="loading" class="flex-1 animate-pulse bg-slate-100 rounded-[12px]"></div>

                    <div v-else class="flex-1 flex flex-col">
                        <div 
                            v-for="(vet, index) in vets" 
                            :key="vet.id || index"
                            :class="[
                                'flex items-center py-3', 
                                index !== vets.length - 1 ? 'border-b border-surface' : '',
                                vet.status === 'pending' ? 'opacity-45' : ''
                            ]"
                        >
                            <div class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-full bg-chip-bg text-[11.5px] font-bold text-primary-dark mr-3 overflow-hidden">
                                <img v-if="vet.profile_image_url" :src="vet.profile_image_url" class="h-full w-full object-cover" />
                                <span v-else>{{ vet.name ? vet.name.charAt(0).toUpperCase() : 'V' }}</span>
                            </div>
                            <div class="flex flex-1 flex-col">
                                <span class="text-[13px] font-semibold text-dark-text">{{ vet.name }}</span>
                                <span class="text-[11.5px] text-meta-text">{{ vet.specialty || 'General Practice' }} • 0 appts today</span>
                            </div>
                            <span 
                                v-if="vet.status === 'approved'" 
                                class="rounded-[100px] bg-completed-bg px-2 py-0.5 text-[10px] font-semibold text-completed-text capitalize"
                            >Approved</span>
                            <span 
                                v-else-if="vet.status === 'pending'" 
                                class="rounded-[100px] bg-pending-bg px-2 py-0.5 text-[10px] font-semibold text-pending-text capitalize"
                            >Pending</span>
                            <span 
                                v-else 
                                class="rounded-[100px] bg-surface px-2 py-0.5 text-[10px] font-semibold text-muted-text capitalize"
                            >{{ vet.status || 'Unknown' }}</span>
                        </div>
                        <p v-if="vets.length === 0" class="text-[12px] text-meta-text text-center py-4">No veterinarians found</p>
                    </div>

                    <div class="mt-auto pt-4 flex gap-2">
                        <router-link to="/manager/veterinarians" class="flex-1 text-center rounded-[10px] bg-primary px-3 py-[9px] text-[12px] font-semibold text-white hover:bg-primary-dark transition-colors">
                            Review vets
                        </router-link>
                    </div>
                </div>

            </div>
        </div>
    </ManagerLayout>
</template>
