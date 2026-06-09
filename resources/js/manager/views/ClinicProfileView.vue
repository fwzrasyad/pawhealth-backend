<script setup>
import { ref, onMounted } from 'vue';
import ManagerLayout from '../layouts/ManagerLayout.vue';
import { useApi } from '../composables/useApi.js';

const api = useApi();
const loading = ref(true);
const saving = ref(false);

const form = ref({
    name: '',
    address: '',
    city: '',
    state: '',
    phone: '',
    description: '',
    image_url: '',
    latitude: null,
    longitude: null,
    google_maps_url: '',
});

const imageFile = ref(null);
const imagePreview = ref(null);

const toast = ref({ show: false, message: '', type: 'success' });
let toastTimer = null;

function showToast(message, type = 'success') {
    if (toastTimer) clearTimeout(toastTimer);
    toast.value = { show: true, message, type };
    toastTimer = setTimeout(() => {
        toast.value.show = false;
    }, 4000);
}

onMounted(async () => {
    loading.value = true;
    const { data } = await api.get('/clinic');
    if (data?.data) {
        form.value = {
            name: data.data.name || '',
            address: data.data.address || '',
            city: data.data.city || '',
            state: data.data.state || '',
            phone: data.data.phone || '',
            description: data.data.description || '',
            image_url: data.data.image_url || '',
            latitude: data.data.latitude || null,
            longitude: data.data.longitude || null,
            google_maps_url: data.data.google_maps_url || '',
        };
    }
    loading.value = false;

    // Initialize Google Places Autocomplete (Modern)
    const checkGoogle = setInterval(() => {
        if (window.google && window.google.maps && window.google.maps.places) {
            clearInterval(checkGoogle);
            initAutocomplete();
        }
    }, 500);
});

const addressContainer = ref(null);
const autocompleteReady = ref(false);

function initAutocomplete() {
    if (!addressContainer.value) return;
    
    // Create the modern web component required for new API keys
    const autocompleteElement = new window.google.maps.places.PlaceAutocompleteElement();

    // Restrict search to Malaysia using the new Places API property
    autocompleteElement.includedRegionCodes = ['my'];

    // Override the dark mode default and style to match Tailwind inputs
    autocompleteElement.style.setProperty('color-scheme', 'light');
    autocompleteElement.style.setProperty('--gmp-background-color', '#ffffff');
    autocompleteElement.style.setProperty('--gmp-color', '#1E1B2E');
    autocompleteElement.style.setProperty('--gmp-font-family', 'Figtree, sans-serif');
    autocompleteElement.style.setProperty('--gmp-border-radius', '10px');
    autocompleteElement.style.setProperty('--gmp-border-color', '#EDE8F8');
    autocompleteElement.style.setProperty('--gmp-box-shadow', 'none');
    autocompleteElement.style.setProperty('--gmp-font-size', '13px');
    autocompleteElement.style.width = '100%';
    autocompleteElement.style.backgroundColor = '#ffffff';

    autocompleteElement.addEventListener('gmp-placeselect', async (e) => {
        const place = e.place;
        if (!place) return;

        // Fetch the necessary fields from the modern Places API
        await place.fetchFields({
            fields: ['displayName', 'formattedAddress', 'location', 'googleMapsURI', 'addressComponents']
        });

        form.value.latitude = place.location.lat();
        form.value.longitude = place.location.lng();
        form.value.google_maps_url = place.googleMapsURI || '';

        // Extract city, state, and street details
        let streetNumber = '';
        let route = '';
        let premise = '';
        let sublocality = '';

        if (place.addressComponents) {
            for (const component of place.addressComponents) {
                const types = component.types;
                if (types.includes('locality') || types.includes('administrative_area_level_2')) {
                    form.value.city = component.longText;
                } else if (types.includes('administrative_area_level_1')) {
                    form.value.state = component.longText;
                } else if (types.includes('street_number')) {
                    streetNumber = component.longText;
                } else if (types.includes('route')) {
                    route = component.longText;
                } else if (types.includes('premise')) {
                    premise = component.longText;
                } else if (types.includes('sublocality_level_1') || types.includes('sublocality')) {
                    sublocality = component.longText;
                }
            }
        }

        // Build a short street address
        const streetParts = [];
        if (premise) streetParts.push(premise);
        if (streetNumber && route) {
            streetParts.push(`${streetNumber} ${route}`);
        } else if (route) {
            streetParts.push(route);
        }
        if (sublocality) streetParts.push(sublocality);
        
        let streetAddress = streetParts.join(', ');
        
        // Fallback if we couldn't extract street details
        if (!streetAddress) {
            streetAddress = place.displayName || place.formattedAddress.split(',')[0];
        }

        form.value.address = streetAddress;
    });

    autocompleteReady.value = true;
    addressContainer.value.innerHTML = '';
    addressContainer.value.appendChild(autocompleteElement);
}

