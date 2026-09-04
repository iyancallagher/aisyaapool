<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import {
    ClipboardList, Clock, CheckCircle2, Loader2, ChevronDown,
    Bell, RefreshCw, Wifi, WifiOff,
} from '@lucide/vue';
import { dashboard } from '@/routes';
import * as DashboardController from '@/actions/App/Http/Controllers/DashboardController';

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem,
    DropdownMenuTrigger, DropdownMenuSeparator, DropdownMenuLabel,
} from '@/components/ui/dropdown-menu';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Dashboard', href: dashboard() }] },
});

interface Menu { id: number; nama_menu: string; harga: string; }
interface DetailPesanan {
    id: number; pesanan_id: number; menu_id: number;
    jumlah: number; subtotal: string; menu?: Menu;
}
interface Pesanan {
    id: number; nama_pelanggan: string; nomor_meja: string;
    status_pesanan: 'menunggu' | 'diproses' | 'selesai';
    total_harga: string; detail_pesanan: DetailPesanan[]; created_at: string;
}

const props = defineProps<{ pesanan: Pesanan[] }>();

// ── Real-time polling ─────────────────────────────────────────────────────
const POLL_INTERVAL = 5000; // ms
let pollTimer: ReturnType<typeof setInterval> | null = null;
const isPolling  = ref(true);
const lastPollAt = ref<Date>(new Date());
const isRefreshing = ref(false);

// Snapshot untuk deteksi perubahan
const prevSnapshot = ref<Map<number, string>>(new Map());

function buildSnapshot(list: Pesanan[]): Map<number, string> {
    return new Map(list.map((p) => [p.id, p.status_pesanan]));
}

