<script setup>
import { ref, computed, onMounted } from 'vue';
import ManagerLayout   from '../layouts/ManagerLayout.vue';
import TableSkeleton   from '../components/TableSkeleton.vue';
import { useApi }      from '../composables/useApi.js';

const api     = useApi();
const vets    = ref([]);
const loading = ref(true);
const search  = ref('');
const updating = ref(null); // vet_id currently being updated

// Filter vets by search term
const filteredVets = computed(() => {
    if (!search.value) return vets.value;
    const q = search.value.toLowerCase();
    return vets.value.filter(v =>
        v.name?.toLowerCase().includes(q) ||
        v.email?.toLowerCase().includes(q) ||
        (v.specialties || []).join(' ').toLowerCase().includes(q)
    );
});

// Counts
const approvedCount = computed(() => vets.value.filter(v => v.status === 'approved').length);
const pendingCount  = computed(() => vets.value.filter(v => v.status === 'pending').length);

onMounted(fetchVets);

async function fetchVets() {
    loading.value = true;
    const { data } = await api.get('/veterinarians');
    if (data?.data) vets.value = data.data;
    loading.value = false;
}

async function toggleStatus(vet) {
    const newStatus = vet.status === 'approved' ? 'suspended' : 'approved';
    updating.value = vet.vet_id;

    const { data, error } = await api.put(`/veterinarians/${vet.vet_id}`, {
        status: newStatus,
    });

    if (!error) {
        vet.status = newStatus;
    }

    updating.value = null;
}

function statusColor(status) {
    switch (status) {
        case 'approved':  return 'bg-emerald-50 text-emerald-700 ring-emerald-600/10';
        case 'pending':   return 'bg-amber-50 text-amber-700 ring-amber-600/10';
        case 'suspended': return 'bg-red-50 text-red-700 ring-red-600/10';
        default:          return 'bg-slate-50 text-slate-700 ring-slate-600/10';
    }
}
</script>

<template>
    <ManagerLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Veterinarians</h2>
                    <p class="mt-1 text-sm text-slate-500">Manage and approve veterinarian accounts.</p>
                </div>

                <!-- Summary badges -->
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        {{ approvedCount }} Approved
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/10">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        {{ pendingCount }} Pending
                    </span>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative max-w-md">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by name, email, or specialty…"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none ring-1 ring-transparent transition-all placeholder:text-slate-400 focus:border-violet-300 focus:ring-violet-200"
                />
            </div>

            <!-- Loading Skeleton -->
            <TableSkeleton v-if="loading" :columns="5" :rows="5" />

            <!-- Data Table -->
            <div v-else class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <!-- Empty state -->
                <div v-if="filteredVets.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4">
                        <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-600">No veterinarians found</p>
                    <p class="mt-1 text-xs text-slate-400">{{ search ? 'Try adjusting your search.' : 'Veterinarians will appear here once registered.' }}</p>
                </div>

                <!-- Table -->
                <table v-else class="w-full">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Veterinarian</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Specialties</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Working Hours</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="vet in filteredVets"
                            :key="vet.vet_id"
                            class="transition-colors hover:bg-slate-50/50"
                        >
                            <!-- Name & Email -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-sm font-bold text-white shadow-sm">
                                        {{ vet.name?.charAt(0)?.toUpperCase() || '?' }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">{{ vet.name }}</p>
                                        <p class="text-xs text-slate-400">{{ vet.email || 'No email' }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Specialties -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="spec in (vet.specialties || []).slice(0, 3)"
                                        :key="spec"
                                        class="inline-flex rounded-md bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-700"
                                    >
                                        {{ spec }}
                                    </span>
                                    <span
                                        v-if="(vet.specialties || []).length > 3"
                                        class="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500"
                                    >
                                        +{{ vet.specialties.length - 3 }}
                                    </span>
                                    <span v-if="!(vet.specialties || []).length" class="text-xs text-slate-300">—</span>
                                </div>
                            </td>

                            <!-- Working Hours -->
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ vet.working_hours || '—' }}
                            </td>

                            <!-- Status Badge -->
                            <td class="px-6 py-4">
                                <span
                                    :class="statusColor(vet.status)"
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold capitalize ring-1 ring-inset"
                                >
                                    <span
                                        :class="{
                                            'bg-emerald-500': vet.status === 'approved',
                                            'bg-amber-500':   vet.status === 'pending',
                                            'bg-red-500':     vet.status === 'suspended',
                                        }"
                                        class="h-1.5 w-1.5 rounded-full"
                                    ></span>
                                    {{ vet.status || 'pending' }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <button
                                    @click="toggleStatus(vet)"
                                    :disabled="updating === vet.vet_id"
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-all',
                                        vet.status === 'approved'
                                            ? 'border border-red-200 text-red-600 hover:bg-red-50'
                                            : 'border border-emerald-200 text-emerald-600 hover:bg-emerald-50',
                                        updating === vet.vet_id ? 'opacity-50 cursor-not-allowed' : '',
                                    ]"
                                >
                                    <div v-if="updating === vet.vet_id" class="h-3 w-3 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
                                    <template v-else>
                                        <svg v-if="vet.status === 'approved'" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        <svg v-else class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ vet.status === 'approved' ? 'Suspend' : 'Approve' }}
                                    </template>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Footer -->
                <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-3">
                    <p class="text-xs text-slate-400">
                        Showing {{ filteredVets.length }} of {{ vets.length }} veterinarian{{ vets.length !== 1 ? 's' : '' }}
                    </p>
                </div>
            </div>
        </div>
    </ManagerLayout>
</template>
