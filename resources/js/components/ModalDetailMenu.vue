<script setup lang="ts">
import { ref, computed } from 'vue';
import { X, Minus, Plus } from '@lucide/vue';

interface Menu {
    id: number;
    nama_menu: string;
    deskripsi: string | null;
    harga: string;
    foto: string | null;
    status: 'tersedia' | 'habis';
}

const props = defineProps<{ menu: Menu | null }>();
const emit = defineEmits<{
    close: [];
    addToCart: [payload: { menu: Menu; jumlah: number }];
}>();

const jumlah = ref(1);

const hargaFormatted = computed(() =>
    props.menu ? `Rp ${Number(props.menu.harga).toLocaleString('id-ID')}` : ''
);
const subtotalFormatted = computed(() =>
    props.menu ? `Rp ${(Number(props.menu.harga) * jumlah.value).toLocaleString('id-ID')}` : ''
);
const fotoUrl = computed(() =>
    props.menu?.foto ? `/storage/${props.menu.foto}` : null
);

function decrement() {
    if (jumlah.value > 1) jumlah.value--;
}
function increment() {
    jumlah.value++;
}
function handleAdd() {
    if (!props.menu) return;
    emit('addToCart', { menu: props.menu, jumlah: jumlah.value });
    jumlah.value = 1;
    emit('close');
}
</script>

<template>
    <!-- Backdrop -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="menu"
                class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm"
                @click="emit('close')"
            />
        </Transition>

        <!-- Modal Panel -->
        <Transition
            enter-active-class="transition-all duration-300"
            enter-from-class="opacity-0 scale-95 translate-y-4"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-4"
        >
            <div
                v-if="menu"
                class="fixed inset-x-4 bottom-4 top-auto z-50 mx-auto max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl sm:inset-auto sm:left-1/2 sm:top-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2"
            >
                <!-- Close button -->
                <button
                    class="absolute right-3 top-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-black/30 text-white transition hover:bg-black/50"
                    @click="emit('close')"
                >
                    <X class="h-4 w-4" />
                </button>

                <!-- Foto -->
                <div class="relative aspect-[16/9] w-full overflow-hidden bg-brand-surface">
                    <img
                        v-if="fotoUrl"
                        :src="fotoUrl"
                        :alt="menu.nama_menu"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center">
                        <svg class="h-20 w-20 text-brand-accent/30" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11 9H9V2H7v7H5V2H3v7a4 4 0 0 0 3 3.86V22h2v-9.14A4 4 0 0 0 11 9zM16 6v8h3v8h2V2c-2.76 0-5 2.24-5 4z"/>
                        </svg>
                    </div>
                </div>

                <!-- Konten -->
                <div class="flex flex-col gap-3 p-5">
                    <div>
                        <h2 class="font-display text-xl font-semibold text-brand-text-dark">
                            {{ menu.nama_menu }}
                        </h2>
                        <p class="mt-1 text-sm font-bold text-brand-accent">{{ hargaFormatted }}</p>
                    </div>

                    <p v-if="menu.deskripsi" class="text-sm leading-relaxed text-gray-600">
                        {{ menu.deskripsi }}
                    </p>

                    <!-- Stepper -->
                    <div class="flex items-center justify-between rounded-xl bg-brand-surface px-4 py-3">
                        <span class="text-sm font-medium text-brand-text-dark">Jumlah</span>
                        <div class="flex items-center gap-3">
                            <button
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-primary text-brand-text-light transition hover:bg-brand-accent disabled:opacity-40"
                                :disabled="jumlah <= 1"
                                @click="decrement"
                            >
                                <Minus class="h-4 w-4" />
                            </button>
                            <span class="w-6 text-center text-base font-bold text-brand-text-dark">{{ jumlah }}</span>
                            <button
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-primary text-brand-text-light transition hover:bg-brand-accent"
                                @click="increment"
                            >
                                <Plus class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Tambah -->
                    <button
                        class="flex w-full items-center justify-between rounded-xl bg-brand-primary px-5 py-3 text-brand-text-light transition hover:bg-brand-accent hover:text-brand-text-dark active:scale-[0.98]"
                        @click="handleAdd"
                    >
                        <span class="font-semibold">Tambah ke Pesanan</span>
                        <span class="text-sm font-bold">{{ subtotalFormatted }}</span>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
