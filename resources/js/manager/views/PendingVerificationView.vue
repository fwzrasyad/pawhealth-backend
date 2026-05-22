<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-violet-950 p-4" style="font-family: 'Inter', sans-serif;">
        <!-- Background decoration -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-96 w-96 rounded-full bg-violet-600/10 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-indigo-600/10 blur-3xl"></div>
        </div>

        <div class="relative w-full max-w-md rounded-2xl border border-white/10 bg-white/5 p-8 text-center shadow-2xl backdrop-blur-xl">
            <!-- Brand -->
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-white">Paw<span class="text-violet-400">Health</span></h1>
            </div>

            <!-- Icon -->
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-orange-500/10 border border-orange-500/20 shadow-[0_0_15px_rgba(249,115,22,0.2)]">
                <svg class="h-10 w-10 text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>

            <!-- Content -->
            <h1 class="mb-4 text-2xl font-bold text-white">Account Pending Verification</h1>
            <p class="mb-8 text-sm leading-relaxed text-slate-300">
                Welcome to PawHealth! Your clinic account has been created successfully. Our trust and safety team is currently verifying your veterinary credentials. You will receive an email once your portal is unlocked and ready to use.
            </p>

            <!-- Actions -->
            <button
                @click="handleLogout"
                class="w-full rounded-xl bg-gradient-to-r from-slate-700 to-slate-800 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/50 transition-all hover:from-slate-600 hover:to-slate-700 hover:shadow-xl hover:shadow-slate-900/60 active:scale-[0.98] border border-white/10"
            >
                Log Out
            </button>
        </div>
    </div>
</template>

<script setup>
import { auth } from '../firebase.js';
import { signOut } from 'firebase/auth';
import { useRouter } from 'vue-router';

const router = useRouter();

async function handleLogout() {
    await signOut(auth);
    router.push('/manager/login');
}
</script>
