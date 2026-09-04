<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { katalog, dashboard } from '@/routes';
import { MapPin, Clock, Phone, ArrowRight, ChevronDown, Coffee, Waves, Star } from '@lucide/vue';

const scrollY = ref(0);
const navOpaque = ref(false);

function onScroll() {
    scrollY.value = window.scrollY;
    navOpaque.value = window.scrollY > 60;
}
onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

// Lokasi — Aisya Poolside & Brew
const mapEmbedUrl = 'https://maps.google.com/maps?q=-0.5267287,117.1272776&hl=id&z=17&output=embed';
const mapLinkUrl = 'https://maps.app.goo.gl/M7A1y5fPRYtBDcCg8';
// Galeri — dokumentasi tempat
const galleryImages = [
    {
        src: 'https://images.unsplash.com/photo-1680013995061-2d6d1f3ab417?q=80&w=1200&auto=format&fit=crop',
        alt: 'Kolam renang dari atas',
        caption: 'Kolam Renang',
        span: 'sm:col-span-2 sm:row-span-2',
    },
    {
        src: 'https://images.unsplash.com/photo-1751956066306-c5684cbcf385?q=80&w=800&auto=format&fit=crop',
        alt: 'Suasana coffee bar',
        caption: 'Coffee Bar',
        span: '',
    },
    {
        src: 'https://images.unsplash.com/photo-1758900450186-e829f72d25fb?q=80&w=800&auto=format&fit=crop',
        alt: 'Kopi dengan latte art',
        caption: 'Signature Coffee',
        span: '',
    },
    {
        src: 'https://images.unsplash.com/photo-1631356552355-08dfe9d8c533?q=80&w=800&auto=format&fit=crop',
        alt: 'Area kolam renang biru jernih',
        caption: 'Area Renang',
        span: '',
    },
    {
        src: 'https://images.unsplash.com/photo-1744330311965-ca7342d73512?q=80&w=800&auto=format&fit=crop',
        alt: 'Area duduk outdoor yang teduh',
        caption: 'Area Outdoor',
        span: '',
    },
    {
        src: 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?q=80&w=1200&auto=format&fit=crop',
        alt: 'Hidangan makanan',
        caption: 'Makanan & Snack',
        span: 'sm:col-span-2',
    },
];

const lightboxIndex = ref<number | null>(null);
function openLightbox(i: number) {
    lightboxIndex.value = i;
}
function closeLightbox() {
    lightboxIndex.value = null;
}
</script>

