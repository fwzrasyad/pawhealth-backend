<script setup>
defineProps({
    columns: { type: Number, default: 5 },
    rows:    { type: Number, default: 6 },
});
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <!-- Header skeleton -->
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <div class="flex gap-4">
                <div
                    v-for="col in columns"
                    :key="'h-' + col"
                    class="h-4 flex-1 animate-pulse rounded bg-slate-200"
                    :style="{ animationDelay: col * 100 + 'ms' }"
                ></div>
            </div>
        </div>

        <!-- Row skeletons -->
        <div
            v-for="row in rows"
            :key="'r-' + row"
            class="flex gap-4 border-b border-slate-50 px-6 py-4 last:border-b-0"
        >
            <div
                v-for="col in columns"
                :key="'c-' + row + '-' + col"
                :class="[
                    'h-4 animate-pulse rounded',
                    col === 1 ? 'w-32 bg-slate-200' : 'flex-1 bg-slate-100',
                ]"
                :style="{ animationDelay: (row * columns + col) * 50 + 'ms' }"
            ></div>
        </div>
    </div>
</template>
