<script setup lang="ts">
import { ref, computed, reactive } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { ShoppingCart, X, Plus, Minus, Trash2, CheckCircle2, ArrowLeft, ChevronRight, Search } from '@lucide/vue';
import * as PesananController from '@/actions/App/Http/Controllers/PesananController';

interface Kategori { id: number; nama_kategori: string; }
interface Menu {
    id: number; kategori_id: number; nama_menu: string; deskripsi: string | null;
    harga: string; foto: string | null; status: 'tersedia' | 'habis'; kategori?: Kategori;
}
interface CartItem { menu: Menu; jumlah: number; }

const props = defineProps<{ kategori: Kategori[]; menu: Menu[] }>();

// ── State ──────────────────────────────────────────────────────────────────
const activeKategori = ref<number | null>(null);
const selectedMenu = ref<Menu | null>(null);
const drawerOpen = ref(false);
const showSuccess = ref(false);
const searchQuery = ref('');

const cart = ref<CartItem[]>([]);
const cartForm = reactive({
    nama_pelanggan: '', nomor_meja: '',
    processing: false, errors: {} as Record<string, string>,
});

// ── Filtered menu ──────────────────────────────────────────────────────────
const filteredMenu = computed(() => {
    let list = props.menu;
    if (activeKategori.value !== null) list = list.filter((m) => m.kategori_id === activeKategori.value);
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter((m) => m.nama_menu.toLowerCase().includes(q));
    }
    return list;
});

// ── Cart ───────────────────────────────────────────────────────────────────
const cartCount = computed(() => cart.value.reduce((a, i) => a + i.jumlah, 0));
const cartTotal = computed(() => cart.value.reduce((a, i) => a + Number(i.menu.harga) * i.jumlah, 0));
const cartTotalFormatted = computed(() => `Rp ${cartTotal.value.toLocaleString('id-ID')}`);

function getCartItem(menuId: number) { return cart.value.find((i) => i.menu.id === menuId); }

function addToCart(menu: Menu, jumlah = 1) {
    if (menu.status === 'habis') return;
    const existing = getCartItem(menu.id);
    if (existing) existing.jumlah += jumlah;
    else cart.value.push({ menu, jumlah });
}
function decreaseCart(menu: Menu) {
    const existing = getCartItem(menu.id);
    if (!existing) return;
    if (existing.jumlah <= 1) cart.value = cart.value.filter((i) => i.menu.id !== menu.id);
    else existing.jumlah -= 1;
}
function removeFromCart(menuId: number) { cart.value = cart.value.filter((i) => i.menu.id !== menuId); }

// ── Modal detail ───────────────────────────────────────────────────────────
const modalQty = ref(1);
function openDetail(menu: Menu) {
    if (menu.status === 'habis') return;
    selectedMenu.value = menu;
    modalQty.value = getCartItem(menu.id)?.jumlah ?? 1;
}
function addFromModal() {
    if (!selectedMenu.value) return;
    addToCart(selectedMenu.value, modalQty.value);
    selectedMenu.value = null;
}

// ── Submit ─────────────────────────────────────────────────────────────────
function submitPesanan() {
    if (!cartForm.nama_pelanggan.trim() || !cartForm.nomor_meja.trim()) return;
    cartForm.processing = true;
    cartForm.errors = {};
    router.post(PesananController.store().url, {
        nama_pelanggan: cartForm.nama_pelanggan,
        nomor_meja: cartForm.nomor_meja,
        items: cart.value.map((i) => ({ menu_id: i.menu.id, jumlah: i.jumlah, harga: i.menu.harga })),
    }, {
        onSuccess: () => {
            cartForm.processing = false;
            cartForm.nama_pelanggan = '';
            cartForm.nomor_meja = '';
            cart.value = [];
            drawerOpen.value = false;
            showSuccess.value = true;
            setTimeout(() => showSuccess.value = false, 5000);
        },
        onError: (e) => { cartForm.processing = false; cartForm.errors = e; },
    });
}

