// ─── API Composable ──────────────────────────────────────────────────────────
// Wraps axios with automatic Firebase ID token injection.
// Usage:
//   const api = useApi();
//   const { data, error } = await api.get('/stats');

import { ref } from 'vue';
import axios from 'axios';
import { auth } from '../firebase.js';

const BASE_URL = '/api/manager';

/**
 * Returns the current Firebase user's ID token,
 * or null if not signed in.
 */
async function getToken() {
    const user = auth.currentUser;
    if (!user) return null;
    return await user.getIdToken();
}

/**
 * Creates an axios instance with the Bearer token attached.
 */
async function createClient() {
    const token = await getToken();
    return axios.create({
        baseURL: BASE_URL,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
    });
}

export function useApi() {
    const loading = ref(false);
    const error   = ref(null);

    async function request(method, url, data = null) {
        loading.value = true;
        error.value   = null;

        try {
            const client   = await createClient();
            const response = await client({ method, url, data });
            return { data: response.data, error: null };
        } catch (err) {
            const message =
                err.response?.data?.message ||
                err.response?.data?.error   ||
                err.message ||
                'An unexpected error occurred';
            error.value = message;
            return { data: null, error: message };
        } finally {
            loading.value = false;
        }
    }

    /**
     * Request with an absolute URL (no /api/manager prefix).
     * Useful for endpoints outside the manager namespace.
     */
    async function absRequest(method, url, data = null) {
        loading.value = true;
        error.value   = null;

        try {
            const token = await getToken();
            const client = axios.create({
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...(token ? { Authorization: `Bearer ${token}` } : {}),
                },
            });
            const response = await client({ method, url, data });
            return { data: response.data, error: null };
        } catch (err) {
            const message =
                err.response?.data?.message ||
                err.response?.data?.error   ||
                err.message ||
                'An unexpected error occurred';
            error.value = message;
            return { data: null, error: message };
        } finally {
            loading.value = false;
        }
    }

    /**
     * POST with FormData (multipart/form-data) to an absolute URL.
     * Used for file uploads — does NOT set Content-Type so the browser
     * auto-generates the correct multipart boundary.
     */
    async function absPostFormData(url, formData) {
        loading.value = true;
        error.value   = null;

        try {
            const token = await getToken();
            const response = await axios.post(url, formData, {
                headers: {
                    'Accept': 'application/json',
                    ...(token ? { Authorization: `Bearer ${token}` } : {}),
                    // NOTE: Do NOT set Content-Type manually for FormData
                },
            });
            return { data: response.data, error: null };
        } catch (err) {
            const message =
                err.response?.data?.message ||
                err.response?.data?.error   ||
                err.message ||
                'An unexpected error occurred';
            error.value = message;
            return { data: null, error: message };
        } finally {
            loading.value = false;
        }
    }

    return {
        loading,
        error,
        get:    (url)       => request('GET', url),
        post:   (url, data) => request('POST', url, data),
        put:    (url, data) => request('PUT', url, data),
        del:    (url)       => request('DELETE', url),
        absGet:    (url)       => absRequest('GET', url),
        absPost:   (url, data) => absRequest('POST', url, data),
        absPut:    (url, data) => absRequest('PUT', url, data),
        absDelete: (url)       => absRequest('DELETE', url),
        absPatch:  (url, data) => absRequest('PATCH', url, data),
        absPostFormData,
    };
}
