// ─── Vue Router ──────────────────────────────────────────────────────────────

import { createRouter, createWebHistory } from 'vue-router';
import { auth } from './firebase.js';
import { onAuthStateChanged } from 'firebase/auth';

import LoginView     from './views/LoginView.vue';
import DashboardView from './views/DashboardView.vue';
import VetListView   from './views/VetListView.vue';
import UserListView       from './views/UserListView.vue';
import AppointmentsView   from './views/AppointmentsView.vue';
import PendingVerificationView from './views/PendingVerificationView.vue';
import RegisterView from './views/RegisterView.vue';
import SuperAdminDashboard from './views/SuperAdminDashboard.vue';
import ClinicProfileView from './views/ClinicProfileView.vue';
import { useApi } from './composables/useApi.js';

const routes = [
    {
        path: '/manager/login',
        name: 'Login',
        component: LoginView,
        meta: { requiresAuth: false },
    },
    {
        path: '/manager/register',
        name: 'Register',
        component: RegisterView,
        meta: { requiresAuth: false },
    },
    {
        path: '/manager/pending-verification',
        name: 'PendingVerification',
        component: PendingVerificationView,
        meta: { requiresAuth: true, allowPending: true },
    },
    {
        path: '/manager',
        name: 'Dashboard',
        component: DashboardView,
        meta: { requiresAuth: true },
    },
    {
        path: '/manager/veterinarians',
        name: 'Veterinarians',
        component: VetListView,
        meta: { requiresAuth: true },
    },
    {
        path: '/manager/users',
        name: 'Users',
        component: UserListView,
        meta: { requiresAuth: true },
    },
    {
        path: '/manager/appointments',
        name: 'Appointments',
        component: AppointmentsView,
        meta: { requiresAuth: true },
    },
    {
        path: '/manager/clinic',
        name: 'ClinicProfile',
        component: ClinicProfileView,
        meta: { requiresAuth: true },
    },
    // ── Super Admin ──
    {
        path: '/super-admin',
        name: 'SuperAdmin',
        component: SuperAdminDashboard,
        meta: { requiresAuth: true, requiresSuperAdmin: true },
    },
    // Redirect unknown manager paths to dashboard
    {
        path: '/manager/:pathMatch(.*)*',
        redirect: '/manager',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// ── Auth Guard ───────────────────────────────────────────────────────────────
// Wait for Firebase to resolve the current user before evaluating guards.
function getCurrentUser() {
    return new Promise((resolve) => {
        const unsubscribe = onAuthStateChanged(auth, (user) => {
            unsubscribe();
            resolve(user);
        });
    });
}

// Cache user data to avoid excessive /auth/sync calls
let cachedUserData = null;

async function fetchUserData(api) {
    const { data } = await api.absPost('/api/auth/sync');
    if (data && data.data) {
        cachedUserData = data.data;
    }
    return cachedUserData;
}

router.beforeEach(async (to) => {
    const requiresAuth = to.meta.requiresAuth !== false;
    const currentUser  = await getCurrentUser();

    if (requiresAuth && !currentUser) {
        return { name: 'Login' };
    }

    if (!requiresAuth && currentUser && (to.name === 'Login' || to.name === 'Register')) {
        return { name: 'Dashboard' };
    }

    // For authenticated routes, fetch user data once per navigation
    if (currentUser && requiresAuth) {
        const api = useApi();
        const user = await fetchUserData(api);

        if (!user) return;

        // Super Admin route guard
        if (to.meta.requiresSuperAdmin) {
            if (user.role !== 'super_admin') {
                // Non-super-admins cannot access this route
                return { name: 'Dashboard' };
            }
            // Super admins can proceed
            return;
        }

        // If super_admin is trying to access manager routes, redirect to their dashboard
        if (user.role === 'super_admin' && to.name !== 'SuperAdmin') {
            return { name: 'SuperAdmin' };
        }

        // Manager quarantine check
        if (user.role === 'manager' && user.clinic?.status === 'pending' && to.name !== 'PendingVerification') {
            return { name: 'PendingVerification' };
        }

        // Block approved managers from accessing pending-verification
        if (to.name === 'PendingVerification') {
            if (user.role !== 'manager' || user.clinic?.status !== 'pending') {
                return { name: 'Dashboard' };
            }
        }
    }
});

export default router;
