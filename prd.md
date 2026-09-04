# Product Requirement Document (PRD) — Front-End
## E-Catalog Menu — Aisya Poolside & Brew

Dokumen ini menspesifikasikan tampilan front-end (Inertia + Vue) berdasarkan struktur backend yang sudah jadi (routes, controller, props). Tujuannya: siap dipakai langsung sebagai prompt generate di Antigravity tanpa perlu nebak nama field/route.

---

## 1. Stack & Konvensi

- **Framework**: Laravel 13 + Inertia.js + Vue 3 (Composition API, `<script setup>`)
- **Styling**: Tailwind CSS
- **Routing di Vue**: pakai helper `route()` (Ziggy) — semua nama route sudah didefinisikan di backend, jangan hardcode URL manual
- **Struktur folder halaman**: `resources/js/Pages/`
- **Layout**: halaman yang butuh login (`/dashboard`, `/menu-kelola`, `/kategori-kelola`) dibungkus `AuthenticatedLayout.vue` (bawaan starter kit). Halaman publik (`/`, `/katalog`) pakai layout sendiri tanpa sidebar, disebut `GuestLayout.vue` atau tanpa layout sama sekali.

---

## 2. Design System

### Palet Warna (dari logo)

| Token Tailwind (custom) | Hex | Peran |
|---|---|---|
| `primary` | `#0E2B23` | Background hero, navbar, tombol utama |
| `accent` | `#C9B27C` | Judul, garis, ikon, harga, hover |
| `secondary` | `#7FA073` | Badge kategori, status "tersedia" |
| `surface` | `#F6F1E7` | Background konten terang, card |
| `text-dark` | `#132520` | Teks di atas surface terang |
| `text-light` | `#EFE7D2` | Teks di atas background gelap |
| `danger` | `#B5654A` | Badge status "habis" |

Tambahkan ke `tailwind.config.js` sebagai `theme.extend.colors`.

### Tipografi
- Heading: `font-serif` → import Google Fonts *Cormorant Garamond* atau *Playfair Display*
- Body/UI: `font-sans` default Tailwind (Inter/Poppins)
- Radius sudut: `rounded-2xl` untuk card, `rounded-full` untuk badge/pill

---

## 3. Peta Halaman ↔ Route ↔ Props

| Route (name) | Method | Vue Page | Props yang diterima |
|---|---|---|---|
| `home` | GET `/` | `Welcome.vue` | — (statis, `Route::inertia`) |
| `katalog` | GET `/katalog` | `Katalog.vue` | `kategori: Kategori[]`, `menu: Menu[]` |
| `pesanan.store` | POST `/pesanan` | (submit dari `Katalog.vue`, tanpa halaman baru) | flash `pesanan` di response |
| `dashboard` | GET `/dashboard` | `Dashboard.vue` | `pesanan: Pesanan[]` (dengan `detail_pesanan.menu`) |
| `pesanan.update-status` | PATCH `/pesanan/{pesanan}/status` | (aksi dari `Dashboard.vue`) | — |
| `menu.index` | GET `/menu-kelola` | `Menu/Index.vue` | `menu: Menu[]` (dengan `kategori`), `kategori: Kategori[]` |
| `menu.store` | POST `/menu-kelola` | (form di `Menu/Index.vue`) | — |
| `menu.update` | PATCH `/menu-kelola/{menu}` | (form edit di `Menu/Index.vue`) | — |
| `menu.destroy` | DELETE `/menu-kelola/{menu}` | (aksi hapus) | — |
| `kategori.index` | GET `/kategori-kelola` | `Kategori/Index.vue` | `kategori: Kategori[]` (dengan `menu_count`) |
| `kategori.store` | POST `/kategori-kelola` | (form di `Kategori/Index.vue`) | — |
| `kategori.update` | PATCH `/kategori-kelola/{kategori}` | (form edit) | — |
| `kategori.destroy` | DELETE `/kategori-kelola/{kategori}` | (aksi hapus) | — |

### Bentuk Data (shape props)

```ts
interface Kategori {
  id: number
  nama_kategori: string
  menu_count?: number // hanya ada di halaman Kategori/Index
}

interface Menu {
  id: number
  kategori_id: number
  nama_menu: string
  deskripsi: string | null
  harga: string // decimal dari Laravel dikirim sebagai string
  foto: string | null
  status: 'tersedia' | 'habis'
  kategori?: Kategori
}

interface DetailPesanan {
  id: number
  pesanan_id: number
  menu_id: number
  jumlah: number
  subtotal: string
  menu?: Menu
}

interface Pesanan {
  id: number
  nama_pelanggan: string
  nomor_meja: string
  status_pesanan: 'menunggu' | 'diproses' | 'selesai'
  total_harga: string
  detail_pesanan: DetailPesanan[]
  created_at: string
}
```

---

## 4. Spesifikasi Per Halaman

