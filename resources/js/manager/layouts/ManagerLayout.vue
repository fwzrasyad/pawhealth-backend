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
    { name: 'Dashboard', route: '/manager', icon: 'ti-layout-dashboard' },
    { name: 'Veterinarians', route: '/manager/veterinarians', icon: 'ti-stethoscope' },
    { name: 'Users & Pets', route: '/manager/users', icon: 'ti-users' },
    { name: 'Appointments', route: '/manager/appointments', icon: 'ti-calendar-event' },
    { name: 'Clinic Profile', route: '/manager/clinic', icon: 'ti-building-hospital' },
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
    <div class="flex h-screen overflow-hidden bg-surface font-sans">
        <!-- ── Sidebar ────────────────────────────────────────────────────── -->
        <aside
            :class="collapsed ? 'w-[72px]' : 'w-[228px]'"
            class="relative flex flex-col bg-sidebar-bg transition-all duration-300 ease-in-out"
        >
            <!-- Brand -->
            <div class="flex h-16 items-center gap-3 px-5 border-b border-primary/20 shrink-0">
                <!-- Paw icon -->
                <div class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-[10px] bg-primary">
                    <img src="/pawhealth_logo.png" alt="Logo" class="h-5 w-5 object-contain" style="filter: brightness(0) invert(1);" />
                </div>
                <transition name="fade">
                    <span v-if="!collapsed" class="text-[17px] font-bold tracking-[-0.3px] text-white whitespace-nowrap">
                        PawHealth
                    </span>
                </transition>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-1.5 px-[14px] py-[18px] overflow-y-auto">
                <!-- Section Label (Example if needed, otherwise omitted as prompt mentioned section labels style but didn't list any specific sections) -->
                <!-- <p v-if="!collapsed" class="px-3 pb-2 text-[10px] font-semibold text-muted-text uppercase tracking-[0.1em]">Menu</p> -->
                <router-link
                    v-for="item in navItems"
                    :key="item.name"
                    :to="item.route"
                    :class="[
                        'group flex items-center gap-3 rounded-[10px] px-[12px] py-[9px] text-[13.5px] transition-all duration-200',
                        isActive(item.route)
                            ? 'bg-primary text-white font-semibold'
                            : 'text-nav-inactive hover:bg-primary/15 hover:text-white',
                    ]"
                >
                    <i :class="['ti text-[20px] shrink-0', item.icon]"></i>
                    <transition name="fade">
                        <span v-if="!collapsed" class="whitespace-nowrap">{{ item.name }}</span>
                    </transition>
                </router-link>
            </nav>

            <!-- Collapse toggle -->
            <button
                @click="collapsed = !collapsed"
                class="flex h-10 items-center justify-center text-muted-text hover:text-white transition-colors"
            >
                <i :class="['ti text-[18px] transition-transform duration-300', collapsed ? 'rotate-180' : 'ti-chevron-left']">
                    <template v-if="!collapsed"></template>
                </i>
                <i v-if="collapsed" class="ti ti-chevron-right text-[18px]"></i>
            </button>

            <!-- Footer Avatar -->
            <div class="flex items-center gap-3 px-[14px] py-[16px] border-t border-primary/20 shrink-0">
                <div class="flex h-[32px] w-[32px] shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-white">
                    {{ user?.email?.charAt(0)?.toUpperCase() || 'M' }}
                </div>
                <transition name="fade">
                    <div v-if="!collapsed" class="flex flex-1 items-center justify-between overflow-hidden">
                        <div class="flex flex-col truncate pr-2">
                            <span class="truncate text-[11.5px] text-nav-inactive">{{ user?.email || 'manager@pawhealth.com' }}</span>
                            <span class="text-[11px] text-muted-text">Manager</span>
                        </div>
                        <button @click="handleLogout" class="text-muted-text hover:text-white transition-colors shrink-0" title="Logout">
                            <i class="ti ti-logout text-[18px]"></i>
                        </button>
                    </div>
                </transition>
            </div>
        </aside>

        <!-- ── Main Content ───────────────────────────────────────────────── -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Page Content (scrollable) -->
            <main class="flex-1 overflow-y-auto p-[24px]">
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