function diffAndNotify(prev: Map<number, string>, next: Pesanan[]) {
    const nextMap = buildSnapshot(next);

    // Pesanan BARU
    for (const p of next) {
        if (!prev.has(p.id)) {
            toast('🛎️ Pesanan Baru Masuk!', {
                description: `${p.nama_pelanggan} — Meja ${p.nomor_meja} — ${formatHarga(p.total_harga)}`,
                duration: 6000,
                action: { label: 'Lihat', onClick: () => document.getElementById(`row-${p.id}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' }) },
            });
            // Juga coba browser notification
            sendBrowserNotification(`Pesanan Baru dari ${p.nama_pelanggan}`, `Meja ${p.nomor_meja} — ${formatHarga(p.total_harga)}`);
        }
    }

    // Perubahan STATUS
    for (const p of next) {
        const oldStatus = prev.get(p.id);
        if (oldStatus && oldStatus !== p.status_pesanan) {
            const statusLabel: Record<string, string> = {
                menunggu: 'Menunggu',
                diproses: 'Diproses',
                selesai:  'Selesai',
            };
            toast.info(`📋 Status Diperbarui`, {
                description: `${p.nama_pelanggan}: ${statusLabel[oldStatus]} → ${statusLabel[p.status_pesanan]}`,
                duration: 4000,
            });
        }
    }
}

function poll() {
    if (isRefreshing.value) return;
    isRefreshing.value = true;
    router.reload({
        only: ['pesanan'],
        onSuccess: () => {
            isRefreshing.value = false;
            lastPollAt.value   = new Date();
        },
        onError: () => { isRefreshing.value = false; },
    });
}

// Watch prop changes dari server → deteksi perbedaan
watch(
    () => props.pesanan,
    (next) => {
        diffAndNotify(prevSnapshot.value, next);
        prevSnapshot.value = buildSnapshot(next);
    },
    { deep: false }
);

function startPolling() {
    if (pollTimer) return;
    pollTimer = setInterval(poll, POLL_INTERVAL);
    isPolling.value = true;
}
function stopPolling() {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    isPolling.value = false;
}
function togglePolling() {
    isPolling.value ? stopPolling() : startPolling();
}
function manualRefresh() {
    lastPollAt.value = new Date();
    poll();
}

onMounted(() => {
    prevSnapshot.value = buildSnapshot(props.pesanan);
    requestBrowserNotificationPermission();
    startPolling();
});
onUnmounted(() => stopPolling());

// ── Browser Notification API ──────────────────────────────────────────────
function requestBrowserNotificationPermission() {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
}
function sendBrowserNotification(title: string, body: string) {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, { body, icon: '/storage/logo.png' });
    }
}

// ── Stats ─────────────────────────────────────────────────────────────────
const jumlahMenunggu = computed(() => props.pesanan.filter((p) => p.status_pesanan === 'menunggu').length);
const jumlahDiproses = computed(() => props.pesanan.filter((p) => p.status_pesanan === 'diproses').length);
const jumlahSelesai  = computed(() => props.pesanan.filter((p) => p.status_pesanan === 'selesai').length);

const statusOptions: { value: Pesanan['status_pesanan']; label: string }[] = [
    { value: 'menunggu', label: 'Menunggu' },
    { value: 'diproses', label: 'Diproses' },
    { value: 'selesai',  label: 'Selesai'  },
];

function changeStatus(pesanan: Pesanan, newStatus: Pesanan['status_pesanan']) {
    router.patch(
        DashboardController.updateStatus({ pesanan: pesanan.id }).url,
        { status_pesanan: newStatus },
        { preserveScroll: true }
    );
}

function formatHarga(val: string) {
    return `Rp ${Number(val).toLocaleString('id-ID')}`;
}
function formatDate(val: string) {
    return new Date(val).toLocaleString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}
function ringkasanItem(detail: DetailPesanan[]) {
    return detail.map((d) => `${d.menu?.nama_menu ?? 'Menu'} ×${d.jumlah}`).join(', ');
}
function lastPollLabel(date: Date) {
    const diff = Math.round((Date.now() - date.getTime()) / 1000);
    if (diff < 5)  return 'baru saja';
    if (diff < 60) return `${diff} dtk lalu`;
    return `${Math.round(diff / 60)} mnt lalu`;
}
</script>

<template>
    <Head title="Dashboard — Aisya Poolside & Brew" />

    <div class="flex flex-col gap-6 p-4 sm:p-6">
        <!-- ── Page Title + Real-time controls ───────────────────────── -->
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
                <p class="text-muted-foreground text-sm">Pantau pesanan secara real-time.</p>
            </div>

            <!-- Status bar + controls -->
            <div class="flex items-center gap-2">
                <!-- Live indicator -->
                <div class="flex items-center gap-1.5 rounded-full border bg-background px-3 py-1.5 text-xs shadow-sm">
                    <span
                        :class="[
                            'h-2 w-2 rounded-full',
                            isPolling ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'
                        ]"
                    />
                    <span class="text-muted-foreground">
                        {{ isPolling ? `Live · ${lastPollLabel(lastPollAt)}` : 'Dijeda' }}
                    </span>
                </div>

                <!-- Manual refresh -->
                <Button variant="outline" size="icon" class="h-8 w-8" title="Refresh sekarang" @click="manualRefresh">
                    <RefreshCw :class="['h-3.5 w-3.5', isRefreshing && 'animate-spin']" />
                </Button>

                <!-- Toggle polling -->
                <Button
                    :variant="isPolling ? 'default' : 'outline'"
                    size="icon"
                    class="h-8 w-8"
                    :title="isPolling ? 'Jeda auto-refresh' : 'Aktifkan auto-refresh'"
                    @click="togglePolling"
                >
                    <Wifi v-if="isPolling" class="h-3.5 w-3.5" />
                    <WifiOff v-else class="h-3.5 w-3.5" />
                </Button>
            </div>
        </div>

        <!-- ── Stats Cards ────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Menunggu</CardTitle>
                    <ClipboardList class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-yellow-600">{{ jumlahMenunggu }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">Pesanan belum dikonfirmasi</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Diproses</CardTitle>
                    <Loader2 class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-blue-600">{{ jumlahDiproses }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">Sedang disiapkan</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Selesai</CardTitle>
                    <CheckCircle2 class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-emerald-600">{{ jumlahSelesai }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">Pesanan telah selesai</p>
                </CardContent>
            </Card>
        </div>

        <!-- ── Pesanan Table ──────────────────────────────────────────── -->
        <Card>
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle>Daftar Pesanan</CardTitle>
                        <CardDescription>
                            {{ pesanan.length }} pesanan total — auto-refresh setiap 5 detik
                        </CardDescription>
                    </div>
                    <Badge v-if="jumlahMenunggu > 0" class="bg-yellow-100 text-yellow-800 border border-yellow-300 animate-pulse">
                        <Bell class="mr-1 h-3 w-3" />
                        {{ jumlahMenunggu }} menunggu
                    </Badge>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <!-- Empty -->
                <div v-if="pesanan.length === 0" class="flex flex-col items-center gap-3 py-20 text-center">
                    <ClipboardList class="h-12 w-12 text-muted-foreground opacity-40" />
                    <p class="text-sm text-muted-foreground">Belum ada pesanan masuk</p>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/40">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">Pelanggan</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground sm:table-cell">Item</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground md:table-cell">Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-muted-foreground">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="p in pesanan"
                                :id="`row-${p.id}`"
                                :key="p.id"
                                :class="[
                                    'transition-colors hover:bg-muted/30',
                                    p.status_pesanan === 'menunggu' ? 'border-l-2 border-l-yellow-400' : ''
                                ]"
                            >
                                <td class="px-4 py-3 text-xs text-muted-foreground">#{{ p.id }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium">{{ p.nama_pelanggan }}</p>
                                    <p class="text-xs text-muted-foreground">Meja {{ p.nomor_meja }}</p>
                                </td>
                                <td class="hidden px-4 py-3 sm:table-cell">
                                    <p class="line-clamp-2 max-w-xs text-xs text-muted-foreground">
                                        {{ ringkasanItem(p.detail_pesanan) }}
                                    </p>
                                </td>
                                <td class="hidden px-4 py-3 text-xs text-muted-foreground md:table-cell">
                                    {{ formatDate(p.created_at) }}
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ formatHarga(p.total_harga) }}</td>
                                <td class="px-4 py-3">
                                    <Badge
                                        variant="outline"
                                        :class="[
                                            'rounded-full text-xs font-semibold border',
                                            p.status_pesanan === 'menunggu' ? 'bg-yellow-100 text-yellow-800 border-yellow-300' :
                                            p.status_pesanan === 'diproses' ? 'bg-blue-100 text-blue-800 border-blue-300' :
                                            'bg-emerald-100 text-emerald-800 border-emerald-300'
                                        ]"
                                    >
                                        {{ p.status_pesanan === 'menunggu' ? 'Menunggu' : p.status_pesanan === 'diproses' ? 'Diproses' : 'Selesai' }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <button class="inline-flex items-center gap-1 rounded-lg border border-input bg-background px-3 py-1.5 text-xs font-medium shadow-sm transition hover:bg-accent hover:text-accent-foreground">
                                                Ubah Status
                                                <ChevronDown class="h-3.5 w-3.5" />
                                            </button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-36">
                                            <DropdownMenuLabel class="text-xs text-muted-foreground">Pilih Status</DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                v-for="opt in statusOptions"
                                                :key="opt.value"
                                                :class="p.status_pesanan === opt.value ? 'font-semibold bg-accent' : ''"
                                                @click="changeStatus(p, opt.value)"
                                            >
                                                {{ opt.label }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
