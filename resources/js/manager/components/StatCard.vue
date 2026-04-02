<script setup>
defineProps({
    title:    { type: String, required: true },
    value:    { type: [String, Number], required: true },
    subtitle: { type: String, default: '' },
    color:    { type: String, default: 'violet' }, // violet | emerald | amber | sky
    loading:  { type: Boolean, default: false },
});

const colorMap = {
    violet:  { bg: 'bg-violet-50',  icon: 'text-violet-600',  ring: 'ring-violet-200',  gradient: 'from-violet-500 to-violet-600'  },
    emerald: { bg: 'bg-emerald-50', icon: 'text-emerald-600', ring: 'ring-emerald-200', gradient: 'from-emerald-500 to-emerald-600' },
    amber:   { bg: 'bg-amber-50',   icon: 'text-amber-600',   ring: 'ring-amber-200',   gradient: 'from-amber-500 to-amber-600'   },
    sky:     { bg: 'bg-sky-50',      icon: 'text-sky-600',      ring: 'ring-sky-200',      gradient: 'from-sky-500 to-sky-600'      },
};
</script>

<template>
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
        <!-- Accent bar -->
        <div
            :class="`bg-gradient-to-r ${colorMap[color].gradient}`"
            class="absolute inset-x-0 top-0 h-1 opacity-80"
        ></div>

        <div class="flex items-start justify-between">
            <div class="space-y-2">
                <p class="text-sm font-medium text-slate-500">{{ title }}</p>

                <!-- Loading skeleton -->
                <div v-if="loading" class="space-y-2">
                    <div class="h-8 w-20 animate-pulse rounded-lg bg-slate-200"></div>
                    <div class="h-4 w-28 animate-pulse rounded bg-slate-100"></div>
                </div>

                <!-- Value -->
                <template v-else>
                    <p class="text-3xl font-bold tracking-tight text-slate-900">{{ value }}</p>
                    <p v-if="subtitle" class="text-xs text-slate-400">{{ subtitle }}</p>
                </template>
            </div>

            <!-- Icon slot -->
            <div
                :class="[colorMap[color].bg, colorMap[color].icon]"
                class="flex h-12 w-12 items-center justify-center rounded-xl ring-1 ring-inset transition-transform duration-300 group-hover:scale-110"
                :style="{ '--tw-ring-color': 'transparent' }"
            >
                <slot name="icon">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </slot>
            </div>
        </div>
    </div>
</template>
