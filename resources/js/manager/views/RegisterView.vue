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
    // Reset the file input
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

    // Build FormData for multipart/form-data upload
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
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-violet-950 p-4" style="font-family: 'Inter', sans-serif;">
        <!-- Background decoration -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-96 w-96 rounded-full bg-violet-600/10 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-indigo-600/10 blur-3xl"></div>
        </div>

        <!-- Register Card -->
        <div class="relative w-full max-w-md my-8">
            <!-- Brand -->
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 shadow-2xl shadow-violet-600/30">
                    <svg class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-4.5-2c-.83 0-1.5.67-1.5 1.5S6.67 11 7.5 11 9 10.33 9 9.5 8.33 8 7.5 8zm0 6c-.83 0-1.5.67-1.5 1.5S6.67 17 7.5 17 9 16.33 9 15.5 8.33 14 7.5 14zm9-6c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5S17.33 8 16.5 8zm0 6c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zM12 4c-.83 0-1.5.67-1.5 1.5S11.17 7 12 7s1.5-.67 1.5-1.5S12.83 4 12 4z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Paw<span class="text-violet-400">Health</span></h1>
                <p class="mt-1 text-sm text-slate-400">System Manager Portal</p>
            </div>

            <!-- Card -->
            <div class="rounded-2xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl">
                <h2 class="text-lg font-semibold text-white">Partner Registration</h2>
                <p class="mt-1 text-sm text-slate-400">Register your clinic to join the platform</p>

                <!-- Success -->
                <div
                    v-if="success"
                    class="mt-4 flex items-center gap-2 rounded-xl border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-300"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Registration successful! Redirecting...
                </div>

                <!-- Error -->
                <div
                    v-if="error"
                    class="mt-4 flex items-center gap-2 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-300"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    {{ error }}
                </div>

                <form v-if="!success" @submit.prevent="handleRegister" class="mt-6 space-y-5">
                    <!-- Name -->
                    <div>
                        <label for="register-name" class="block text-sm font-medium text-slate-300">Clinic Name</label>
                        <input
                            id="register-name"
                            v-model="name"
                            type="text"
                            required
                            placeholder="e.g., Happy Paws Clinic"
                            class="mt-1.5 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none ring-1 ring-transparent transition-all focus:border-violet-500/50 focus:bg-white/10 focus:ring-violet-500/20"
                        />
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="register-email" class="block text-sm font-medium text-slate-300">Email Address</label>
                        <input
                            id="register-email"
                            v-model="email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="manager@pawhealth.com"
                            class="mt-1.5 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none ring-1 ring-transparent transition-all focus:border-violet-500/50 focus:bg-white/10 focus:ring-violet-500/20"
                        />
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="register-address" class="block text-sm font-medium text-slate-300">Clinic Address</label>
                        <input
                            id="register-address"
                            v-model="address"
                            type="text"
                            required
                            placeholder="123 Vet Street"
                            class="mt-1.5 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none ring-1 ring-transparent transition-all focus:border-violet-500/50 focus:bg-white/10 focus:ring-violet-500/20"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- City -->
                        <div>
                            <label for="register-city" class="block text-sm font-medium text-slate-300">City</label>
                            <input
                                id="register-city"
                                v-model="city"
                                type="text"
                                required
                                placeholder="e.g., Seattle"
                                class="mt-1.5 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none ring-1 ring-transparent transition-all focus:border-violet-500/50 focus:bg-white/10 focus:ring-violet-500/20"
                            />
                        </div>

                        <!-- State -->
                        <div>
                            <label for="register-state" class="block text-sm font-medium text-slate-300">State / Region</label>
                            <input
                                id="register-state"
                                v-model="state"
                                type="text"
                                required
                                placeholder="e.g., WA"
                                class="mt-1.5 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none ring-1 ring-transparent transition-all focus:border-violet-500/50 focus:bg-white/10 focus:ring-violet-500/20"
                            />
                        </div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="register-phone" class="block text-sm font-medium text-slate-300">Phone Number (Optional)</label>
                        <input
                            id="register-phone"
                            v-model="phone"
                            type="tel"
                            placeholder="+1 (555) 000-0000"
                            class="mt-1.5 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none ring-1 ring-transparent transition-all focus:border-violet-500/50 focus:bg-white/10 focus:ring-violet-500/20"
                        />
                    </div>

                    <!-- License File Upload -->
                    <div>
                        <label for="register-license" class="block text-sm font-medium text-slate-300">
                            Clinic License / Business Registration
                            <span class="text-red-400 ml-0.5">*</span>
                        </label>
                        <p class="mt-0.5 text-xs text-slate-500">Upload your license or registration document (PDF, JPG, or PNG, max 2MB)</p>
                        
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
                                    class="group flex cursor-pointer items-center justify-center gap-3 rounded-xl border-2 border-dashed border-white/15 bg-white/[0.02] px-4 py-5 transition-all hover:border-violet-500/40 hover:bg-white/5"
                                >
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-500/10 text-violet-400 transition-colors group-hover:bg-violet-500/20">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-300">Click to upload document</p>
                                        <p class="text-xs text-slate-500">.pdf, .jpg, .png — max 2MB</p>
                                    </div>
                                </label>
                            </div>

                            <!-- File preview -->
                            <div v-else class="flex items-center gap-3 rounded-xl border border-violet-500/20 bg-violet-500/5 px-4 py-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white truncate">{{ licenseFileName }}</p>
                                    <p class="text-xs text-slate-500">Ready to upload</p>
                                </div>
                                <button
                                    type="button"
                                    @click="removeFile"
                                    class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-500 hover:bg-red-500/10 hover:text-red-400 transition-colors"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="register-password" class="block text-sm font-medium text-slate-300">Password</label>
                        <div class="relative mt-1.5">
                            <input
                                id="register-password"
                                v-model="password"
                                :type="showPass ? 'text' : 'password'"
                                required
                                minlength="6"
                                placeholder="Min 6 characters"
                                class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 pr-12 text-sm text-white placeholder-slate-500 outline-none ring-1 ring-transparent transition-all focus:border-violet-500/50 focus:bg-white/10 focus:ring-violet-500/20"
                            />
                            <button
                                type="button"
                                @click="showPass = !showPass"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors"
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
                        <label for="register-password-confirm" class="block text-sm font-medium text-slate-300">Confirm Password</label>
                        <div class="relative mt-1.5">
                            <input
                                id="register-password-confirm"
                                v-model="confirmPassword"
                                :type="showPass ? 'text' : 'password'"
                                required
                                minlength="6"
                                placeholder="••••••••"
                                class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 pr-12 text-sm text-white placeholder-slate-500 outline-none ring-1 ring-transparent transition-all focus:border-violet-500/50 focus:bg-white/10 focus:ring-violet-500/20"
                            />
                        </div>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full rounded-xl bg-gradient-to-r from-violet-500 to-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-500/25 transition-all hover:from-violet-600 hover:to-indigo-700 hover:shadow-xl hover:shadow-violet-500/30 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]"
                    >
                        <span v-if="loading" class="flex items-center justify-center gap-2">
                            <div class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></div>
                            Registering…
                        </span>
                        <span v-else>Register Clinic</span>
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-slate-600">
                PawHealth &copy; {{ new Date().getFullYear() }} — Manager Portal
            </p>
            <p class="mt-4 text-center text-sm text-slate-400">
                Already registered? 
                <router-link to="/manager/login" class="text-violet-400 font-medium hover:text-violet-300 transition-colors">Sign in here</router-link>
            </p>
        </div>
    </div>
</template>