### 4.1 `Welcome.vue` (Beranda — publik)
- Hero full-width background `primary`, logo + nama brand di tengah, tagline "Coffee & Swimming Pool"
- Section foto suasana (grid 3 foto atau carousel)
- Info jam buka/tutup sebagai badge kecil
- Tombol CTA besar (`bg-accent text-text-dark`) → `<Link :href="route('katalog')">Lihat Menu</Link>`
- Section lokasi: embed Google Maps + tombol WhatsApp (floating button)

### 4.2 `Katalog.vue` (publik)
**Props**: `kategori`, `menu`

- Tab filter kategori sticky di atas grid, computed dari `props.kategori` + opsi "Semua"
- Grid card menu (`v-for` dari `props.menu` yang di-filter di client-side sesuai tab aktif) — 2 kolom mobile, 3-4 desktop
- Card: foto (`menu.foto` atau placeholder), `nama_menu`, `harga` (format `Rp {{ Number(harga).toLocaleString('id-ID') }}`)
- Item `status === 'habis'` → `opacity-50 pointer-events-none` + badge "Habis" (`bg-danger`)
- Klik card (yang tersedia) → buka modal/bottom-sheet detail menu (state lokal `selectedMenu`)
- Modal detail: foto besar, deskripsi, stepper jumlah, tombol "Tambah ke Pesanan" → push ke array `cart` (state lokal Vue, **belum** dikirim ke backend)
- Floating cart button (pojok bawah) menampilkan jumlah item di `cart`, klik → buka drawer/modal form pesanan
- Form pesanan (drawer): list item di `cart` + subtotal, input `nama_pelanggan`, input `nomor_meja`, tombol submit
- Submit pakai `router.post(route('pesanan.store'), { nama_pelanggan, nomor_meja, items: cart.map(...) })` dari Inertia — payload `items` harus array `{ menu_id, jumlah, harga }` sesuai `StorePesananRequest`
- Setelah sukses (`onSuccess`): tampilkan toast/modal konfirmasi "Pesanan diterima", kosongkan `cart`

### 4.3 `Dashboard.vue` (login required)
**Props**: `pesanan`

- List/tabel pesanan, urutkan terbaru di atas (backend sudah `latest()`)
- Tiap baris: `nama_pelanggan`, `nomor_meja`, ringkasan `detail_pesanan` (nama menu × jumlah), `total_harga`, badge `status_pesanan` (warna beda per status: kuning `menunggu`, biru `diproses`, hijau `selesai`)
- Dropdown/tombol ubah status → `router.patch(route('pesanan.update-status', pesanan.id), { status_pesanan: newStatus })`
- Badge counter jumlah pesanan `status_pesanan === 'menunggu'` di header halaman

### 4.4 `Menu/Index.vue` (login required)
**Props**: `menu`, `kategori`

- Tabel/list menu: thumbnail `foto`, `nama_menu`, nama kategori (`menu.kategori.nama_kategori`), `harga`, toggle `status`
- Tombol "Tambah Menu" → buka modal form (nama, kategori dropdown dari `props.kategori`, harga, deskripsi, upload foto, status)
- Submit tambah: `router.post(route('menu.store'), formData, { forceFormData: true })` (wajib `forceFormData` karena ada upload file)
- Submit edit: `router.patch(route('menu.update', menu.id), formData, { forceFormData: true })` — Inertia tidak bisa PATCH dengan file langsung, gunakan trik `_method: 'patch'` + `router.post`
- Tombol hapus → konfirmasi dialog dulu, lalu `router.delete(route('menu.destroy', menu.id))`

### 4.5 `Kategori/Index.vue` (login required)
**Props**: `kategori` (dengan `menu_count`)

- List sederhana: nama kategori + jumlah menu di dalamnya (`menu_count`)
- Form tambah/edit inline atau modal kecil (cuma 1 field `nama_kategori`)
- Tombol hapus dengan validasi: jika `menu_count > 0`, tampilkan konfirmasi peringatan "Kategori ini masih punya X menu" sebelum submit `router.delete(route('kategori.destroy', kategori.id))`

---

## 5. Komponen Reusable (buat di `resources/js/Components/`)

| Komponen | Dipakai di | Isi |
|---|---|---|
| `CardMenu.vue` | Katalog | foto, nama, harga, badge status |
| `BadgeStatus.vue` | Katalog, Dashboard, Menu/Index | terima prop `status` + `variant` (menu/pesanan), render warna & label sesuai |
| `FilterKategori.vue` | Katalog | tab horizontal scrollable |
| `ModalDetailMenu.vue` | Katalog | detail + stepper + tambah ke cart |
| `DrawerCart.vue` | Katalog | ringkasan cart + form pesanan |
| `FormMenu.vue` | Menu/Index | form tambah/edit menu (dipakai 2 mode lewat prop `mode: 'create' | 'edit'`) |

---

## 6. Non-Functional
- Mobile-first — target utama pelanggan akses via QR code di smartphone
- Format harga selalu `Rp` + separator ribuan Indonesia (`toLocaleString('id-ID')`)
- Gambar menu lazy-load (`loading="lazy"`) di grid katalog
- Semua form pakai Inertia `useForm()` helper (bukan `fetch`/`axios` manual) supaya error validasi dari Form Request otomatis ke-bind ke `form.errors`