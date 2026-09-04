<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Tag, Check, X, Search } from '@lucide/vue';
import * as AdminKategoriController from '@/actions/App/Http/Controllers/Admin/KategoriController';
import { dashboard } from '@/routes';

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogClose,
} from '@/components/ui/dialog';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Kelola Kategori', href: AdminKategoriController.index().url },
        ],
    },
});

interface Kategori {
    id: number;
    nama_kategori: string;
    menu_count?: number;
}

const props = defineProps<{ kategori: Kategori[] }>();

// ── Search ─────────────────────────────────────────────────────────────────
const search = ref('');
const filteredKategori = computed(() => {
    if (!search.value.trim()) return props.kategori;
    return props.kategori.filter((k) =>
        k.nama_kategori.toLowerCase().includes(search.value.toLowerCase())
    );
});

// ── Add Dialog ────────────────────────────────────────────────────────────
const addDialogOpen = ref(false);
const addForm = reactive({ nama_kategori: '', processing: false, error: '' });

function submitAdd() {
    if (!addForm.nama_kategori.trim()) return;
    addForm.processing = true;
    addForm.error = '';
    router.post(
        AdminKategoriController.store().url,
        { nama_kategori: addForm.nama_kategori },
        {
            preserveScroll: true,
            onSuccess: () => {
                addForm.processing = false;
                addForm.nama_kategori = '';
                addDialogOpen.value = false;
            },
            onError: (errors) => {
                addForm.processing = false;
                addForm.error = errors.nama_kategori ?? 'Terjadi kesalahan';
            },
        }
    );
}

// ── Inline Edit ───────────────────────────────────────────────────────────
const editingId = ref<number | null>(null);
const editValue = ref('');

function startEdit(kat: Kategori) {
    editingId.value = kat.id;
    editValue.value = kat.nama_kategori;
}
function cancelEdit() {
    editingId.value = null;
    editValue.value = '';
}
function submitEdit(kat: Kategori) {
    if (!editValue.value.trim()) return;
    router.patch(
        AdminKategoriController.update({ kategori: kat.id }).url,
        { nama_kategori: editValue.value },
        { preserveScroll: true, onSuccess: () => cancelEdit() }
    );
}

// ── Delete ────────────────────────────────────────────────────────────────
function handleDelete(kat: Kategori) {
    const count = kat.menu_count ?? 0;
    const msg = count > 0
        ? `Kategori "${kat.nama_kategori}" masih memiliki ${count} menu.\nMenghapus ini mungkin mempengaruhi data menu.\n\nLanjutkan?`
        : `Hapus kategori "${kat.nama_kategori}"?`;
    if (!confirm(msg)) return;
    router.delete(AdminKategoriController.destroy({ kategori: kat.id }).url, { preserveScroll: true });
}
</script>

<template>
    <Head title="Kelola Kategori — Aisya Poolside & Brew" />

    <div class="flex flex-col gap-6 p-4 sm:p-6">
        <!-- ── Header ─────────────────────────────────────────────────── -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Kelola Kategori</h1>
                <p class="text-muted-foreground text-sm">{{ kategori.length }} kategori terdaftar</p>
            </div>
            <Button id="btn-tambah-kategori" @click="addDialogOpen = true">
                <Plus class="mr-2 h-4 w-4" />
                Tambah Kategori
            </Button>
        </div>

        <!-- ── Search ─────────────────────────────────────────────────── -->
        <div class="relative max-w-sm">
            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input v-model="search" placeholder="Cari kategori..." class="pl-9" />
        </div>

        <!-- ── Stats ──────────────────────────────────────────────────── -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <Card v-for="kat in kategori.slice(0, 4)" :key="kat.id" class="border-dashed">
                <CardContent class="flex items-center gap-3 p-4">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10">
                        <Tag class="h-4 w-4 text-primary" />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">{{ kat.nama_kategori }}</p>
                        <p class="text-xs text-muted-foreground">{{ kat.menu_count ?? 0 }} menu</p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- ── Table Card ──────────────────────────────────────────────── -->
        <Card>
            <CardHeader>
                <CardTitle>Semua Kategori</CardTitle>
                <CardDescription>Klik ikon pensil untuk mengedit nama kategori secara langsung</CardDescription>
            </CardHeader>
            <CardContent class="p-0">
                <!-- Empty -->
                <div v-if="filteredKategori.length === 0" class="flex flex-col items-center gap-3 py-20 text-center">
                    <Tag class="h-12 w-12 text-muted-foreground opacity-40" />
                    <p class="text-sm text-muted-foreground">
                        {{ search ? 'Tidak ada kategori yang cocok' : 'Belum ada kategori.' }}
                    </p>
                </div>

                <!-- List -->
                <ul v-else class="divide-y">
                    <li
                        v-for="kat in filteredKategori"
                        :key="kat.id"
                        class="flex items-center gap-4 px-6 py-4 transition-colors hover:bg-muted/30"
                    >
                        <!-- Icon -->
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-secondary">
                            <Tag class="h-4 w-4 text-secondary-foreground" />
                        </div>

                        <!-- Inline edit or display -->
                        <div class="flex-1">
                            <div v-if="editingId === kat.id" class="flex items-center gap-2">
                                <Input
                                    :id="`edit-kategori-${kat.id}`"
                                    v-model="editValue"
                                    class="h-8 text-sm"
                                    @keyup.enter="submitEdit(kat)"
                                    @keyup.escape="cancelEdit"
                                />
                                <Button size="icon" class="h-8 w-8" variant="default" @click="submitEdit(kat)">
                                    <Check class="h-4 w-4" />
                                </Button>
                                <Button size="icon" class="h-8 w-8" variant="ghost" @click="cancelEdit">
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                            <div v-else class="flex items-center gap-2">
                                <p class="font-medium">{{ kat.nama_kategori }}</p>
                                <Badge variant="secondary" class="text-xs">
                                    {{ kat.menu_count ?? 0 }} menu
                                </Badge>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div v-if="editingId !== kat.id" class="flex shrink-0 items-center gap-1">
                            <Button
                                :id="`btn-edit-kategori-${kat.id}`"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8"
                                title="Edit kategori"
                                @click="startEdit(kat)"
                            >
                                <Pencil class="h-4 w-4" />
                            </Button>
                            <Button
                                :id="`btn-hapus-kategori-${kat.id}`"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-destructive hover:text-destructive"
                                title="Hapus kategori"
                                @click="handleDelete(kat)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>

    <!-- ── Add Dialog ─────────────────────────────────────────────────── -->
    <Dialog v-model:open="addDialogOpen">
        <DialogContent class="max-w-sm">
            <DialogHeader>
                <DialogTitle>Tambah Kategori</DialogTitle>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submitAdd">
                <div class="space-y-1.5">
                    <Label for="input-nama-kategori">Nama Kategori</Label>
                    <Input
                        id="input-nama-kategori"
                        v-model="addForm.nama_kategori"
                        placeholder="Contoh: Minuman, Makanan..."
                        @keyup.enter="submitAdd"
                    />
                    <p v-if="addForm.error" class="text-xs text-destructive">{{ addForm.error }}</p>
                </div>

                <DialogFooter>
                    <DialogClose as-child>
                        <Button type="button" variant="outline">Batal</Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        :disabled="addForm.processing || !addForm.nama_kategori.trim()"
                    >
                        {{ addForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