<template>

    <Head title="Aisya Poolside & Brew — Coffee & Swimming Pool" />

    <div class="min-h-screen overflow-x-hidden"
        style="font-family: 'Instrument Sans', sans-serif; background-color: #f8f5ef;">

        <!-- ══ NAVBAR ══════════════════════════════════════════════════════ -->
        <nav :class="[
            'fixed inset-x-0 top-0 z-50 transition-all duration-500',
            navOpaque
                ? 'bg-[#0e2b23]/95 shadow-lg shadow-black/20 backdrop-blur-md py-2.5 sm:py-3'
                : 'bg-transparent py-3.5 sm:py-5'
        ]">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 sm:px-5">
                <!-- Logo -->
                <a href="/"
                    class="text-base font-bold text-[#c9b27c] sm:text-2xl truncate max-w-[55%] sm:max-w-none">Aisya
                    Poolside & Brew</a>

                <!-- CTA Buttons -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    <Link v-if="$page.props.auth?.user" :href="dashboard()"
                        class="rounded-full border border-[#c9b27c]/40 px-3 py-1.5 text-xs text-[#c9b27c] transition hover:bg-[#c9b27c]/10 sm:px-4 sm:text-sm">
                        Dashboard
                    </Link>
                    <Link :href="katalog()"
                        class="group flex items-center gap-1.5 rounded-full bg-[#c9b27c] px-3.5 py-2 text-xs font-semibold text-[#0e2b23] shadow-md transition hover:bg-[#d4be8a] active:scale-95 sm:gap-2 sm:px-5 sm:text-sm">
                        Pesan
                        <ArrowRight class="h-3 w-3 transition group-hover:translate-x-0.5 sm:h-3.5 sm:w-3.5" />
                    </Link>
                </div>
            </div>
        </nav>

        <!-- ══ HERO ════════════════════════════════════════════════════════ -->
        <section class="relative flex min-h-[100svh] flex-col items-center justify-center overflow-hidden bg-[#0e2b23]">
            <!-- Animated gradient orbs -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div
                    class="absolute -left-32 top-1/4 h-[350px] w-[350px] animate-blob rounded-full bg-[#c9b27c]/8 blur-3xl sm:h-[600px] sm:w-[600px]" />
                <div
                    class="absolute -right-32 bottom-1/4 h-[300px] w-[300px] animate-blob animation-delay-2000 rounded-full bg-[#4a8c5c]/10 blur-3xl sm:h-[500px] sm:w-[500px]" />
                <div
                    class="absolute left-1/2 top-1/2 h-[250px] w-[250px] -translate-x-1/2 -translate-y-1/2 animate-blob animation-delay-4000 rounded-full bg-[#c9b27c]/5 blur-3xl sm:h-[400px] sm:w-[400px]" />
            </div>

            <!-- Subtle grid pattern -->
            <div class="pointer-events-none absolute inset-0 opacity-[0.03]"
                style="background-image: linear-gradient(#c9b27c 1px, transparent 1px), linear-gradient(to right, #c9b27c 1px, transparent 1px); background-size: 40px 40px;" />

            <!-- Content -->
            <div class="relative z-10 flex flex-col items-center px-5 text-center sm:px-6">
                <!-- Open badge -->
                <div
                    class="mb-6 inline-flex animate-fade-in items-center gap-2 rounded-full border border-[#c9b27c]/25 bg-[#c9b27c]/10 px-3 py-1.5 text-[10px] font-medium tracking-widest text-[#c9b27c] backdrop-blur-sm sm:mb-8 sm:px-4 sm:text-xs">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400 animate-pulse" />
                    <span class="whitespace-nowrap">BUKA SETIAP HARI · 08.00–22.00</span>
                </div>

                <!-- Logo large -->
                <img src="/storage/logo.png" alt="Aisya Poolside & Brew"
                    class="mb-6 h-28 w-auto drop-shadow-2xl animate-fade-in sm:mb-8 sm:h-64"
                    style="animation-delay: 0.1s;" />

                <!-- Tagline -->
                <p class="max-w-xs text-sm leading-relaxed text-[#e8dcc8]/60 animate-fade-in sm:max-w-md sm:text-lg"
                    style="animation-delay: 0.2s;">
                    Nikmati kopi pilihan dan hidangan lezat ditemani semilir angin segar di tepi kolam renang kami.
                </p>

                <!-- Pillars -->
                <div class="mt-5 flex flex-wrap items-center justify-center gap-x-3 gap-y-2 animate-fade-in sm:mt-6 sm:gap-4"
                    style="animation-delay: 0.3s;">
                    <span class="flex items-center gap-1.5 text-[11px] text-[#c9b27c]/70 sm:text-xs">
                        <Coffee class="h-3.5 w-3.5" /> Specialty Coffee
                    </span>
                    <span class="hidden h-1 w-1 rounded-full bg-[#c9b27c]/30 sm:block" />
                    <span class="flex items-center gap-1.5 text-[11px] text-[#c9b27c]/70 sm:text-xs">
                        <Waves class="h-3.5 w-3.5" /> Swimming Pool
                    </span>
                    <span class="hidden h-1 w-1 rounded-full bg-[#c9b27c]/30 sm:block" />
                    <span class="flex items-center gap-1.5 text-[11px] text-[#c9b27c]/70 sm:text-xs">
                        <Star class="h-3.5 w-3.5" /> Good Vibes Only
                    </span>
                </div>

                <!-- CTA -->
                <div class="mt-8 flex w-full max-w-xs flex-col items-center gap-2.5 animate-fade-in sm:mt-10 sm:w-auto sm:max-w-none sm:flex-row sm:gap-3"
                    style="animation-delay: 0.4s;">
                    <Link :href="katalog()"
                        class="group flex w-full items-center justify-center gap-2 rounded-full bg-[#c9b27c] px-6 py-3 text-sm font-bold text-[#0e2b23] shadow-xl shadow-[#c9b27c]/20 transition hover:bg-[#d4be8a] hover:shadow-2xl active:scale-95 sm:w-auto sm:px-8 sm:py-3.5">
                        Lihat Menu & Pesan
                        <ArrowRight class="h-4 w-4 transition group-hover:translate-x-1" />
                    </Link>
                    <a href="#suasana"
                        class="flex w-full items-center justify-center gap-2 rounded-full border border-[#c9b27c]/25 px-6 py-3 text-sm text-[#c9b27c]/80 transition hover:border-[#c9b27c]/50 hover:text-[#c9b27c] sm:w-auto sm:py-3.5">
                        Selengkapnya
                    </a>
                </div>
            </div>

            <!-- Scroll arrow -->
            <a href="#suasana"
                class="absolute bottom-6 left-1/2 -translate-x-1/2 flex flex-col items-center gap-1 text-[#c9b27c]/30 transition hover:text-[#c9b27c]/60 animate-bounce sm:bottom-8">
                <ChevronDown class="h-5 w-5" />
            </a>
        </section>

        <!-- ══ SUASANA ════════════════════════════════════════════════════ -->
        <section id="suasana" class="bg-[#f8f5ef] px-4 py-16 sm:px-5 sm:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="mb-10 text-center sm:mb-14">
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#4a8c5c]">Suasana Kami</p>
                    <h2 class="mt-3 text-2xl font-bold leading-tight text-[#0e2b23] sm:text-4xl">
                        Tempat yang <span class="italic text-[#c9b27c]">Sempurna</span> untuk Bersantai
                    </h2>
                    <p class="mx-auto mt-3 max-w-lg text-sm leading-relaxed text-[#0e2b23]/50 sm:mt-4">
                        Dari suara gemericik air kolam hingga aroma kopi yang menenangkan — setiap sudut dirancang untuk
                        kenyamananmu.
                    </p>
                </div>

                <!-- Feature grid -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-5">
                    <!-- Card 1 -->
                    <div
                        class="group relative overflow-hidden rounded-3xl bg-[#0e2b23] p-6 text-center transition-transform duration-300 hover:-translate-y-1 hover:shadow-2xl sm:col-span-1 sm:row-span-2 sm:p-8">
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-b from-[#c9b27c]/5 to-transparent" />
                        <Waves class="mx-auto mb-3 h-10 w-10 text-[#c9b27c]/60 sm:mb-4 sm:h-14 sm:w-14" />
                        <h3 class="font-bold text-[#c9b27c]">Kolam Renang</h3>
                        <p class="mt-2 text-xs leading-relaxed text-white/50">Area renang bersih dan nyaman untuk
                            seluruh keluarga. Tersedia gazebo dan kursi santai di tepi kolam.</p>
                    </div>

                    <!-- Card 2 -->
                    <div
                        class="group relative overflow-hidden rounded-3xl bg-white p-6 text-center shadow-sm ring-1 ring-black/5 transition-transform duration-300 hover:-translate-y-1 hover:shadow-xl sm:p-8">
                        <Coffee class="mx-auto mb-3 h-9 w-9 text-[#c9b27c] sm:mb-4 sm:h-12 sm:w-12" />
                        <h3 class="font-bold text-[#0e2b23]">Coffee Bar</h3>
                        <p class="mt-2 text-xs leading-relaxed text-[#0e2b23]/50">Kopi pilihan dari biji terbaik
                            berbagai daerah di Indonesia, diseduh oleh barista berpengalaman.</p>
                    </div>

                    <!-- Card 3 -->
                    <div
                        class="group relative overflow-hidden rounded-3xl bg-white p-6 text-center shadow-sm ring-1 ring-black/5 transition-transform duration-300 hover:-translate-y-1 hover:shadow-xl sm:p-8">
                        <svg class="mx-auto mb-3 h-9 w-9 text-[#4a8c5c] sm:mb-4 sm:h-12 sm:w-12" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="M11 9H9V2H7v7H5V2H3v7a4 4 0 0 0 3 3.86V22h2v-9.14A4 4 0 0 0 11 9zM16 6v8h3v8h2V2c-2.76 0-5 2.24-5 4z" />
                        </svg>
                        <h3 class="font-bold text-[#0e2b23]">Makanan & Snack</h3>
                        <p class="mt-2 text-xs leading-relaxed text-[#0e2b23]/50">Pilihan menu makanan ringan hingga
                            berat yang cocok dinikmati sembari bersantai di pinggir kolam.</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- ══ GALERI ══════════════════════════════════════════════════════ -->
        <section id="galeri" class="bg-[#0e2b23] px-4 py-16 sm:px-5 sm:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="mb-10 text-center sm:mb-14">
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#c9b27c]/60">Galeri</p>
                    <h2 class="mt-3 text-2xl font-bold text-white sm:text-4xl">
                        Momen di <span class="italic text-[#c9b27c]">Aisya</span>
                    </h2>
                    <p class="mx-auto mt-3 max-w-lg text-sm leading-relaxed text-white/40 sm:mt-4">
                        Sekilas suasana kolam, kopi, dan hidangan yang menanti kunjunganmu.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                    <button v-for="(img, i) in galleryImages" :key="img.caption" type="button"
                        :class="['group relative overflow-hidden rounded-2xl sm:rounded-3xl', img.span]"
                        @click="openLightbox(i)">
                        <img :src="img.src" :alt="img.alt" loading="lazy"
                            class="h-full min-h-[140px] w-full object-cover transition duration-500 group-hover:scale-110 sm:min-h-[180px]" />
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-black/0 opacity-0 transition group-hover:opacity-100" />
                        <span
                            class="absolute bottom-2.5 left-3 text-xs font-medium text-white opacity-0 transition group-hover:opacity-100 sm:bottom-3 sm:left-4 sm:text-sm">
                            {{ img.caption }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Lightbox -->
            <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="lightboxIndex !== null"
                    class="fixed inset-0 z-[60] flex items-center justify-center bg-black/85 p-4 backdrop-blur-sm"
                    @click="closeLightbox">
                    <button type="button"
                        class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:right-6 sm:top-6"
                        @click="closeLightbox">
                        ✕
                    </button>
                    <img v-if="lightboxIndex !== null" :src="galleryImages[lightboxIndex].src"
                        :alt="galleryImages[lightboxIndex].alt"
                        class="max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl" @click.stop />
                </div>
            </Transition>
        </section>

        <!-- ══ INFO HIGHLIGHTS ════════════════════════════════════════════ -->
        <section id="info" class="bg-[#0e2b23] px-4 py-16 sm:px-5 sm:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="mb-10 text-center sm:mb-14">
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#c9b27c]/60">Informasi</p>
                    <h2 class="mt-3 text-2xl font-bold text-white sm:text-4xl">Yang Perlu Kamu Tahu</h2>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-5">
                    <div
                        class="rounded-3xl border border-[#c9b27c]/10 bg-[#c9b27c]/5 p-6 text-center backdrop-blur-sm sm:p-8">
                        <div
                            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#c9b27c]/10 sm:mb-5 sm:h-16 sm:w-16">
                            <Clock class="h-6 w-6 text-[#c9b27c] sm:h-7 sm:w-7" />
                        </div>
                        <h3 class="font-semibold text-white">Jam Operasional</h3>
                        <p class="mt-2 text-sm text-white/50">Senin – Minggu</p>
                        <p class="mt-1 text-base font-bold text-[#c9b27c] sm:text-lg">08.00 – 22.00 WIB</p>
                    </div>

                    <div class="rounded-3xl border border-[#c9b27c]/10 bg-[#c9b27c]/5 p-6 text-center sm:p-8">
                        <div
                            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#c9b27c]/10 sm:mb-5 sm:h-16 sm:w-16">
                            <MapPin class="h-6 w-6 text-[#c9b27c] sm:h-7 sm:w-7" />
                        </div>
                        <h3 class="font-semibold text-white">Lokasi</h3>
                        <p class="mt-2 text-sm leading-relaxed text-white/50">Jl. Contoh No. 123<br />Kota, Provinsi</p>
                        <a href="#lokasi"
                            class="mt-3 inline-block text-xs text-[#c9b27c] underline underline-offset-4 hover:no-underline">Lihat
                            di peta →</a>
                    </div>

                    <div class="rounded-3xl border border-[#c9b27c]/10 bg-[#c9b27c]/5 p-6 text-center sm:p-8">
                        <div
                            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#c9b27c]/10 sm:mb-5 sm:h-16 sm:w-16">
                            <Phone class="h-6 w-6 text-[#c9b27c] sm:h-7 sm:w-7" />
                        </div>
                        <h3 class="font-semibold text-white">Reservasi</h3>
                        <p class="mt-2 text-sm leading-relaxed text-white/50">Hubungi kami via WhatsApp untuk reservasi
                            dan pertanyaan lebih lanjut</p>
                        <a href="https://wa.me/6281234567890" target="_blank"
                            class="mt-3 inline-block text-xs text-[#c9b27c] underline underline-offset-4 hover:no-underline">Chat
                            WhatsApp →</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══ LOKASI ═══════════════════════════════════════════════════════ -->
        <section id="lokasi" class="bg-[#f8f5ef] px-4 py-16 sm:px-5 sm:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="mb-8 text-center sm:mb-10">
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#4a8c5c]">Temukan Kami</p>
                    <h2 class="mt-3 text-2xl font-bold text-[#0e2b23] sm:text-4xl">Lokasi Kami</h2>
                </div>

                <div class="overflow-hidden rounded-3xl ring-1 ring-[#0e2b23]/10 shadow-xl">
                    <iframe :src="mapEmbedUrl" class="h-56 w-full border-0 sm:h-72" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade" allowfullscreen />
                </div>

                <div
                    class="mt-4 flex flex-col items-center justify-center gap-1.5 text-center sm:mt-5 sm:flex-row sm:gap-3">
                    <p class="text-sm text-[#0e2b23]/50">Jl. Contoh No. 123, Kota, Provinsi</p>
                    <span class="hidden h-1 w-1 rounded-full bg-[#0e2b23]/20 sm:block" />
                    <a :href="mapLinkUrl" target="_blank" rel="noopener"
                        class="text-sm font-medium text-[#c9b27c] hover:underline">Buka Google Maps →</a>
                </div>
            </div>
        </section>

        <!-- ══ FINAL CTA ═══════════════════════════════════════════════════ -->
        <section class="relative overflow-hidden bg-[#0e2b23] px-4 py-20 text-center sm:px-5 sm:py-32">
            <div class="pointer-events-none absolute inset-0">
                <div
                    class="absolute left-1/4 top-0 h-48 w-48 -translate-y-1/2 rounded-full bg-[#c9b27c]/8 blur-3xl sm:h-72 sm:w-72" />
                <div
                    class="absolute right-1/4 bottom-0 h-48 w-48 translate-y-1/2 rounded-full bg-[#4a8c5c]/8 blur-3xl sm:h-72 sm:w-72" />
            </div>
            <div class="relative z-10">
                <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#c9b27c]/50">Siap Menikmati?</p>
                <h2 class="mt-4 text-3xl font-bold leading-tight text-white sm:text-5xl">
                    Lihat Semua <br />Menu dan Pesan Sekarang
                </h2>
                <p class="mx-auto mt-4 max-w-xs text-sm leading-relaxed text-white/40 sm:max-w-sm">
                    Nikmati pengalaman bersantai yang sempurna dengan kopi pilihan dan hidangan lezat di Aisya Poolside
                    & Brew.
                </p>
                <Link :href="katalog()"
                    class="mt-8 inline-flex items-center gap-2.5 rounded-full bg-[#c9b27c] px-7 py-3.5 text-sm font-bold text-[#0e2b23] shadow-2xl shadow-[#c9b27c]/20 transition hover:bg-[#d4be8a] hover:shadow-3xl active:scale-95 sm:mt-10 sm:gap-3 sm:px-10 sm:py-4 sm:text-base">
                    Lihat Menu Lengkap
                    <ArrowRight class="h-4 w-4 sm:h-5 sm:w-5" />
                </Link>
            </div>
        </section>

        <!-- ══ FOOTER ══════════════════════════════════════════════════════ -->
        <footer class="bg-[#071a12] px-4 py-8 sm:px-5 sm:py-10">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 sm:flex-row">
                <img src="/storage/logo.png" alt="Aisya Poolside & Brew" class="h-7 w-auto opacity-60 sm:h-8" />
                <p class="text-center text-xs text-white/25">© {{ new Date().getFullYear() }} Aisya Poolside & Brew. All
                    rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="https://wa.me/6281234567890" target="_blank"
                        class="text-xs text-white/30 hover:text-white/60 transition">WhatsApp</a>
                    <Link :href="katalog()" class="text-xs text-white/30 hover:text-white/60 transition">Menu</Link>
                </div>
            </div>
        </footer>

        <!-- ══ FLOATING WA ═════════════════════════════════════════════════ -->
        <a href="https://wa.me/6281234567890" target="_blank" rel="noopener"
            class="fixed bottom-5 right-5 z-50 flex h-12 w-12 items-center justify-center rounded-full bg-[#25D366] text-white shadow-xl shadow-[#25D366]/40 transition hover:scale-110 active:scale-95 sm:bottom-6 sm:right-6 sm:h-14 sm:w-14"
            title="Chat WhatsApp">
            <svg class="h-6 w-6 sm:h-7 sm:w-7" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z" />
            </svg>
        </a>
    </div>
</template>

<style scoped>
@keyframes blob {

    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }

    33% {
        transform: translate(30px, -20px) scale(1.05);
    }

    66% {
        transform: translate(-20px, 20px) scale(0.97);
    }
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(16px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-blob {
    animation: blob 10s infinite ease-in-out;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

.animate-fade-in {
    animation: fade-in 0.7s both ease-out;
}
</style>