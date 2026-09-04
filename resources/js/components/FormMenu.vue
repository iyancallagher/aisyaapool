<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, Upload } from '@lucide/vue';
import * as AdminMenuController from '@/actions/App/Http/Controllers/Admin/MenuController';

interface Kategori {
    id: number;
    nama_kategori: string;
}
interface Menu {
    id: number;
    nama_menu: string;
    deskripsi: string | null;
    harga: string;
    foto: string | null;
    status: 'tersedia' | 'habis';
    kategori_id: number;
}

const props = defineProps<{
    open: boolean;
    mode: 'create' | 'edit';
    menu?: Menu | null;
    kategori: Kategori[];
}>();

const emit = defineEmits<{ close: [] }>();

// ── Form state ──────────────────────────────────────────────────────────────
const form = ref({
    nama_menu: '',
    kategori_id: '' as string | number,
    harga: '',
    deskripsi: '',
    status: 'tersedia' as 'tersedia' | 'habis',
    foto: null as File | null,
    _method: 'post' as 'post' | 'patch',
});
const errors = ref<Record<string, string>>({});
const processing = ref(false);
const previewUrl = ref<string | null>(null);

// Sync form saat mode/menu berubah
watch(
    [() => props.open, () => props.menu],
    () => {
        if (props.open) {
            if (props.mode === 'edit' && props.menu) {
                form.value.nama_menu = props.menu.nama_menu;
                form.value.kategori_id = props.menu.kategori_id;
                form.value.harga = props.menu.harga;
                form.value.deskripsi = props.menu.deskripsi ?? '';
                form.value.status = props.menu.status;
                form.value.foto = null;
                form.value._method = 'patch';
                previewUrl.value = props.menu.foto ? `/storage/${props.menu.foto}` : null;
            } else {
                form.value.nama_menu = '';
                form.value.kategori_id = '';
                form.value.harga = '';
                form.value.deskripsi = '';
                form.value.status = 'tersedia';
                form.value.foto = null;
                form.value._method = 'post';
                previewUrl.value = null;
            }
            errors.value = {};
        }
    },
    { immediate: true }
);

function onFotoChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.value.foto = file;
        previewUrl.value = URL.createObjectURL(file);
    }
}

const title = computed(() => (props.mode === 'create' ? 'Tambah Menu' : 'Edit Menu'));

function handleSubmit() {
    processing.value = true;
    errors.value = {};

    const payload = new FormData();
    payload.append('nama_menu', form.value.nama_menu);
    payload.append('kategori_id', String(form.value.kategori_id));
    payload.append('harga', form.value.harga);
    payload.append('deskripsi', form.value.deskripsi);
    payload.append('status', form.value.status);
    if (form.value.foto) payload.append('foto', form.value.foto);

    const isEdit = props.mode === 'edit' && props.menu;
    const url = isEdit
        ? AdminMenuController.update({ menu: props.menu!.id }).url
        : AdminMenuController.store().url;

    // Inertia can't PATCH with FormData — use POST + _method spoofing
    if (isEdit) payload.append('_method', 'PATCH');

    router.post(url, payload, {
        forceFormData: true,
        onSuccess: () => {
            processing.value = false;
            emit('close');
        },
        onError: (errs) => {
            processing.value = false;
            errors.value = errs;
        },
    });
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
            <div v-if="open" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm" @click="emit('close')" />
        </Transition>

        <!-- Modal Panel -->
        <Transition
            enter-active-class="transition-all duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open"
                class="fixed inset-x-4 top-1/2 z-50 mx-auto max-w-lg -translate-y-1/2 overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 class="font-display text-lg font-semibold text-brand-text-dark">{{ title }}</h3>
                    <button
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200"
                        @click="emit('close')"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Form -->
                <div class="max-h-[75vh] overflow-y-auto">
                    <form class="space-y-4 px-6 py-5" @submit.prevent="handleSubmit">
                        <!-- Nama Menu -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-brand-text-dark">Nama Menu</label>
                            <input
                                v-model="form.nama_menu"
                                type="text"
                                placeholder="Nama menu..."
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20"
                            />
                            <p v-if="errors.nama_menu" class="mt-1 text-xs text-brand-danger">{{ errors.nama_menu }}</p>
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-brand-text-dark">Kategori</label>
                            <select
                                v-model="form.kategori_id"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20"
                            >
                                <option value="" disabled>Pilih kategori...</option>
                                <option v-for="kat in kategori" :key="kat.id" :value="kat.id">
                                    {{ kat.nama_kategori }}
                                </option>
                            </select>
                            <p v-if="errors.kategori_id" class="mt-1 text-xs text-brand-danger">{{ errors.kategori_id }}</p>
                        </div>

                        <!-- Harga -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-brand-text-dark">Harga (Rp)</label>
                            <input
                                v-model="form.harga"
                                type="number"
                                min="0"
                                step="500"
                                placeholder="25000"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20"
                            />
                            <p v-if="errors.harga" class="mt-1 text-xs text-brand-danger">{{ errors.harga }}</p>
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-brand-text-dark">Deskripsi</label>
                            <textarea
                                v-model="form.deskripsi"
                                rows="3"
                                placeholder="Deskripsi singkat menu..."
                                class="w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20"
                            />
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-brand-text-dark">Status</label>
                            <div class="flex gap-3">
                                <label
                                    v-for="opt in (['tersedia', 'habis'] as const)"
                                    :key="opt"
                                    :class="[
                                        'flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl border py-2.5 text-sm font-medium transition',
                                        form.status === opt
                                            ? opt === 'tersedia'
                                                ? 'border-brand-secondary bg-brand-secondary/10 text-brand-secondary'
                                                : 'border-brand-danger bg-brand-danger/10 text-brand-danger'
                                            : 'border-gray-200 text-gray-500 hover:border-gray-300',
                                    ]"
                                >
                                    <input v-model="form.status" type="radio" :value="opt" class="sr-only" />
                                    {{ opt === 'tersedia' ? 'Tersedia' : 'Habis' }}
                                </label>
                            </div>
                        </div>

                        <!-- Foto Upload -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-brand-text-dark">Foto Menu</label>
                            <label
                                class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 py-6 text-center transition hover:border-brand-accent/50 hover:bg-brand-surface/50"
                            >
                                <input type="file" accept="image/*" class="sr-only" @change="onFotoChange" />
                                <img
                                    v-if="previewUrl"
                                    :src="previewUrl"
                                    alt="Preview"
                                    class="mb-2 h-24 w-24 rounded-xl object-cover"
                                />
                                <Upload v-else class="h-8 w-8 text-gray-400" />
                                <span class="text-xs text-gray-500">
                                    {{ previewUrl ? 'Klik untuk ganti foto' : 'Klik untuk upload foto' }}
                                </span>
                            </label>
                            <p v-if="errors.foto" class="mt-1 text-xs text-brand-danger">{{ errors.foto }}</p>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :disabled="processing"
                            class="w-full rounded-xl bg-brand-primary py-3 text-sm font-semibold text-brand-text-light transition hover:bg-brand-accent hover:text-brand-text-dark disabled:opacity-50"
                        >
                            {{ processing ? 'Menyimpan...' : (mode === 'create' ? 'Simpan Menu' : 'Update Menu') }}
                        </button>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
