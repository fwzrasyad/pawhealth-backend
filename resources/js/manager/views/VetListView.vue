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

// Register Modal State
const showRegisterModal = ref(false);
const registerLoading = ref(false);
const registerError = ref(null);
const registerForm = ref({
    name: '',
    email: '',
    phone_number: '',
    password: '',
    specialties: ''
});

const toastMessage = ref(null);
function showToast(msg) {
    toastMessage.value = msg;
    setTimeout(() => { toastMessage.value = null; }, 3000);
}

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

async function registerVeterinarian() {
    registerLoading.value = true;
    registerError.value = null;

    const { data, error } = await api.post('/veterinarians', registerForm.value);

    if (error) {
        registerError.value = error;
        registerLoading.value = false;
        return;
    }

    // Success
    showRegisterModal.value = false;
    registerForm.value = { name: '', email: '', phone_number: '', password: '', specialties: '' };
    
    if (data?.data) {
        vets.value.unshift(data.data);
    } else {
        await fetchVets();
    }
    
    showToast('Veterinarian registered successfully.');
    registerLoading.value = false;
}

// Edit Modal State
const showEditModal = ref(false);
const editLoading = ref(false);
const editError = ref(null);
const editForm = ref({
    vet_id: null,
    name: '',
    specialties: '',
    consultation_fee: 0,
});

function openEditModal(vet) {
    editForm.value = {
        vet_id: vet.vet_id,
        name: vet.name,
        specialties: (vet.specialties || []).join(', '),
        consultation_fee: vet.consultation_fee || 0,
    };
    showEditModal.value = true;
    editError.value = null;
}

async function updateVeterinarianInfo() {
    editLoading.value = true;
    editError.value = null;

    const { data, error } = await api.put(`/veterinarians/${editForm.value.vet_id}`, {
        name: editForm.value.name,
        specialties: editForm.value.specialties.split(',').map(s => s.trim()),
        consultation_fee: editForm.value.consultation_fee,
    });

    if (error) {
        editError.value = error;
        editLoading.value = false;
        return;
    }

    // Success
    showEditModal.value = false;
    
    // Update local vet
    const index = vets.value.findIndex(v => v.vet_id === editForm.value.vet_id);
    if (index !== -1 && data?.data) {
        vets.value[index].name = data.data.name;
        vets.value[index].specialties = data.data.specialties;
        vets.value[index].consultation_fee = data.data.consultation_fee;
    }
    
    showToast('Veterinarian updated successfully.');
    editLoading.value = false;
}
</script>

