<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, Trash2, ShoppingBag } from '@lucide/vue';
import * as PesananController from '@/actions/App/Http/Controllers/PesananController';

interface Menu {
    id: number;
    nama_menu: string;
    harga: string;
    foto: string | null;
}
interface CartItem {
    menu: Menu;
    jumlah: number;
}

interface FormState {
    nama_pelanggan: string;
    nomor_meja: string;
    processing: boolean;
    errors: Record<string, string>;
}

const props = defineProps<{
    open: boolean;
    cart: CartItem[];
}>();

const emit = defineEmits<{
    close: [];
    removeItem: [menuId: number];
    clearCart: [];
}>();

const form = defineModel<FormState>('form', {
    default: () => ({ nama_pelanggan: '', nomor_meja: '', processing: false, errors: {} }),
});

const totalHarga = computed(() =>
    props.cart.reduce((acc, item) => acc + Number(item.menu.harga) * item.jumlah, 0)
);
const totalFormatted = computed(() => `Rp ${totalHarga.value.toLocaleString('id-ID')}`);

const subtotal = (item: CartItem) =>
    `Rp ${(Number(item.menu.harga) * item.jumlah).toLocaleString('id-ID')}`;

function submitPesanan() {
    if (!form.value.nama_pelanggan.trim() || !form.value.nomor_meja.trim()) return;

    form.value.processing = true;
    form.value.errors = {};

    router.post(
        PesananController.store().url,
        {
            nama_pelanggan: form.value.nama_pelanggan,
            nomor_meja: form.value.nomor_meja,
            items: props.cart.map((item) => ({
                menu_id: item.menu.id,
                jumlah: item.jumlah,
                harga: item.menu.harga,
            })),
        },
        {
            onSuccess: () => {
                form.value.processing = false;
                form.value.nama_pelanggan = '';
                form.value.nomor_meja = '';
                emit('clearCart');
                emit('close');
            },
            onError: (errors) => {
                form.value.processing = false;
                form.value.errors = errors;
            },
        }
    );
}
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm"
                @click="emit('close')"
            />
        </Transition>

        <!-- Drawer Panel -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div
                v-if="open"
                class="fixed inset-x-0 bottom-0 z-50 flex max-h-[90dvh] flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl"
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <ShoppingBag class="h-5 w-5 text-brand-primary" />
                        <h3 class="font-display text-lg font-semibold text-brand-text-dark">Pesanan Saya</h3>
                    </div>
                    <button
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200"
                        @click="emit('close')"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Scrollable content -->
                <div class="flex-1 overflow-y-auto">
                    <!-- Empty state -->
                    <div v-if="cart.length === 0" class="flex flex-col items-center justify-center gap-3 px-6 py-12 text-center">
                        <ShoppingBag class="h-12 w-12 text-gray-300" />
                        <p class="text-sm text-gray-500">Belum ada item di pesanan kamu</p>
                    </div>

                    <!-- Cart Items -->
                    <ul v-else class="divide-y divide-gray-50 px-5">
                        <li
                            v-for="item in cart"
                            :key="item.menu.id"
                            class="flex items-center gap-3 py-3"
                        >
                            <!-- Foto mini -->
                            <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-brand-surface">
                                <img
                                    v-if="item.menu.foto"
                                    :src="`/storage/${item.menu.foto}`"
                                    :alt="item.menu.nama_menu"
                                    loading="lazy"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-brand-text-dark">{{ item.menu.nama_menu }}</p>
                                <p class="text-xs text-brand-accent">{{ subtotal(item) }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-brand-surface px-2 py-0.5 text-xs font-semibold text-brand-text-dark">
                                ×{{ item.jumlah }}
                            </span>
                            <button
                                class="shrink-0 rounded-full p-1.5 text-brand-danger hover:bg-brand-danger/10"
                                @click="emit('removeItem', item.menu.id)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </li>
                    </ul>

                    <!-- Form Pesanan -->
                    <div v-if="cart.length > 0" class="space-y-4 border-t border-gray-100 bg-brand-surface/50 px-5 py-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Detail Pemesan</p>

                        <div class="space-y-3">
                            <div>
                                <label for="nama-pelanggan" class="mb-1 block text-sm font-medium text-brand-text-dark">
                                    Nama Kamu
                                </label>
                                <input
                                    id="nama-pelanggan"
                                    v-model="form.nama_pelanggan"
                                    type="text"
                                    placeholder="Masukkan nama kamu..."
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-brand-text-dark placeholder:text-gray-400 focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20"
                                />
                                <p v-if="form.errors.nama_pelanggan" class="mt-1 text-xs text-brand-danger">
                                    {{ form.errors.nama_pelanggan }}
                                </p>
                            </div>
                            <div>
                                <label for="nomor-meja" class="mb-1 block text-sm font-medium text-brand-text-dark">
                                    Nomor Meja
                                </label>
                                <input
                                    id="nomor-meja"
                                    v-model="form.nomor_meja"
                                    type="text"
                                    placeholder="Contoh: Meja 5, Pool Area 2..."
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-brand-text-dark placeholder:text-gray-400 focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20"
                                />
                                <p v-if="form.errors.nomor_meja" class="mt-1 text-xs text-brand-danger">
                                    {{ form.errors.nomor_meja }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer: Total + Submit -->
                <div v-if="cart.length > 0" class="border-t border-gray-100 bg-white px-5 pb-safe pt-4">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-600">Total Pesanan</span>
                        <span class="text-base font-bold text-brand-primary">{{ totalFormatted }}</span>
                    </div>
                    <button
                        :disabled="form.processing || !form.nama_pelanggan.trim() || !form.nomor_meja.trim()"
                        class="w-full rounded-xl bg-brand-primary py-3.5 text-sm font-semibold text-brand-text-light transition hover:bg-brand-accent hover:text-brand-text-dark disabled:cursor-not-allowed disabled:opacity-50 active:scale-[0.98]"
                        @click="submitPesanan"
                    >
                        <span v-if="form.processing">Mengirim...</span>
                        <span v-else>Kirim Pesanan</span>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
