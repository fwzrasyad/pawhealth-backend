<script setup>
import { watch } from 'vue';

const props = defineProps({
    show:           { type: Boolean, default: false },
    title:          { type: String,  default: 'Confirm Action' },
    message:        { type: String,  default: 'Are you sure you want to proceed?' },
    confirmText:    { type: String,  default: 'Confirm' },
    cancelText:     { type: String,  default: 'Cancel' },
    confirmVariant: { type: String,  default: 'danger' }, // danger | primary
    loading:        { type: Boolean, default: false },
});

const emit = defineEmits(['confirm', 'cancel']);

// Lock body scroll when modal is open
watch(() => props.show, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
                    @click="!loading && emit('cancel')"
                ></div>

                <!-- Dialog -->
                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-slate-200/50">
                    <!-- Icon -->
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full"
                         :class="confirmVariant === 'danger' ? 'bg-red-100' : 'bg-violet-100'"
                    >
                        <svg
                            v-if="confirmVariant === 'danger'"
                            class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <svg
                            v-else
                            class="h-7 w-7 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                        </svg>
                    </div>

                    <h3 class="text-center text-lg font-semibold text-slate-900">{{ title }}</h3>
                    <p class="mt-2 text-center text-sm text-slate-500 leading-relaxed">{{ message }}</p>

                    <!-- Actions -->
                    <div class="mt-6 flex gap-3">
                        <button
                            :disabled="loading"
                            @click="emit('cancel')"
                            class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition-all hover:bg-slate-50 disabled:opacity-50"
                        >
                            {{ cancelText }}
                        </button>
                        <button
                            :disabled="loading"
                            @click="emit('confirm')"
                            :class="[
                                'flex-1 rounded-xl px-4 py-2.5 text-sm font-medium text-white transition-all disabled:opacity-50',
                                confirmVariant === 'danger'
                                    ? 'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 shadow-lg shadow-red-500/25'
                                    : 'bg-gradient-to-r from-violet-500 to-violet-600 hover:from-violet-600 hover:to-violet-700 shadow-lg shadow-violet-500/25',
                            ]"
                        >
                            <span v-if="loading" class="flex items-center justify-center gap-2">
                                <div class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></div>
                                Processing…
                            </span>
                            <span v-else>{{ confirmText }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.25s ease;
}
.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.25s ease, opacity 0.25s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
.modal-enter-from .relative {
    transform: scale(0.95) translateY(10px);
    opacity: 0;
}
.modal-leave-to .relative {
    transform: scale(0.95) translateY(10px);
    opacity: 0;
}
</style>