function handleImageChange(event) {
    const file = event.target.files[0];
    if (file) {
        imageFile.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function removeImage() {
    imageFile.value = null;
    imagePreview.value = null;
    const input = document.getElementById('clinic-image');
    if (input) input.value = '';
}

async function saveProfile() {
    saving.value = true;
    
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('address', form.value.address);
    formData.append('city', form.value.city);
    formData.append('state', form.value.state);
    formData.append('phone', form.value.phone);
    if (form.value.description) {
        formData.append('description', form.value.description);
    }
    if (form.value.latitude !== null) formData.append('latitude', form.value.latitude);
    if (form.value.longitude !== null) formData.append('longitude', form.value.longitude);
    if (form.value.google_maps_url) formData.append('google_maps_url', form.value.google_maps_url);
    
    if (imageFile.value) {
        formData.append('image', imageFile.value);
    }

    const { data, error } = await api.absPostFormData('/api/manager/clinic', formData);

    if (error) {
        showToast(error, 'error');
    } else {
        if (data?.data) {
            form.value.image_url = data.data.image_url;
            imageFile.value = null;
            imagePreview.value = null;
        }
        showToast('Clinic profile updated successfully.');
    }
    
    saving.value = false;
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

            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-[24px] font-bold text-dark-text tracking-[-0.4px]">Clinic Profile</h2>
                    <p class="text-[13px] font-normal text-meta-text mt-1">Manage your clinic's public information and branding.</p>
                </div>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-20">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-chip-bg border-t-primary"></div>
            </div>

            <!-- Form -->
            <form v-else @submit.prevent="saveProfile" class="space-y-8 divide-y divide-slate-100">
                
                <!-- Basic Info -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
                    <div class="px-4 sm:px-0">
                        <h2 class="text-[15px] font-bold text-dark-text">Profile Image</h2>
                        <p class="mt-1 text-[12px] text-meta-text">This will be displayed to users in the app.</p>
                    </div>

                    <div class="bg-white border border-card-border rounded-[16px] md:col-span-2">
                        <div class="px-4 py-6 sm:p-8">
                            <div class="flex items-center gap-x-6">
                                <div class="relative h-24 w-24 overflow-hidden rounded-full bg-chip-bg ring-4 ring-white shadow-md">
                                    <img v-if="imagePreview || form.image_url" :src="imagePreview || form.image_url" class="h-full w-full object-cover" alt="Clinic Profile" />
                                    <svg v-else class="h-full w-full text-slate-300" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                                    </svg>
                                </div>
                                <div>
                                    <input type="file" id="clinic-image" accept="image/*" class="hidden" @change="handleImageChange" />
                                    <div class="flex items-center gap-3">
                                        <label for="clinic-image" class="cursor-pointer rounded-[10px] bg-white px-3 py-2 text-[13px] font-semibold text-dark-text border border-card-border hover:bg-surface transition-colors">
                                            Change photo
                                        </label>
                                        <button v-if="imageFile" type="button" @click="removeImage" class="text-sm font-medium text-red-600 hover:text-red-500">
                                            Remove
                                        </button>
                                    </div>
                                    <p class="mt-2 text-[11px] text-meta-text">JPG, PNG, WebP up to 5MB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-x-8 gap-y-8 pt-8 md:grid-cols-3">
                    <div class="px-4 sm:px-0">
                        <h2 class="text-[15px] font-bold text-dark-text">Clinic Details</h2>
                        <p class="mt-1 text-[12px] text-meta-text">Update your clinic's public contact information.</p>
                    </div>

                    <div class="bg-white border border-card-border rounded-[16px] md:col-span-2">
                        <div class="px-4 py-6 sm:p-8">
                            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                <div class="sm:col-span-6">
                                    <label for="name" class="block text-[13px] font-semibold text-dark-text mb-1">Clinic Name</label>
                                    <div class="mt-2">
                                        <input type="text" id="name" v-model="form.name" required class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                                    </div>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="phone" class="block text-[13px] font-semibold text-dark-text mb-1">Phone Number</label>
                                    <div class="mt-2">
                                        <input type="text" id="phone" v-model="form.phone" required class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                                    </div>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="address" class="block text-[13px] font-semibold text-dark-text mb-1">Address</label>
                                    <div class="mt-2" ref="addressContainer">
                                        <input v-if="!autocompleteReady" type="text" id="address" v-model="form.address" required class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" placeholder="Loading map search..." disabled />
                                    </div>
                                    <p v-if="form.google_maps_url" class="mt-2 text-[12px] text-completed-text font-semibold">
                                        ✓ Location captured. <a :href="form.google_maps_url" target="_blank" class="underline hover:opacity-80">View on Maps</a>
                                    </p>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="city" class="block text-[13px] font-semibold text-dark-text mb-1">City</label>
                                    <div class="mt-2">
                                        <input type="text" id="city" v-model="form.city" required class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="state" class="block text-[13px] font-semibold text-dark-text mb-1">State</label>
                                    <div class="mt-2">
                                        <input type="text" id="state" v-model="form.state" required class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                                    </div>
                                </div>

                                <div class="col-span-full">
                                    <label for="description" class="block text-[13px] font-semibold text-dark-text mb-1">Description</label>
                                    <div class="mt-2">
                                        <textarea id="description" v-model="form.description" rows="4" class="block w-full rounded-[10px] border border-card-border py-[9px] px-3 text-[13px] text-dark-text focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" placeholder="A brief description of your clinic services..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-x-6 border-t border-card-border px-4 py-4 sm:px-8">
                            <button type="submit" :disabled="saving" class="inline-flex justify-center rounded-[10px] bg-primary px-4 py-[9px] text-[13px] font-semibold text-white hover:bg-primary-dark transition-all disabled:opacity-50 min-w-[100px]">
                                <div v-if="saving" class="h-5 w-5 animate-spin rounded-full border-2 border-white/30 border-t-white"></div>
                                <span v-else>Save Changes</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </ManagerLayout>
</template>
