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
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-violet-950 p-4" style="font-family: 'Inter', sans-serif;">
        <!-- Background decoration -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-96 w-96 rounded-full bg-violet-600/10 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-indigo-600/10 blur-3xl"></div>
        </div>

        <!-- Login Card -->
        <div class="relative w-full max-w-md">
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
                <h2 class="text-lg font-semibold text-white">Welcome back</h2>
                <p class="mt-1 text-sm text-slate-400">Sign in to your manager account</p>

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

                <form @submit.prevent="handleLogin" class="mt-6 space-y-5">
                    <!-- Email -->
                    <div>
                        <label for="login-email" class="block text-sm font-medium text-slate-300">Email</label>
                        <input
                            id="login-email"
                            v-model="email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="manager@pawhealth.com"
                            class="mt-1.5 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none ring-1 ring-transparent transition-all focus:border-violet-500/50 focus:bg-white/10 focus:ring-violet-500/20"
                        />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="login-password" class="block text-sm font-medium text-slate-300">Password</label>
                        <div class="relative mt-1.5">
                            <input
                                id="login-password"
                                v-model="password"
                                :type="showPass ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
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

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full rounded-xl bg-gradient-to-r from-violet-500 to-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-500/25 transition-all hover:from-violet-600 hover:to-indigo-700 hover:shadow-xl hover:shadow-violet-500/30 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]"
                    >
                        <span v-if="loading" class="flex items-center justify-center gap-2">
                            <div class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></div>
                            Signing in…
                        </span>
                        <span v-else>Sign In</span>
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-slate-600">
                PawHealth &copy; {{ new Date().getFullYear() }} — Manager Portal
            </p>
        </div>
    </div>
</template>
