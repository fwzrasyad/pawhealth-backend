<script setup>
defineProps({
    title:    { type: String, required: true },
    value:    { type: [String, Number], required: true },
    subtitle: { type: String, default: '' },
    color:    { type: String, default: 'primary' }, // primary | pending
    loading:  { type: Boolean, default: false },
});
</script>

<template>
    <div class="relative overflow-hidden rounded-[16px] border border-card-border bg-white px-[16px] py-[18px]">
        <div class="flex items-start justify-between">
            <div class="space-y-1">
                <!-- Label -->
                <p class="text-[11px] font-semibold uppercase tracking-[0.06em] text-meta-text">{{ title }}</p>

                <!-- Loading skeleton -->
                <div v-if="loading" class="space-y-2 mt-2">
                    <div class="h-8 w-20 animate-pulse rounded-lg bg-slate-200"></div>
                    <div class="h-3 w-28 animate-pulse rounded bg-slate-100"></div>
                </div>

                <!-- Value and Note -->
                <template v-else>
                    <p :class="['text-[34px] font-bold tracking-[-1px]', color === 'pending' ? 'text-pending-text' : 'text-dark-text']">
                        {{ value }}
                    </p>
                    <p v-if="subtitle" class="text-[12px] text-meta-text">{{ subtitle }}</p>
                </template>
            </div>

            <!-- Icon slot -->
            <div
                :class="['flex h-[30px] w-[30px] shrink-0 items-center justify-center rounded-[8px] text-[15px] text-white', color === 'pending' ? 'bg-pending-text' : 'bg-primary']"
            >
                <slot name="icon">
                    <i class="ti ti-activity"></i>
                </slot>
            </div>
        </div>

        <!-- Accent bar -->
        <div
            :class="['absolute bottom-0 left-0 h-[2px] w-full', color === 'pending' ? 'bg-pending-text' : 'bg-primary']"
        ></div>
    </div>
</template>
