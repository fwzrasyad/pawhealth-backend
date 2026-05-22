<script setup>
import { ref, onMounted } from 'vue';
import ManagerLayout from '../layouts/ManagerLayout.vue';
import StatCard      from '../components/StatCard.vue';
import { useApi }    from '../composables/useApi.js';

const api     = useApi();
const stats   = ref(null);
const loading = ref(true);

onMounted(async () => {
    loading.value = true;
    const { data } = await api.get('/stats');
    if (data) stats.value = data;
    loading.value = false;
});
</script>

<template>
    <ManagerLayout>
        <div class="space-y-8">
            <!-- Page Header -->
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Dashboard Overview</h2>
                <p class="mt-1 text-sm text-slate-500">Welcome back! Here's what's happening at your clinic today.</p>
            </div>

            <!-- Stat Cards Grid -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <!-- Total Users -->
                <StatCard
                    title="Clinic Patients"
                    :value="stats?.total_users ?? '—'"
                    subtitle="Your clinic's pet owners"
                    color="violet"
                    :loading="loading"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </template>
                </StatCard>

                <!-- Total Vets -->
                <StatCard
                    title="Clinic Veterinarians"
                    :value="stats?.total_vets ?? '—'"
                    :subtitle="stats ? `${stats.approved_vets ?? 0} approved, ${stats.pending_vets ?? 0} pending` : ''"
                    color="emerald"
                    :loading="loading"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </template>
                </StatCard>

                <!-- Total Pets -->
                <StatCard
                    title="Clinic Pets"
                    :value="stats?.total_pets ?? '—'"
                    subtitle="Registered at your clinic"
                    color="amber"
                    :loading="loading"
                >
                    <template #icon>
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-4.5-2c-.83 0-1.5.67-1.5 1.5S6.67 11 7.5 11 9 10.33 9 9.5 8.33 8 7.5 8zm0 6c-.83 0-1.5.67-1.5 1.5S6.67 17 7.5 17 9 16.33 9 15.5 8.33 14 7.5 14zm9-6c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5S17.33 8 16.5 8z" />
                        </svg>
                    </template>
                </StatCard>

                <!-- Pending Appointments -->
                <StatCard
                    title="Pending Appointments"
                    :value="stats?.pending_appointments ?? '—'"
                    subtitle="Awaiting confirmation"
                    color="sky"
                    :loading="loading"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                        </svg>
                    </template>
                </StatCard>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-slate-800">Quick Actions</h3>
                <p class="mt-1 text-sm text-slate-400">Frequently used management tasks</p>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <router-link
                        to="/manager/veterinarians"
                        class="group flex items-center gap-4 rounded-xl border border-slate-200 p-4 transition-all hover:border-violet-200 hover:bg-violet-50/50 hover:shadow-sm"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 transition-transform group-hover:scale-110">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700">Manage Veterinarians</p>
                            <p class="text-xs text-slate-400">Review & approve vet profiles</p>
                        </div>
                    </router-link>

                    <router-link
                        to="/manager/users"
                        class="group flex items-center gap-4 rounded-xl border border-slate-200 p-4 transition-all hover:border-violet-200 hover:bg-violet-50/50 hover:shadow-sm"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-600 transition-transform group-hover:scale-110">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700">View Users</p>
                            <p class="text-xs text-slate-400">Browse users & their pets</p>
                        </div>
                    </router-link>

                    <router-link
                        to="/manager/appointments"
                        class="group flex items-center gap-4 rounded-xl border border-slate-200 p-4 transition-all hover:border-violet-200 hover:bg-violet-50/50 hover:shadow-sm"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-600 transition-transform group-hover:scale-110">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700">Manage Appointments</p>
                            <p class="text-xs text-slate-400">Assign vets to pending bookings</p>
                        </div>
                    </router-link>
                </div>
            </div>
        </div>
    </ManagerLayout>
</template>