function fmt(val: string) { return `Rp ${Number(val).toLocaleString('id-ID')}`; }
</script>

<template>

    <Head title="Menu — Aisya Poolside & Brew" />

    <div class="min-h-screen" style="background-color: #f8f5ef; font-family: 'Instrument Sans', sans-serif;">

        <!-- ══ NAVBAR ══════════════════════════════════════════════════════ -->
        <header class="sticky top-0 z-30 bg-[#0e2b23]/95 backdrop-blur-md shadow-sm">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
                <!-- Logo -->
                <a href="/" class="text-2xl font-bold text-[#c9b27c]">Aisya Poolside & Brew</a>
                <span class="text-xs text-[#c9b27c]/60">Menu & Pemesanan</span>
            </div>
        </header>

        <!-- ══ PAGE HERO ═══════════════════════════════════════════════════ -->
        <div class="bg-[#0e2b23] px-4 pb-6 pt-8 text-center">
            <img src="/storage/logo.png" alt="" class="mx-auto mb-4 h-20 w-auto opacity-90" />
            <h1 class="text-2xl font-bold text-white sm:text-3xl">Menu Kami</h1>
            <p class="mt-1 text-sm text-white/40">Pilih menu favoritmu dan pesan langsung dari sini</p>
        </div>

        <!-- ══ SEARCH + FILTER ════════════════════════════════════════════ -->
        <div class="sticky top-[57px] z-20 bg-white/95 shadow-sm backdrop-blur-md">
            <div class="mx-auto max-w-5xl px-4 py-3">
                <!-- Search -->
                <div class="relative mb-3">
                    <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input v-model="searchQuery" type="search" placeholder="Cari menu..."
                        class="w-full rounded-full border border-gray-200 bg-gray-50 py-2 pl-10 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-[#c9b27c] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c9b27c]/20" />
                </div>

                <!-- Kategori tabs -->
                <div class="flex gap-2 overflow-x-auto pb-0.5 scrollbar-hide">
                    <button :class="[
                        'shrink-0 rounded-full px-4 py-1.5 text-xs font-medium transition',
                        activeKategori === null
                            ? 'bg-[#0e2b23] text-[#c9b27c]'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    ]" @click="activeKategori = null">
                        Semua
                    </button>
                    <button v-for="kat in kategori" :key="kat.id" :class="[
                        'shrink-0 rounded-full px-4 py-1.5 text-xs font-medium transition',
                        activeKategori === kat.id
                            ? 'bg-[#0e2b23] text-[#c9b27c]'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    ]" @click="activeKategori = kat.id">
                        {{ kat.nama_kategori }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ MENU GRID ═══════════════════════════════════════════════════ -->
        <main class="mx-auto max-w-5xl px-4 py-6">
            <p v-if="filteredMenu.length > 0" class="mb-4 text-xs text-gray-400">{{ filteredMenu.length }} menu
                ditemukan</p>

            <!-- Empty -->
            <div v-if="filteredMenu.length === 0"
                class="flex flex-col items-center gap-3 py-24 text-center text-gray-400">
                <svg class="h-14 w-14 opacity-20" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M11 9H9V2H7v7H5V2H3v7a4 4 0 0 0 3 3.86V22h2v-9.14A4 4 0 0 0 11 9zM16 6v8h3v8h2V2c-2.76 0-5 2.24-5 4z" />
                </svg>
                <p class="text-sm">Tidak ada menu ditemukan</p>
            </div>

            <!-- Grid -->
            <div v-else class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                <div v-for="m in filteredMenu" :key="m.id" :class="[
                    'group relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5 transition-all duration-300',
                    m.status === 'tersedia'
                        ? 'cursor-pointer hover:-translate-y-1 hover:shadow-lg'
                        : 'opacity-60'
                ]" @click="openDetail(m)">
                    <!-- Foto -->
                    <div class="relative aspect-square w-full overflow-hidden bg-gray-100">
                        <img v-if="m.foto" :src="`/storage/${m.foto}`" :alt="m.nama_menu" loading="lazy"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                        <div v-else class="flex h-full items-center justify-center bg-[#0e2b23]/5">
                            <svg class="h-12 w-12 text-[#0e2b23]/15" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M11 9H9V2H7v7H5V2H3v7a4 4 0 0 0 3 3.86V22h2v-9.14A4 4 0 0 0 11 9zM16 6v8h3v8h2V2c-2.76 0-5 2.24-5 4z" />
                            </svg>
                        </div>

                        <!-- Habis overlay -->
                        <div v-if="m.status === 'habis'"
                            class="absolute inset-0 flex items-center justify-center bg-black/40">
                            <span
                                class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-gray-700">Habis</span>
                        </div>

                        <!-- Cart qty badge -->
                        <div v-if="getCartItem(m.id)"
                            class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-[#0e2b23] text-xs font-bold text-[#c9b27c] shadow">
                            {{ getCartItem(m.id)?.jumlah }}
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="p-3">
                        <p class="line-clamp-1 text-sm font-semibold text-gray-800">{{ m.nama_menu }}</p>
                        <p class="mt-0.5 text-xs font-bold text-[#c9b27c]">{{ fmt(m.harga) }}</p>

                        <!-- Quick add (tersedia) -->
                        <div v-if="m.status === 'tersedia'" class="mt-2">
                            <div v-if="getCartItem(m.id)"
                                class="flex items-center justify-between rounded-xl bg-[#0e2b23] px-2 py-1" @click.stop>
                                <button class="p-1 text-[#c9b27c]" @click="decreaseCart(m)">
                                    <Minus class="h-3.5 w-3.5" />
                                </button>
                                <span class="text-xs font-bold text-white">{{ getCartItem(m.id)?.jumlah }}</span>
                                <button class="p-1 text-[#c9b27c]" @click="addToCart(m)">
                                    <Plus class="h-3.5 w-3.5" />
                                </button>
                            </div>
                            <button v-else
                                class="w-full rounded-xl bg-[#0e2b23]/8 py-1.5 text-xs font-medium text-[#0e2b23] transition hover:bg-[#0e2b23] hover:text-[#c9b27c]"
                                @click.stop="addToCart(m)">
                                + Tambah
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- ══ MODAL DETAIL ════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="selectedMenu"
                    class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 backdrop-blur-sm sm:items-center"
                    @click.self="selectedMenu = null">
                    <Transition enter-active-class="transition-all duration-300"
                        enter-from-class="translate-y-8 opacity-0 scale-95"
                        enter-to-class="translate-y-0 opacity-100 scale-100" appear>
                        <div
                            class="relative w-full max-w-sm overflow-hidden rounded-t-3xl bg-white shadow-2xl sm:rounded-3xl">
                            <!-- Foto -->
                            <div class="relative aspect-video w-full bg-gray-100">
                                <img v-if="selectedMenu.foto" :src="`/storage/${selectedMenu.foto}`"
                                    :alt="selectedMenu.nama_menu" class="h-full w-full object-cover" />
                                <div v-else class="flex h-full items-center justify-center bg-[#0e2b23]/5" />
                                <button
                                    class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-gray-600 shadow hover:bg-white"
                                    @click="selectedMenu = null">
                                    <X class="h-4 w-4" />
                                </button>
                            </div>

                            <!-- Info -->
                            <div class="p-5">
                                <h3 class="text-xl font-bold text-gray-900">{{ selectedMenu.nama_menu }}</h3>
                                <p class="mt-0.5 text-lg font-bold text-[#c9b27c]">{{ fmt(selectedMenu.harga) }}</p>
                                <p v-if="selectedMenu.deskripsi" class="mt-2 text-sm leading-relaxed text-gray-500">{{
                                    selectedMenu.deskripsi }}</p>

                                <!-- Qty stepper -->
                                <div class="mt-5 flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-3">
                                    <span class="text-sm font-medium text-gray-600">Jumlah</span>
                                    <div class="flex items-center gap-4">
                                        <button :disabled="modalQty <= 1"
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-white shadow transition hover:bg-gray-100 disabled:opacity-30"
                                            @click="modalQty = Math.max(1, modalQty - 1)">
                                            <Minus class="h-3.5 w-3.5" />
                                        </button>
                                        <span class="w-8 text-center text-base font-bold">{{ modalQty }}</span>
                                        <button
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-white shadow transition hover:bg-gray-100"
                                            @click="modalQty += 1">
                                            <Plus class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Subtotal + Add -->
                                <div class="mt-4 flex items-center gap-3">
                                    <div class="flex-1 rounded-xl bg-[#0e2b23]/5 px-4 py-2.5 text-center">
                                        <p class="text-xs text-gray-400">Subtotal</p>
                                        <p class="font-bold text-[#0e2b23]">Rp {{ (Number(selectedMenu.harga) *
                                            modalQty).toLocaleString('id-ID') }}</p>
                                    </div>
                                    <button
                                        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-[#0e2b23] py-3 text-sm font-bold text-[#c9b27c] transition hover:bg-[#0e2b23]/90 active:scale-95"
                                        @click="addFromModal">
                                        <ShoppingCart class="h-4 w-4" />
                                        Tambah ke Keranjang
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- ══ CART DRAWER ════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="drawerOpen" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
                    @click="drawerOpen = false" />
            </Transition>

            <Transition enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-y-full" enter-to-class="translate-y-0"
                leave-active-class="transition-transform duration-250 ease-in" leave-from-class="translate-y-0"
                leave-to-class="translate-y-full">
                <div v-if="drawerOpen"
                    class="fixed inset-x-0 bottom-0 z-50 flex max-h-[90dvh] flex-col overflow-hidden rounded-t-3xl bg-white shadow-2xl">

                    <!-- Handle + Header -->
                    <div class="border-b border-gray-100 px-5 pb-4 pt-3">
                        <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-gray-200" />
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900">Keranjang Pesanan</h3>
                            <button
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200"
                                @click="drawerOpen = false">
                                <X class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="flex-1 overflow-y-auto">
                        <div v-if="cart.length === 0"
                            class="flex flex-col items-center gap-3 py-16 text-center text-gray-400">
                            <ShoppingCart class="h-12 w-12 opacity-30" />
                            <p class="text-sm">Keranjangmu masih kosong</p>
                        </div>

                        <ul v-else class="divide-y divide-gray-50 px-5">
                            <li v-for="item in cart" :key="item.menu.id" class="flex items-center gap-3 py-3">
                                <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl bg-gray-100">
                                    <img v-if="item.menu.foto" :src="`/storage/${item.menu.foto}`"
                                        :alt="item.menu.nama_menu" class="h-full w-full object-cover" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-800">{{ item.menu.nama_menu }}
                                    </p>
                                    <p class="text-xs text-[#c9b27c]">Rp {{ (Number(item.menu.harga) *
                                        item.jumlah).toLocaleString('id-ID') }}</p>
                                </div>
                                <!-- Inline stepper -->
                                <div class="flex items-center gap-2 rounded-xl bg-gray-50 px-2 py-1">
                                    <button class="text-gray-400 hover:text-gray-700" @click="decreaseCart(item.menu)">
                                        <Minus class="h-3.5 w-3.5" />
                                    </button>
                                    <span class="w-5 text-center text-sm font-bold">{{ item.jumlah }}</span>
                                    <button class="text-gray-400 hover:text-gray-700" @click="addToCart(item.menu)">
                                        <Plus class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <button class="text-red-400 hover:text-red-600" @click="removeFromCart(item.menu.id)">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </li>
                        </ul>

                        <!-- Form pemesan -->
                        <div v-if="cart.length > 0" class="space-y-3 border-t border-gray-100 bg-gray-50/50 px-5 py-5">
                            <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Detail Pemesan</p>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Nama Kamu</label>
                                <input v-model="cartForm.nama_pelanggan" type="text" placeholder="Masukkan nama kamu..."
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-[#c9b27c] focus:outline-none focus:ring-2 focus:ring-[#c9b27c]/20" />
                                <p v-if="cartForm.errors.nama_pelanggan" class="mt-1 text-xs text-red-500">{{
                                    cartForm.errors.nama_pelanggan }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Nomor Meja</label>
                                <input v-model="cartForm.nomor_meja" type="text" placeholder="Contoh: Meja 5..."
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-[#c9b27c] focus:outline-none focus:ring-2 focus:ring-[#c9b27c]/20" />
                                <p v-if="cartForm.errors.nomor_meja" class="mt-1 text-xs text-red-500">{{
                                    cartForm.errors.nomor_meja }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div v-if="cart.length > 0" class="border-t border-gray-100 bg-white px-5 pb-6 pt-4">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-sm text-gray-500">Total Pesanan</span>
                            <span class="text-base font-bold text-[#0e2b23]">{{ cartTotalFormatted }}</span>
                        </div>
                        <button
                            :disabled="cartForm.processing || !cartForm.nama_pelanggan.trim() || !cartForm.nomor_meja.trim()"
                            class="w-full rounded-2xl bg-[#0e2b23] py-4 text-sm font-bold text-[#c9b27c] shadow-lg shadow-[#0e2b23]/20 transition hover:bg-[#0e2b23]/90 disabled:opacity-50 active:scale-[0.98]"
                            @click="submitPesanan">
                            {{ cartForm.processing ? 'Mengirim Pesanan...' : 'Kirim Pesanan 🛎️' }}
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ══ FLOATING CART BTN ════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-300"
                enter-from-class="opacity-0 translate-y-4 scale-90" enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition-all duration-200" leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 translate-y-4 scale-90">
                <button v-if="cartCount > 0"
                    class="fixed bottom-6 left-1/2 z-30 -translate-x-1/2 flex items-center gap-3 rounded-full bg-[#0e2b23] px-6 py-3.5 text-sm font-bold text-white shadow-2xl shadow-[#0e2b23]/40 transition hover:bg-[#0e2b23]/90 active:scale-95"
                    @click="drawerOpen = true">
                    <div class="relative">
                        <ShoppingCart class="h-5 w-5 text-[#c9b27c]" />
                        <span
                            class="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-[#c9b27c] text-[9px] font-black text-[#0e2b23]">{{
                            cartCount }}</span>
                    </div>
                    <span>Lihat Pesanan</span>
                    <span class="rounded-full bg-[#c9b27c]/20 px-2.5 py-0.5 text-xs text-[#c9b27c]">{{
                        cartTotalFormatted }}</span>
                    <ChevronRight class="h-4 w-4 text-[#c9b27c]" />
                </button>
            </Transition>
        </Teleport>

        <!-- ══ SUCCESS TOAST ════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-400" enter-from-class="opacity-0 translate-y-4"
                enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-300"
                leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
                <div v-if="showSuccess"
                    class="fixed bottom-24 left-1/2 z-50 w-[90vw] max-w-sm -translate-x-1/2 overflow-hidden rounded-2xl bg-[#0e2b23] shadow-2xl">
                    <div class="flex items-start gap-3 p-4">
                        <CheckCircle2 class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" />
                        <div class="flex-1">
                            <p class="text-sm font-bold text-white">Pesanan diterima! 🎉</p>
                            <p class="mt-0.5 text-xs text-white/50">Kami segera menyiapkan pesananmu. Terima kasih!</p>
                        </div>
                        <button class="text-white/30 hover:text-white/60" @click="showSuccess = false">
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                    <!-- Progress bar -->
                    <div class="h-0.5 bg-[#c9b27c]/20">
                        <div class="h-full bg-[#c9b27c] animate-shrink-width" />
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.scrollbar-hide {
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

@keyframes shrink-width {
    from {
        width: 100%;
    }

    to {
        width: 0%;
    }
}

.animate-shrink-width {
    animation: shrink-width 5s linear forwards;
}
</style>
