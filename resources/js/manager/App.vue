<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { auth } from './firebase.js';
import { onAuthStateChanged } from 'firebase/auth';

const isReady = ref(false);
const user    = ref(null);

onAuthStateChanged(auth, (u) => {
    user.value    = u;
    isReady.value = true;
});

const isLoggedIn = computed(() => !!user.value);
const route      = useRoute();
const isLoginPage = computed(() => route.name === 'Login');
</script>

<template>
    <!-- Full-page loader while Firebase resolves auth state -->
    <div v-if="!isReady" class="flex h-screen items-center justify-center bg-slate-50">
        <div class="flex flex-col items-center gap-4">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-violet-200 border-t-violet-600"></div>
            <p class="text-sm font-medium text-slate-400">Loading PawHealth…</p>
        </div>
    </div>

    <!-- App shell -->
    <router-view v-else />
</template>
