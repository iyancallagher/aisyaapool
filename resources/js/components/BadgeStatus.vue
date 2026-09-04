<script setup lang="ts">
import { computed } from 'vue';

/**
 * BadgeStatus — universal status badge
 *
 * variant='menu'    → status: 'tersedia' | 'habis'
 * variant='pesanan' → status: 'menunggu' | 'diproses' | 'selesai'
 */
const props = defineProps<{
    status: string;
    variant?: 'menu' | 'pesanan';
}>();

const config: Record<string, { label: string; classes: string }> = {
    // Menu statuses
    tersedia: {
        label: 'Tersedia',
        classes: 'bg-brand-secondary/20 text-brand-secondary border border-brand-secondary/40',
    },
    habis: {
        label: 'Habis',
        classes: 'bg-brand-danger/20 text-brand-danger border border-brand-danger/40',
    },
    // Pesanan statuses
    menunggu: {
        label: 'Menunggu',
        classes: 'bg-yellow-100 text-yellow-800 border border-yellow-300',
    },
    diproses: {
        label: 'Diproses',
        classes: 'bg-blue-100 text-blue-800 border border-blue-300',
    },
    selesai: {
        label: 'Selesai',
        classes: 'bg-emerald-100 text-emerald-800 border border-emerald-300',
    },
};

const badge = computed(
    () =>
        config[props.status] ?? {
            label: props.status,
            classes: 'bg-gray-100 text-gray-600 border border-gray-300',
        }
);
</script>

<template>
    <span
        :class="[
            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold tracking-wide',
            badge.classes,
        ]"
    >
        {{ badge.label }}
    </span>
</template>
