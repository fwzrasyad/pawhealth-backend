<script setup>
import { ref, computed, onMounted } from 'vue';
import ManagerLayout from '../layouts/ManagerLayout.vue';
import TableSkeleton from '../components/TableSkeleton.vue';
import ConfirmModal  from '../components/ConfirmModal.vue';
import { useApi }    from '../composables/useApi.js';

const api     = useApi();
const users   = ref([]);
const loading = ref(true);
const search  = ref('');

// Expand / pets
const expandedUserId = ref(null);
const petsMap        = ref({});       // { userId: [pets] }
const petsLoading    = ref(null);     // userId currently loading pets

// Delete
const deleteTarget   = ref(null);
const showDeleteModal = ref(false);
const deleting       = ref(false);

// Filtered users
const filteredUsers = computed(() => {
    if (!search.value) return users.value;
    const q = search.value.toLowerCase();
    return users.value.filter(u =>
        u.name?.toLowerCase().includes(q) ||
        u.email?.toLowerCase().includes(q) ||
        u.phone_number?.toLowerCase().includes(q)
    );
});

onMounted(fetchUsers);

async function fetchUsers() {
    loading.value = true;
    const { data } = await api.get('/users');
    if (data?.data) users.value = data.data;
    loading.value = false;
}

async function toggleExpand(user) {
    if (expandedUserId.value === user.user_id) {
        expandedUserId.value = null;
        return;
    }

    expandedUserId.value = user.user_id;

    // Fetch pets if not cached
    if (!petsMap.value[user.user_id]) {
        petsLoading.value = user.user_id;
        const { data } = await api.get(`/users/${user.user_id}/pets`);
        petsMap.value[user.user_id] = data?.data || [];
        petsLoading.value = null;
    }
}

function confirmDelete(user) {
    deleteTarget.value    = user;
    showDeleteModal.value = true;
}

async function executeDelete() {
    if (!deleteTarget.value) return;

    deleting.value = true;
    const { error } = await api.del(`/users/${deleteTarget.value.user_id}`);

    if (!error) {
        users.value = users.value.filter(u => u.user_id !== deleteTarget.value.user_id);
        // Clean up expanded/pets state
        if (expandedUserId.value === deleteTarget.value.user_id) expandedUserId.value = null;
        delete petsMap.value[deleteTarget.value.user_id];
    }

    deleting.value        = false;
    showDeleteModal.value = false;
    deleteTarget.value    = null;
}

function formatDate(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('en-US', {
        year:  'numeric',
        month: 'short',
        day:   'numeric',
    });
}
</script>

<template>
    <ManagerLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Users & Pets</h2>
                <p class="mt-1 text-sm text-slate-500">View registered pet owners and their pets.</p>
            </div>

            <!-- Search Bar -->
            <div class="relative max-w-md">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by name, email, or phone…"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none ring-1 ring-transparent transition-all placeholder:text-slate-400 focus:border-violet-300 focus:ring-violet-200"
                />
            </div>

            <!-- Loading -->
            <TableSkeleton v-if="loading" :columns="6" :rows="6" />

            <!-- Data Table -->
            <div v-else class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <!-- Empty -->
                <div v-if="filteredUsers.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4">
                        <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-600">No users found</p>
                    <p class="mt-1 text-xs text-slate-400">{{ search ? 'Try adjusting your search.' : 'Users will appear here once registered.' }}</p>
                </div>

                <table v-else class="w-full">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="w-10 px-6 py-3.5"></th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">User</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Phone</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pets</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Joined</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template v-for="user in filteredUsers" :key="user.user_id">
                            <!-- User Row -->
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <!-- Expand button -->
                                <td class="px-6 py-4">
                                    <button
                                        @click="toggleExpand(user)"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600"
                                    >
                                        <svg
                                            :class="expandedUserId === user.user_id ? 'rotate-90' : ''"
                                            class="h-4 w-4 transition-transform duration-200"
                                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </button>
                                </td>

                                <!-- Name & Email -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-violet-400 to-violet-600 text-sm font-bold text-white shadow-sm">
                                            {{ user.name?.charAt(0)?.toUpperCase() || '?' }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">{{ user.name || 'Unnamed' }}</p>
                                            <p class="text-xs text-slate-400">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Phone -->
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ user.phone_number || '—' }}
                                </td>

                                <!-- Pets count -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/10">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-4.5-2c-.83 0-1.5.67-1.5 1.5S6.67 11 7.5 11 9 10.33 9 9.5 8.33 8 7.5 8z"/>
                                        </svg>
                                        {{ user.pets_count ?? 0 }}
                                    </span>
                                </td>

                                <!-- Joined -->
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ formatDate(user.created_at) }}
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="toggleExpand(user)"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition-all hover:bg-slate-50"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Pets
                                        </button>
                                        <button
                                            @click="confirmDelete(user)"
                                            class="inline-flex items-center gap-1 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition-all hover:bg-red-50"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Expanded Pets Row -->
                            <tr v-if="expandedUserId === user.user_id">
                                <td colspan="6" class="bg-slate-50/80 px-6 py-4">
                                    <!-- Pets loading -->
                                    <div v-if="petsLoading === user.user_id" class="flex items-center gap-3 py-4">
                                        <div class="h-5 w-5 animate-spin rounded-full border-2 border-violet-200 border-t-violet-600"></div>
                                        <span class="text-sm text-slate-500">Loading pets…</span>
                                    </div>

                                    <!-- No pets -->
                                    <div v-else-if="!petsMap[user.user_id]?.length" class="py-4 text-center">
                                        <p class="text-sm text-slate-400">No pets registered for this user.</p>
                                    </div>

                                    <!-- Pet cards -->
                                    <div v-else class="space-y-3">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">
                                            {{ user.name }}'s Pets ({{ petsMap[user.user_id].length }})
                                        </p>
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                            <div
                                                v-for="pet in petsMap[user.user_id]"
                                                :key="pet.pet_id"
                                                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                                            >
                                                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-4.5-2c-.83 0-1.5.67-1.5 1.5S6.67 11 7.5 11 9 10.33 9 9.5 8.33 8 7.5 8zm0 6c-.83 0-1.5.67-1.5 1.5S6.67 17 7.5 17 9 16.33 9 15.5 8.33 14 7.5 14zm9-6c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5S17.33 8 16.5 8zm0 6c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5z"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-semibold text-slate-800 truncate">{{ pet.name }}</p>
                                                    <p class="text-xs text-slate-400">
                                                        {{ pet.species }} · {{ pet.breed }}
                                                    </p>
                                                    <div class="mt-1 flex gap-3 text-xs text-slate-500">
                                                        <span>Age: {{ pet.age ?? '—' }}</span>
                                                        <span>{{ pet.weight ? pet.weight + ' kg' : '' }}</span>
                                                        <span class="capitalize">{{ pet.gender }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Footer -->
                <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-3">
                    <p class="text-xs text-slate-400">
                        Showing {{ filteredUsers.length }} of {{ users.length }} user{{ users.length !== 1 ? 's' : '' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            :show="showDeleteModal"
            title="Delete User"
            :message="`Are you sure you want to delete '${deleteTarget?.name || 'this user'}'? This will permanently remove the user and all their pets, appointments, and records. This action cannot be undone.`"
            confirm-text="Delete User"
            confirm-variant="danger"
            :loading="deleting"
            @confirm="executeDelete"
            @cancel="showDeleteModal = false"
        />
    </ManagerLayout>
</template>
