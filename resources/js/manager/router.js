// ─── Vue Router ──────────────────────────────────────────────────────────────

import { createRouter, createWebHistory } from 'vue-router';
import { auth } from './firebase.js';
import { onAuthStateChanged } from 'firebase/auth';

import LoginView     from './views/LoginView.vue';
import DashboardView from './views/DashboardView.vue';
import VetListView   from './views/VetListView.vue';
import UserListView  from './views/UserListView.vue';

const routes = [
    {
        path: '/manager/login',
        name: 'Login',
        component: LoginView,
        meta: { requiresAuth: false },
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

router.beforeEach(async (to) => {
    const requiresAuth = to.meta.requiresAuth !== false;
    const currentUser  = await getCurrentUser();

    if (requiresAuth && !currentUser) {
        return { name: 'Login' };
    }
    if (!requiresAuth && currentUser && to.name === 'Login') {
        return { name: 'Dashboard' };
    }
});

export default router;
