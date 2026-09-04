<script setup lang="ts">
import { computed } from 'vue';
import BadgeStatus from '@/components/BadgeStatus.vue';

interface Menu {
    id: number;
    nama_menu: string;
    deskripsi: string | null;
    harga: string;
    foto: string | null;
    status: 'tersedia' | 'habis';
    kategori_id: number;
    kategori?: { id: number; nama_kategori: string };
}

const props = defineProps<{ menu: Menu }>();
const emit = defineEmits<{ click: [menu: Menu] }>();

const hargaFormatted = computed(() => `Rp ${Number(props.menu.harga).toLocaleString('id-ID')}`);

const fotoUrl = computed(() => (props.menu.foto ? `/storage/${props.menu.foto}` : null));
</script>

<template>
    <div
        :class="[
            'group relative flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5 transition-all duration-300',
            menu.status === 'habis'
                ? 'opacity-60 pointer-events-none'
                : 'hover:shadow-lg hover:-translate-y-1 cursor-pointer',
        ]"
        @click="menu.status === 'tersedia' && emit('click', menu)"
    >
        <!-- Foto -->
        <div class="relative aspect-[4/3] w-full overflow-hidden bg-brand-surface">
            <img
                v-if="fotoUrl"
                :src="fotoUrl"
                :alt="menu.nama_menu"
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div v-else class="flex h-full w-full items-center justify-center">
                <svg class="h-16 w-16 text-brand-accent/30" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11 9H9V2H7v7H5V2H3v7a4 4 0 0 0 3 3.86V22h2v-9.14A4 4 0 0 0 11 9zM16 6v8h3v8h2V2c-2.76 0-5 2.24-5 4z"/>
                </svg>
            </div>

            <!-- Habis overlay badge -->
            <div
                v-if="menu.status === 'habis'"
                class="absolute inset-0 flex items-center justify-center bg-black/30"
            >
                <BadgeStatus status="habis" variant="menu" />
            </div>
        </div>

        <!-- Konten -->
        <div class="flex flex-1 flex-col gap-1 p-3">
            <p class="line-clamp-1 text-sm font-semibold text-brand-text-dark">{{ menu.nama_menu }}</p>
            <p v-if="menu.deskripsi" class="line-clamp-2 text-xs leading-relaxed text-gray-500">
                {{ menu.deskripsi }}
            </p>
            <div class="mt-auto flex items-center justify-between pt-2">
                <span class="text-sm font-bold text-brand-accent">{{ hargaFormatted }}</span>
                <BadgeStatus v-if="menu.status === 'tersedia'" status="tersedia" variant="menu" />
            </div>
        </div>
    </div>
</template>
