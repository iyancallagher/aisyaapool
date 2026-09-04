<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, UtensilsCrossed, Upload, Search } from '@lucide/vue';
import * as AdminMenuController from '@/actions/App/Http/Controllers/Admin/MenuController';
import { dashboard } from '@/routes';

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogClose,
} from '@/components/ui/dialog';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Kelola Menu', href: AdminMenuController.index().url },
        ],
    },
});

interface Kategori { id: number; nama_kategori: string; }
interface Menu {
    id: number; kategori_id: number; nama_menu: string; deskripsi: string | null;
    harga: string; foto: string | null; status: 'tersedia' | 'habis'; kategori?: Kategori;
}

const props = defineProps<{ menu: Menu[]; kategori: Kategori[] }>();

// ── Search ─────────────────────────────────────────────────────────────────
const search = ref('');
const filteredMenu = computed(() => {
    if (!search.value.trim()) return props.menu;
    const q = search.value.toLowerCase();
    return props.menu.filter(
        (m) => m.nama_menu.toLowerCase().includes(q) || m.kategori?.nama_kategori.toLowerCase().includes(q)
    );
});

// ── Modal ──────────────────────────────────────────────────────────────────
const modalOpen   = ref(false);
const modalMode   = ref<'create' | 'edit'>('create');
const editTarget  = ref<Menu | null>(null);

const form = ref({
    nama_menu: '', kategori_id: '' as string | number,
    harga: '', deskripsi: '', status: 'tersedia' as 'tersedia' | 'habis',
    foto: null as File | null,
});
const errors      = ref<Record<string, string>>({});
const processing  = ref(false);
const previewUrl  = ref<string | null>(null);

function openCreate() {
    modalMode.value  = 'create';
    editTarget.value = null;
    form.value = { nama_menu: '', kategori_id: '', harga: '', deskripsi: '', status: 'tersedia', foto: null };
    errors.value = {};
    previewUrl.value = null;
    modalOpen.value  = true;
}
function openEdit(m: Menu) {
    modalMode.value     = 'edit';
    editTarget.value    = m;
    form.value.nama_menu   = m.nama_menu;
    form.value.kategori_id = m.kategori_id;
    form.value.harga       = m.harga;
    form.value.deskripsi   = m.deskripsi ?? '';
    form.value.status      = m.status;
    form.value.foto        = null;
    previewUrl.value       = m.foto ? `/storage/${m.foto}` : null;
    errors.value           = {};
    modalOpen.value        = true;
}
function onFotoChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) { form.value.foto = file; previewUrl.value = URL.createObjectURL(file); }
}
function handleSubmit() {
    processing.value = true;
    errors.value     = {};
    const fd = new FormData();
    fd.append('nama_menu',   form.value.nama_menu);
    fd.append('kategori_id', String(form.value.kategori_id));
    fd.append('harga',       form.value.harga);
    fd.append('deskripsi',   form.value.deskripsi);
    fd.append('status',      form.value.status);
    if (form.value.foto) fd.append('foto', form.value.foto);

    const isEdit = modalMode.value === 'edit' && editTarget.value;
    const url    = isEdit
        ? AdminMenuController.update({ menu: editTarget.value!.id }).url
        : AdminMenuController.store().url;
    if (isEdit) fd.append('_method', 'PATCH');

    router.post(url, fd, {
        forceFormData: true,
        onSuccess: () => { processing.value = false; modalOpen.value = false; },
        onError:   (e) => { processing.value = false; errors.value = e; },
    });
}
function handleDelete(m: Menu) {
    if (!confirm(`Hapus menu "${m.nama_menu}"?`)) return;
    router.delete(AdminMenuController.destroy({ menu: m.id }).url, { preserveScroll: true });
}
function formatHarga(val: string) {
    return `Rp ${Number(val).toLocaleString('id-ID')}`;
}
</script>

