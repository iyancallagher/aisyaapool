<script setup lang="ts">
interface Kategori {
    id: number;
    nama_kategori: string;
}

const props = defineProps<{
    kategori: Kategori[];
    modelValue: number | null; // selected kategori_id, null = Semua
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
}>();
</script>

<template>
    <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none snap-x">
        <!-- Tab Semua -->
        <button
            :class="[
                'shrink-0 snap-start rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-200',
                modelValue === null
                    ? 'bg-brand-primary text-brand-text-light shadow-sm'
                    : 'bg-white text-brand-text-dark ring-1 ring-black/10 hover:ring-brand-accent/50',
            ]"
            @click="emit('update:modelValue', null)"
        >
            Semua
        </button>

        <!-- Tab per Kategori -->
        <button
            v-for="kat in kategori"
            :key="kat.id"
            :class="[
                'shrink-0 snap-start rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-200',
                modelValue === kat.id
                    ? 'bg-brand-primary text-brand-text-light shadow-sm'
                    : 'bg-white text-brand-text-dark ring-1 ring-black/10 hover:ring-brand-accent/50',
            ]"
            @click="emit('update:modelValue', kat.id)"
        >
            {{ kat.nama_kategori }}
        </button>
    </div>
</template>

<style scoped>
.scrollbar-none {
    scrollbar-width: none;
}
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
</style>