<template>
    <ManagerLayout>
        <div class="space-y-[24px]">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-[24px] font-bold text-dark-text tracking-[-0.4px]">Veterinarians</h2>
                    <p class="mt-1 text-[13px] font-normal text-meta-text">Manage and approve veterinarian accounts.</p>
                </div>

                <!-- Summary badges & Action -->
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-[100px] bg-completed-bg px-3 py-1 text-xs font-semibold text-completed-text">
                        <span class="h-1.5 w-1.5 rounded-full bg-completed-text"></span>
                        {{ approvedCount }} Approved
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-[100px] bg-pending-bg px-3 py-1 text-xs font-semibold text-pending-text">
                        <span class="h-1.5 w-1.5 rounded-full bg-pending-text"></span>
                        {{ pendingCount }} Pending
                    </span>
                    <button
                        @click="showRegisterModal = true"
                        class="inline-flex items-center gap-1.5 rounded-[10px] bg-primary px-4 py-2 text-[13px] font-semibold text-white hover:bg-primary-dark transition-all ml-2"
                    >
                        <i class="ti ti-plus text-[16px]"></i>
                        Register New Veterinarian
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative max-w-md">
                <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-meta-text text-[18px]"></i>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by name, email, or specialty…"
                    class="w-full rounded-[10px] border border-card-border bg-white py-[9px] pl-10 pr-4 text-[13px] text-dark-text outline-none transition-all placeholder:text-meta-text focus:border-primary"
                />
            </div>

            <!-- Loading Skeleton -->
            <TableSkeleton v-if="loading" :columns="5" :rows="5" />

            <!-- Data Table -->
            <div v-else class="overflow-hidden rounded-[16px] border border-card-border bg-white">
                <!-- Empty state -->
                <div v-if="filteredVets.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-surface mb-4">
                        <i class="ti ti-stethoscope text-[24px] text-meta-text"></i>
                    </div>
                    <p class="text-[14px] font-semibold text-dark-text">No veterinarians found</p>
                    <p class="mt-1 text-[13px] text-meta-text">{{ search ? 'Try adjusting your search.' : 'Veterinarians will appear here once registered.' }}</p>
                </div>

                <!-- Table -->
                <table v-else class="w-full">
                    <thead>
                        <tr class="border-b border-surface bg-surface/30">
                            <th class="px-6 py-[14px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-meta-text">Veterinarian</th>
                            <th class="px-6 py-[14px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-meta-text">Specialties</th>
                            <th class="px-6 py-[14px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-meta-text">Working Hours</th>
                            <th class="px-6 py-[14px] text-left text-[11px] font-semibold uppercase tracking-[0.06em] text-meta-text">Status</th>
                            <th class="px-6 py-[14px] text-right text-[11px] font-semibold uppercase tracking-[0.06em] text-meta-text">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface">
                        <tr
                            v-for="vet in filteredVets"
                            :key="vet.vet_id"
                            :class="[
                                'transition-colors hover:bg-surface/50',
                                vet.status === 'pending' ? 'bg-pending-bg/30' : ''
                            ]"
                        >
                            <!-- Name & Email -->
                            <td class="px-6 py-[16px]">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-[34px] w-[34px] overflow-hidden items-center justify-center rounded-full bg-chip-bg text-[11.5px] font-bold text-primary-dark">
                                        <img v-if="vet.profile_image_url" :src="vet.profile_image_url" class="h-full w-full object-cover" alt="Vet Profile" />
                                        <span v-else>{{ vet.name?.charAt(0)?.toUpperCase() || '?' }}</span>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-dark-text">{{ vet.name }}</p>
                                        <p class="text-[11.5px] text-meta-text">{{ vet.email || 'No email' }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Specialties -->
                            <td class="px-6 py-[16px]">
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="spec in (vet.specialties || []).slice(0, 3)"
                                        :key="spec"
                                        class="inline-flex rounded-md bg-chip-bg px-2 py-0.5 text-[11px] font-medium text-primary-dark"
                                    >
                                        {{ spec }}
                                    </span>
                                    <span
                                        v-if="(vet.specialties || []).length > 3"
                                        class="inline-flex rounded-md bg-surface px-2 py-0.5 text-[11px] font-medium text-meta-text"
                                    >
                                        +{{ vet.specialties.length - 3 }}
                                    </span>
                                    <span v-if="!(vet.specialties || []).length" class="text-[11.5px] text-meta-text">—</span>
                                </div>
                            </td>

                            <!-- Working Hours & Fee -->
                            <td class="px-6 py-[16px] text-[13px] text-dark-text">
                                <div>{{ vet.working_hours || '—' }}</div>
                                <div class="mt-1 text-[11.5px] font-medium text-meta-text">
                                    Fee: RM {{ Number(vet.consultation_fee || 0).toFixed(2) }}
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-6 py-[16px]">
                                <span 
                                    v-if="vet.status === 'approved'" 
                                    class="rounded-[100px] bg-completed-bg px-2 py-0.5 text-[11px] font-semibold text-completed-text capitalize"
                                >Approved</span>
                                <span 
                                    v-else-if="vet.status === 'pending'" 
                                    class="rounded-[100px] bg-pending-bg px-2 py-0.5 text-[11px] font-semibold text-pending-text capitalize"
                                >Pending</span>
                                <span 
                                    v-else-if="vet.status === 'suspended'" 
                                    class="rounded-[100px] bg-[#FEF2F2] px-2 py-0.5 text-[11px] font-semibold text-[#B91C1C] capitalize"
                                >Suspended</span>
                                <span 
                                    v-else 
                                    class="rounded-[100px] bg-surface px-2 py-0.5 text-[11px] font-semibold text-muted-text capitalize"
                                >{{ vet.status || 'Unknown' }}</span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-[16px] text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="openEditModal(vet)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] bg-surface text-meta-text transition-colors hover:bg-card-border hover:text-dark-text"
                                        title="Edit Veterinarian"
                                    >
                                        <i class="ti ti-edit text-[16px]"></i>
                                    </button>
                                    <button
                                        @click="toggleStatus(vet)"
                                        :disabled="updating === vet.vet_id"
                                        :class="[
                                            'inline-flex items-center justify-center gap-1.5 rounded-[8px] px-3 py-1.5 text-[11.5px] font-semibold transition-all w-[90px]',
                                            vet.status === 'approved'
                                                ? 'bg-[#FEF2F2] text-[#B91C1C] hover:bg-[#FEE2E2]'
                                                : 'bg-chip-bg text-primary-dark hover:bg-card-border',
                                            updating === vet.vet_id ? 'opacity-50 cursor-not-allowed' : '',
                                        ]"
                                    >
                                        <div v-if="updating === vet.vet_id" class="h-3 w-3 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
                                        <template v-else>
                                            {{ vet.status === 'approved' ? 'Suspend' : 'Approve' }}
                                        </template>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Footer -->
                <div class="border-t border-surface bg-surface/30 px-6 py-4">
                    <p class="text-[12px] text-meta-text">
                        Showing {{ filteredVets.length }} of {{ vets.length }} veterinarian{{ vets.length !== 1 ? 's' : '' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-dark-bg/50 backdrop-blur-sm transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-[16px] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <form @submit.prevent="updateVeterinarianInfo">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-chip-bg sm:mx-0 sm:h-10 sm:w-10">
                                        <i class="ti ti-edit text-primary-dark text-[20px]"></i>
                                    </div>
                                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                        <h3 class="text-[16px] font-bold text-dark-text" id="modal-title">Edit Veterinarian</h3>
                                        <div class="mt-4 space-y-4">
                                            <div v-if="editError" class="rounded-md bg-[#FEF2F2] p-3 text-[13px] text-[#B91C1C]">
                                                {{ editError }}
                                            </div>

                                            <div>
                                                <label class="block text-[13px] font-semibold text-dark-text mb-1">Name</label>
                                                <input v-model="editForm.name" type="text" required class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                                            </div>

                                            <div>
                                                <label class="block text-[13px] font-semibold text-dark-text mb-1">Specialties (comma separated)</label>
                                                <input v-model="editForm.specialties" type="text" required class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                                            </div>

                                            <div>
                                                <label class="block text-[13px] font-semibold text-dark-text mb-1">Consultation Fee (RM)</label>
                                                <input v-model.number="editForm.consultation_fee" type="number" step="0.01" min="0" required class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-surface/30 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit" :disabled="editLoading" class="inline-flex w-full justify-center rounded-[10px] bg-primary px-4 py-2 text-[13px] font-semibold text-white hover:bg-primary-dark disabled:opacity-50 sm:ml-3 sm:w-auto">
                                    <div v-if="editLoading" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent mt-0.5"></div>
                                    Save Changes
                                </button>
                                <button type="button" @click="showEditModal = false" :disabled="editLoading" class="mt-3 inline-flex w-full justify-center rounded-[10px] bg-white border border-card-border px-4 py-2 text-[13px] font-semibold text-dark-text hover:bg-surface sm:mt-0 sm:w-auto">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Register Modal -->
        <div v-if="showRegisterModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-dark-bg/50 backdrop-blur-sm transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-[16px] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <form @submit.prevent="registerVeterinarian">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-chip-bg sm:mx-0 sm:h-10 sm:w-10">
                                        <i class="ti ti-user-plus text-primary-dark text-[20px]"></i>
                                    </div>
                                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                        <h3 class="text-[16px] font-bold text-dark-text" id="modal-title">Register Veterinarian</h3>
                                        <div class="mt-4 space-y-4">
                                            <div v-if="registerError" class="rounded-md bg-[#FEF2F2] p-3 text-[13px] text-[#B91C1C]">
                                                {{ registerError }}
                                            </div>

                                            <div>
                                                <label class="block text-[13px] font-semibold text-dark-text mb-1">Name</label>
                                                <input v-model="registerForm.name" type="text" required class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                                            </div>

                                            <div>
                                                <label class="block text-[13px] font-semibold text-dark-text mb-1">Email</label>
                                                <input v-model="registerForm.email" type="email" required class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                                            </div>

                                            <div>
                                                <label class="block text-[13px] font-semibold text-dark-text mb-1">Phone Number</label>
                                                <input v-model="registerForm.phone_number" type="text" required class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                                            </div>

                                            <div>
                                                <label class="block text-[13px] font-semibold text-dark-text mb-1">Temporary Password</label>
                                                <input v-model="registerForm.password" type="password" required minlength="6" class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                                            </div>

                                            <div>
                                                <label class="block text-[13px] font-semibold text-dark-text mb-1">Specialties</label>
                                                <input v-model="registerForm.specialties" type="text" required placeholder="e.g. General Practice, Surgery" class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-surface/30 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit" :disabled="registerLoading" class="inline-flex w-full justify-center rounded-[10px] bg-primary px-4 py-2 text-[13px] font-semibold text-white hover:bg-primary-dark disabled:opacity-50 sm:ml-3 sm:w-auto">
                                    <div v-if="registerLoading" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent mt-0.5"></div>
                                    Register
                                </button>
                                <button type="button" @click="showRegisterModal = false" :disabled="registerLoading" class="mt-3 inline-flex w-full justify-center rounded-[10px] bg-white border border-card-border px-4 py-2 text-[13px] font-semibold text-dark-text hover:bg-surface sm:mt-0 sm:w-auto">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div v-if="toastMessage" class="fixed bottom-4 right-4 z-50">
            <div class="flex items-center gap-2 rounded-[12px] bg-[#15803D] px-4 py-3 text-[13px] font-medium text-white shadow-xl">
                <i class="ti ti-check text-[18px]"></i>
                {{ toastMessage }}
            </div>
        </div>

    </ManagerLayout>
</template>
