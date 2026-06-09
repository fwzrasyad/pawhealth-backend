<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { auth } from '../firebase.js';
import { signInWithEmailAndPassword } from 'firebase/auth';

const router   = useRouter();
const email    = ref('');
const password = ref('');
const error    = ref('');
const loading  = ref(false);
const showPass = ref(false);

async function handleLogin() {
    error.value   = '';
    loading.value = true;

    try {
        await signInWithEmailAndPassword(auth, email.value, password.value);
        
        // Fetch local user data to check clinic status
        const { useApi } = await import('../composables/useApi.js');
        const api = useApi();
        const { data } = await api.absPost('/api/auth/sync');

        if (data && data.data) {
            const user = data.data;
            if (user.role === 'manager' && user.clinic?.status === 'pending') {
                router.push('/manager/pending-verification');
                return;
            }
        }
        
        router.push('/manager');
    } catch (err) {
        const messages = {
            'auth/invalid-email':      'Please enter a valid email address.',
            'auth/user-not-found':     'No account found with this email.',
            'auth/wrong-password':     'Incorrect password. Please try again.',
            'auth/invalid-credential': 'Invalid credentials. Please check your email and password.',
            'auth/too-many-requests':  'Too many attempts. Please try again later.',
        };
        error.value = messages[err.code] || err.message || 'Login failed. Please try again.';
    } finally {
        loading.value = false;
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

        <!-- Login Card -->
        <div class="relative w-full max-w-md">
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
                <h2 class="text-[17px] font-bold text-white">Welcome back</h2>
                <p class="mt-1 text-[13px] text-[#9B8CB8]">Sign in to your manager account</p>

                <!-- Error -->
                <div
                    v-if="error"
                    class="mt-4 flex items-center gap-2 rounded-[12px] border border-red-500/20 bg-red-500/10 px-4 py-3 text-[13px] text-red-300"
                >
                    <i class="ti ti-alert-circle text-[16px] shrink-0"></i>
                    {{ error }}
                </div>

                <form @submit.prevent="handleLogin" class="mt-6 space-y-5">
                    <!-- Email -->
                    <div>
                        <label for="login-email" class="block text-[13px] font-semibold text-[#C4B5FD]">Email</label>
                        <input
                            id="login-email"
                            v-model="email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="manager@pawhealth.com"
                            class="mt-1.5 w-full rounded-[10px] border border-white/10 bg-white px-4 py-[10px] text-[13px] text-dark-text placeholder-[#9B8CB8] outline-none transition-all focus:border-primary focus:ring-1 focus:ring-primary"
                        />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="login-password" class="block text-[13px] font-semibold text-[#C4B5FD]">Password</label>
                        <div class="relative mt-1.5">
                            <input
                                id="login-password"
                                v-model="password"
                                :type="showPass ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
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

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full rounded-[10px] bg-primary px-4 py-[11px] text-[13px] font-semibold text-white transition-all hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]"
                    >
                        <span v-if="loading" class="flex items-center justify-center gap-2">
                            <div class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></div>
                            Signing in…
                        </span>
                        <span v-else>Sign In</span>
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-[11px] text-[#5B4B8A]">
                PawHealth &copy; {{ new Date().getFullYear() }} — Manager Portal
            </p>
            <p class="mt-4 text-center text-[13px] text-[#9B8CB8]">
                Don't have an account? 
                <router-link to="/manager/register" class="text-[#C4B5FD] font-semibold hover:text-white transition-colors">Register your clinic</router-link>
            </p>
        </div>
    </div>
</template>
