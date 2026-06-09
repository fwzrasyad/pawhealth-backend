<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../composables/useApi.js';
import { auth } from '../firebase.js';
import { signInWithEmailAndPassword } from 'firebase/auth';

const router   = useRouter();
const api      = useApi();
const name     = ref('');
const email    = ref('');
const address  = ref('');
const city     = ref('');
const state    = ref('');
const phone    = ref('');
const password = ref('');
const confirmPassword = ref('');
const licenseFile     = ref(null);
const licenseFileName = ref('');
const error    = ref('');
const success  = ref(false);
const loading  = ref(false);
const showPass = ref(false);

function handleFileChange(event) {
    const file = event.target.files[0];
    if (file) {
        licenseFile.value     = file;
        licenseFileName.value = file.name;
    }
}

function removeFile() {
    licenseFile.value     = null;
    licenseFileName.value = '';
    const input = document.getElementById('register-license');
    if (input) input.value = '';
}

async function handleRegister() {
    error.value   = '';
    
    if (password.value !== confirmPassword.value) {
        error.value = "Passwords do not match.";
        return;
    }

    if (!licenseFile.value) {
        error.value = "Please upload a clinic license or business registration document.";
        return;
    }
    
    loading.value = true;

    const formData = new FormData();
    formData.append('name', name.value);
    formData.append('email', email.value);
    formData.append('address', address.value);
    formData.append('city', city.value);
    formData.append('state', state.value);
    formData.append('phone', phone.value);
    formData.append('password', password.value);
    formData.append('license_file', licenseFile.value);

    const { error: apiError } = await api.absPostFormData('/api/manager/register', formData);

    if (apiError) {
        error.value = apiError;
        loading.value = false;
    } else {
        success.value = true;
        try {
            await signInWithEmailAndPassword(auth, email.value, password.value);
            const { data } = await api.absPost('/api/auth/sync');
            
            setTimeout(() => {
                if (data && data.data && data.data.clinic?.status === 'pending') {
                    router.push('/manager/pending-verification');
                } else {
                    router.push('/manager');
                }
            }, 2000);
        } catch (err) {
            setTimeout(() => {
                router.push('/manager/login');
            }, 2000);
        }
    }
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-sidebar-bg p-4 font-sans">
        <!-- Background decoration -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-96 w-96 rounded-full bg-primary/10 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-primary-dark/10 blur-3xl"></div>
        </div>

        <!-- Register Card -->
        <div class="relative w-full max-w-md my-8">
            <!-- Brand -->
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-[16px] bg-primary shadow-lg shadow-primary/30">
                    <img src="/pawhealth_logo.png" alt="PawHealth Logo" class="h-9 w-9 object-contain" style="filter: brightness(0) invert(1);" />
                </div>
                <h1 class="text-[24px] font-bold text-white tracking-[-0.4px]">Paw<span class="text-[#C4B5FD]">Health</span></h1>
                <p class="mt-1 text-[13px] text-[#9B8CB8]">System Manager Portal</p>
            </div>

            <!-- Card -->
            <div class="rounded-[16px] border border-white/10 bg-white/5 p-8 backdrop-blur-xl">
                <h2 class="text-[17px] font-bold text-white">Partner Registration</h2>
                <p class="mt-1 text-[13px] text-[#9B8CB8]">Register your clinic to join the platform</p>

                <!-- Success -->
                <div
                    v-if="success"
                    class="mt-4 flex items-center gap-2 rounded-[12px] border border-green-500/20 bg-green-500/10 px-4 py-3 text-[13px] text-green-300"
                >
                    <i class="ti ti-circle-check text-[18px] shrink-0"></i>
                    Registration successful! Redirecting...
                </div>

                <!-- Error -->
                <div
                    v-if="error"
                    class="mt-4 flex items-center gap-2 rounded-[12px] border border-red-500/20 bg-red-500/10 px-4 py-3 text-[13px] text-red-300"
                >
                    <i class="ti ti-alert-circle text-[16px] shrink-0"></i>
                    {{ error }}
                </div>

                <form v-if="!success" @submit.prevent="handleRegister" class="mt-6 space-y-5">
                    <!-- Name -->
                    <div>
                        <label for="register-name" class="block text-[13px] font-semibold text-[#C4B5FD]">Clinic Name</label>
                        <input
                            id="register-name"
                            v-model="name"
                            type="text"
                            required
                            placeholder="e.g., Happy Paws Clinic"
                            class="mt-1.5 w-full rounded-[10px] border border-white/10 bg-white px-4 py-[10px] text-[13px] text-dark-text placeholder-[#9B8CB8] outline-none transition-all focus:border-primary focus:ring-1 focus:ring-primary"
                        />
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="register-email" class="block text-[13px] font-semibold text-[#C4B5FD]">Email Address</label>
                        <input
                            id="register-email"
                            v-model="email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="manager@pawhealth.com"
                            class="mt-1.5 w-full rounded-[10px] border border-white/10 bg-white px-4 py-[10px] text-[13px] text-dark-text placeholder-[#9B8CB8] outline-none transition-all focus:border-primary focus:ring-1 focus:ring-primary"
                        />
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="register-address" class="block text-[13px] font-semibold text-[#C4B5FD]">Clinic Address</label>
                        <input
                            id="register-address"
                            v-model="address"
                            type="text"
                            required
                            placeholder="123 Vet Street"
                            class="mt-1.5 w-full rounded-[10px] border border-white/10 bg-white px-4 py-[10px] text-[13px] text-dark-text placeholder-[#9B8CB8] outline-none transition-all focus:border-primary focus:ring-1 focus:ring-primary"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- City -->
                        <div>
                            <label for="register-city" class="block text-[13px] font-semibold text-[#C4B5FD]">City</label>
                            <input
                                id="register-city"
                                v-model="city"
                                type="text"
                                required
                                placeholder="e.g., Georgetown"
                                class="mt-1.5 w-full rounded-[10px] border border-white/10 bg-white px-4 py-[10px] text-[13px] text-dark-text placeholder-[#9B8CB8] outline-none transition-all focus:border-primary focus:ring-1 focus:ring-primary"
                            />
                        </div>

                        <!-- State -->
                        <div>
                            <label for="register-state" class="block text-[13px] font-semibold text-[#C4B5FD]">State</label>
                            <input
                                id="register-state"
                                v-model="state"
                                type="text"
                                required
                                placeholder="e.g., Penang"
                                class="mt-1.5 w-full rounded-[10px] border border-white/10 bg-white px-4 py-[10px] text-[13px] text-dark-text placeholder-[#9B8CB8] outline-none transition-all focus:border-primary focus:ring-1 focus:ring-primary"
                            />
                        </div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="register-phone" class="block text-[13px] font-semibold text-[#C4B5FD]">Phone Number (Optional)</label>
                        <input
                            id="register-phone"
                            v-model="phone"
                            type="tel"
                            placeholder="+60 12-345 6789"
                            class="mt-1.5 w-full rounded-[10px] border border-white/10 bg-white px-4 py-[10px] text-[13px] text-dark-text placeholder-[#9B8CB8] outline-none transition-all focus:border-primary focus:ring-1 focus:ring-primary"
                        />
                    </div>

                    <!-- License File Upload -->
                    <div>
                        <label for="register-license" class="block text-[13px] font-semibold text-[#C4B5FD]">
                            Clinic License / Business Registration
                            <span class="text-red-400 ml-0.5">*</span>
                        </label>
                        <p class="mt-0.5 text-[11px] text-[#5B4B8A]">Upload your license or registration document (PDF, JPG, or PNG, max 2MB)</p>
                        
                        <div class="mt-2">
                            <!-- Hidden file input -->
                            <input
                                id="register-license"
                                type="file"
                                accept=".pdf,.jpg,.jpeg,.png"
                                @change="handleFileChange"
                                class="hidden"
                            />

                            <!-- Custom upload button -->
                            <div v-if="!licenseFileName">
                                <label
                                    for="register-license"
                                    class="group flex cursor-pointer items-center justify-center gap-3 rounded-[12px] border-2 border-dashed border-white/15 bg-white/[0.02] px-4 py-5 transition-all hover:border-primary/40 hover:bg-white/5"
                                >
                                    <div class="flex h-10 w-10 items-center justify-center rounded-[10px] bg-primary/10 text-[#C4B5FD] transition-colors group-hover:bg-primary/20">
                                        <i class="ti ti-cloud-upload text-[20px]"></i>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-[#C4B5FD]">Click to upload document</p>
                                        <p class="text-[11px] text-[#5B4B8A]">.pdf, .jpg, .png — max 2MB</p>
                                    </div>
                                </label>
                            </div>

                            <!-- File preview -->
                            <div v-else class="flex items-center gap-3 rounded-[12px] border border-primary/20 bg-primary/5 px-4 py-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-[10px] bg-primary/10 text-[#C4B5FD]">
                                    <i class="ti ti-file-text text-[18px]"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[13px] font-semibold text-white truncate">{{ licenseFileName }}</p>
                                    <p class="text-[11px] text-[#5B4B8A]">Ready to upload</p>
                                </div>
                                <button
                                    type="button"
                                    @click="removeFile"
                                    class="flex h-7 w-7 items-center justify-center rounded-[8px] text-[#5B4B8A] hover:bg-red-500/10 hover:text-red-400 transition-colors"
                                >
                                    <i class="ti ti-x text-[14px]"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="register-password" class="block text-[13px] font-semibold text-[#C4B5FD]">Password</label>
                        <div class="relative mt-1.5">
                            <input
                                id="register-password"
                                v-model="password"
                                :type="showPass ? 'text' : 'password'"
                                required
                                minlength="6"
                                placeholder="Min 6 characters"
                                class="w-full rounded-[10px] border border-white/10 bg-white px-4 py-[10px] pr-12 text-[13px] text-dark-text placeholder-[#9B8CB8] outline-none transition-all focus:border-primary focus:ring-1 focus:ring-primary"
                            />
                            <button
                                type="button"
                                @click="showPass = !showPass"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#9B8CB8] hover:text-primary transition-colors"
                            >
                                <svg v-if="!showPass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="register-password-confirm" class="block text-[13px] font-semibold text-[#C4B5FD]">Confirm Password</label>
                        <div class="relative mt-1.5">
                            <input
                                id="register-password-confirm"
                                v-model="confirmPassword"
                                :type="showPass ? 'text' : 'password'"
                                required
                                minlength="6"
                                placeholder="••••••••"
                                class="w-full rounded-[10px] border border-white/10 bg-white px-4 py-[10px] pr-12 text-[13px] text-dark-text placeholder-[#9B8CB8] outline-none transition-all focus:border-primary focus:ring-1 focus:ring-primary"
                            />
                        </div>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full rounded-[10px] bg-primary px-4 py-[11px] text-[13px] font-semibold text-white transition-all hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]"
                    >
                        <span v-if="loading" class="flex items-center justify-center gap-2">
                            <div class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></div>
                            Registering…
                        </span>
                        <span v-else>Register Clinic</span>
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-[11px] text-[#5B4B8A]">
                PawHealth &copy; {{ new Date().getFullYear() }} — Manager Portal
            </p>
            <p class="mt-4 text-center text-[13px] text-[#9B8CB8]">
                Already registered? 
                <router-link to="/manager/login" class="text-[#C4B5FD] font-semibold hover:text-white transition-colors">Sign in here</router-link>
            </p>
        </div>
    </div>
</template>