<template>
    <Head title="Kelola Menu — Aisya Poolside & Brew" />

    <div class="flex flex-col gap-6 p-4 sm:p-6">
        <!-- ── Header ─────────────────────────────────────────────────── -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Kelola Menu</h1>
                <p class="text-muted-foreground text-sm">{{ menu.length }} menu terdaftar</p>
            </div>
            <Button id="btn-tambah-menu" @click="openCreate">
                <Plus class="mr-2 h-4 w-4" />
                Tambah Menu
            </Button>
        </div>

        <!-- ── Search ─────────────────────────────────────────────────── -->
        <div class="relative max-w-sm">
            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input v-model="search" placeholder="Cari nama menu atau kategori..." class="pl-9" />
        </div>

        <!-- ── Table Card ──────────────────────────────────────────────── -->
        <Card>
            <CardContent class="p-0">
                <!-- Empty -->
                <div v-if="filteredMenu.length === 0" class="flex flex-col items-center gap-3 py-20 text-center">
                    <UtensilsCrossed class="text-muted-foreground h-12 w-12 opacity-40" />
                    <p class="text-muted-foreground text-sm">
                        {{ search ? 'Tidak ada menu yang cocok' : 'Belum ada menu. Klik "Tambah Menu".' }}
                    </p>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/40">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">Foto</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">Nama Menu</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground sm:table-cell">Kategori</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">Harga</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground sm:table-cell">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-muted-foreground">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="m in filteredMenu"
                                :key="m.id"
                                class="transition-colors hover:bg-muted/30"
                            >
                                <!-- Foto -->
                                <td class="px-4 py-3">
                                    <div class="h-12 w-12 overflow-hidden rounded-lg bg-muted">
                                        <img
                                            v-if="m.foto"
                                            :src="`/storage/${m.foto}`"
                                            :alt="m.nama_menu"
                                            loading="lazy"
                                            class="h-full w-full object-cover"
                                        />
                                        <div v-else class="flex h-full items-center justify-center">
                                            <UtensilsCrossed class="h-5 w-5 text-muted-foreground" />
                                        </div>
                                    </div>
                                </td>
                                <!-- Nama -->
                                <td class="px-4 py-3">
                                    <p class="font-medium">{{ m.nama_menu }}</p>
                                    <p v-if="m.deskripsi" class="line-clamp-1 text-xs text-muted-foreground max-w-[200px]">{{ m.deskripsi }}</p>
                                </td>
                                <!-- Kategori -->
                                <td class="hidden px-4 py-3 sm:table-cell">
                                    <Badge variant="secondary" class="text-xs">{{ m.kategori?.nama_kategori ?? '—' }}</Badge>
                                </td>
                                <!-- Harga -->
                                <td class="px-4 py-3 font-semibold">{{ formatHarga(m.harga) }}</td>
                                <!-- Status -->
                                <td class="hidden px-4 py-3 sm:table-cell">
                                    <Badge
                                        :class="m.status === 'tersedia'
                                            ? 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                                            : 'bg-red-100 text-red-800 border border-red-300'"
                                        variant="outline"
                                    >
                                        {{ m.status === 'tersedia' ? 'Tersedia' : 'Habis' }}
                                    </Badge>
                                </td>
                                <!-- Aksi -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button
                                            :id="`btn-edit-menu-${m.id}`"
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8"
                                            @click="openEdit(m)"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            :id="`btn-hapus-menu-${m.id}`"
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-destructive hover:text-destructive"
                                            @click="handleDelete(m)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>

    <!-- ── Dialog Form ────────────────────────────────────────────────── -->
    <Dialog v-model:open="modalOpen">
        <DialogContent class="max-w-lg max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{{ modalMode === 'create' ? 'Tambah Menu' : 'Edit Menu' }}</DialogTitle>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="handleSubmit">
                <!-- Nama Menu -->
                <div class="space-y-1.5">
                    <Label for="nama-menu">Nama Menu</Label>
                    <Input id="nama-menu" v-model="form.nama_menu" placeholder="Nama menu..." />
                    <p v-if="errors.nama_menu" class="text-xs text-destructive">{{ errors.nama_menu }}</p>
                </div>

                <!-- Kategori -->
                <div class="space-y-1.5">
                    <Label>Kategori</Label>
                    <Select v-model="form.kategori_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih kategori..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="kat in kategori"
                                :key="kat.id"
                                :value="kat.id"
                            >
                                {{ kat.nama_kategori }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="errors.kategori_id" class="text-xs text-destructive">{{ errors.kategori_id }}</p>
                </div>

                <!-- Harga -->
                <div class="space-y-1.5">
                    <Label for="harga">Harga (Rp)</Label>
                    <Input id="harga" v-model="form.harga" type="number" min="0" step="500" placeholder="25000" />
                    <p v-if="errors.harga" class="text-xs text-destructive">{{ errors.harga }}</p>
                </div>

                <!-- Deskripsi -->
                <div class="space-y-1.5">
                    <Label for="deskripsi">Deskripsi</Label>
                    <textarea
                        id="deskripsi"
                        v-model="form.deskripsi"
                        rows="3"
                        placeholder="Deskripsi singkat menu..."
                        class="flex w-full resize-none rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    />
                </div>

                <!-- Status -->
                <div class="space-y-1.5">
                    <Label>Status</Label>
                    <div class="flex gap-3">
                        <button
                            v-for="opt in (['tersedia', 'habis'] as const)"
                            :key="opt"
                            type="button"
                            :class="[
                                'flex flex-1 items-center justify-center rounded-md border py-2 text-sm font-medium transition',
                                form.status === opt
                                    ? opt === 'tersedia'
                                        ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                        : 'border-red-400 bg-red-50 text-red-700'
                                    : 'border-input text-muted-foreground hover:bg-accent',
                            ]"
                            @click="form.status = opt"
                        >
                            {{ opt === 'tersedia' ? 'Tersedia' : 'Habis' }}
                        </button>
                    </div>
                </div>

                <!-- Foto Upload -->
                <div class="space-y-1.5">
                    <Label>Foto Menu</Label>
                    <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-input py-6 text-center transition hover:bg-accent/50">
                        <input type="file" accept="image/*" class="sr-only" @change="onFotoChange" />
                        <img v-if="previewUrl" :src="previewUrl" alt="Preview" class="mb-2 h-24 w-24 rounded-lg object-cover" />
                        <Upload v-else class="h-8 w-8 text-muted-foreground" />
                        <span class="text-xs text-muted-foreground">
                            {{ previewUrl ? 'Klik untuk ganti foto' : 'Klik untuk upload (maks 5MB)' }}
                        </span>
                    </label>
                    <p v-if="errors.foto" class="text-xs text-destructive">{{ errors.foto }}</p>
                </div>

                <DialogFooter class="pt-2">
                    <DialogClose as-child>
                        <Button type="button" variant="outline">Batal</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Menyimpan...' : (modalMode === 'create' ? 'Simpan' : 'Update') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
