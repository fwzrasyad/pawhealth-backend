<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { auth } from '../firebase.js';
import { signOut } from 'firebase/auth';

const router  = useRouter();
const route   = useRoute();
const collapsed = ref(false);

const user = computed(() => auth.currentUser);

const navItems = [
    {
        name: 'Dashboard',
        route: '/manager',
        icon: 'dashboard',
    },
    {
        name: 'Veterinarians',
        route: '/manager/veterinarians',
        icon: 'veterinarian',
    },
    {
        name: 'Users & Pets',
        route: '/manager/users',
        icon: 'users',
    },
    {
        name: 'Appointments',
        route: '/manager/appointments',
        icon: 'appointments',
    },
];

function isActive(path) {
    if (path === '/manager') return route.path === '/manager';
    return route.path.startsWith(path);
}

async function handleLogout() {
    await signOut(auth);
    router.push('/manager/login');
}
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-slate-50" style="font-family: 'Inter', sans-serif;">
        <!-- ── Sidebar ────────────────────────────────────────────────────── -->
        <aside
            :class="collapsed ? 'w-[72px]' : 'w-64'"
            class="relative flex flex-col bg-gradient-to-b from-slate-900 to-slate-800 text-white transition-all duration-300 ease-in-out"
        >
            <!-- Brand -->
            <div class="flex h-16 items-center gap-3 px-5 border-b border-white/10">
                <!-- Paw icon -->
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-600 shadow-lg shadow-violet-600/30">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-4.5-2c-.83 0-1.5.67-1.5 1.5S6.67 11 7.5 11 9 10.33 9 9.5 8.33 8 7.5 8zm0 6c-.83 0-1.5.67-1.5 1.5S6.67 17 7.5 17 9 16.33 9 15.5 8.33 14 7.5 14zm9-6c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5S17.33 8 16.5 8zm0 6c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zM12 4c-.83 0-1.5.67-1.5 1.5S11.17 7 12 7s1.5-.67 1.5-1.5S12.83 4 12 4z"/>
                    </svg>
                </div>
                <transition name="fade">
                    <span v-if="!collapsed" class="text-base font-bold tracking-tight whitespace-nowrap">
                        Paw<span class="text-violet-400">Health</span>
                    </span>
                </transition>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-1 px-3 py-4 overflow-y-auto">
                <router-link
                    v-for="item in navItems"
                    :key="item.name"
                    :to="item.route"
                    :class="[
                        'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200',
                        isActive(item.route)
                            ? 'bg-violet-600/20 text-violet-300 shadow-sm'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white',
                    ]"
                >
                    <!-- Dashboard Icon -->
                    <svg v-if="item.icon === 'dashboard'" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    <!-- Vet Icon -->
                    <svg v-if="item.icon === 'veterinarian'" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <!-- Users Icon -->
                    <svg v-if="item.icon === 'users'" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <!-- Appointments Icon -->
                    <svg v-if="item.icon === 'appointments'" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                    </svg>
                    <transition name="fade">
                        <span v-if="!collapsed" class="whitespace-nowrap">{{ item.name }}</span>
                    </transition>
                </router-link>
            </nav>

            <!-- Collapse toggle -->
            <button
                @click="collapsed = !collapsed"
                class="flex h-12 items-center justify-center border-t border-white/10 text-slate-500 hover:text-white transition-colors"
            >
                <svg
                    :class="collapsed ? 'rotate-180' : ''"
                    class="h-5 w-5 transition-transform duration-300"
                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>
        </aside>

        <!-- ── Main Content ───────────────────────────────────────────────── -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-6 shadow-sm">
                <div>
                    <h1 class="text-lg font-semibold text-slate-800">
                        {{ route.name }}
                    </h1>
                    <p class="text-xs text-slate-400">System Manager Portal</p>
                </div>

                <div class="flex items-center gap-4">
                    <!-- User avatar -->
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold shadow-md">
                            {{ user?.email?.charAt(0)?.toUpperCase() || 'M' }}
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-sm font-medium text-slate-700">{{ user?.email || 'Manager' }}</p>
                            <p class="text-xs text-slate-400">System Manager</p>
                        </div>
                    </div>

                    <!-- Logout -->
                    <button
                        @click="handleLogout"
                        class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-500 transition-all hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        Logout
                    </button>
                </div>
            </header>

            <!-- Page Content (scrollable) -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
