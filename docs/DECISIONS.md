# DECISIONS.md — Keputusan Teknis POS Kuliner Ciora

Dokumen ini mencatat keputusan arsitektural dan teknis yang diambil selama implementasi PRD v2.0 pada proyek `kuliner`.

---

## 1. Format Uang Tanpa Ekstensi `intl` (`App\Support\Money`)
* **Konteks:** Container runtime PHP 8.5 tidak memasang ekstensi `intl` (sehingga helper `Number::format()` bawaan Laravel melempar `RuntimeException`).
* **Keputusan:** Dibuat helper mandiri `App\Support\Money` dengan fungsi `format()` menggunakan `number_format($amount, 0, ',', '.')` dan `parse()` yang membersihkan simbol non-digit serta membatasi nilai maksimum 999.999.999.
* **Hasil:** 100% konsisten dengan PRD §8.1 (`Rp 12.500`, tanpa desimal, tanpa dependensi eksternal).

---

## 2. Pengujian Upload Gambar Tanpa Ekstensi `gd`
* **Konteks:** `UploadedFile::fake()->image()` memerlukan fungsi GD `imagecreatetruecolor()`.
* **Keputusan:** Pada unit & feature test, file uji JPEG dibuat dengan payload header biner JPEG nyata (`createFakeJpg()`).
* **Hasil:** Seluruh pengujian fitur upload gambar dan hash SHA-256 berjalan hijau 100% di SQLite in-memory dan container.

---

## 3. Tema Desain "Warm Ember" di Tailwind CSS v4
* **Konteks:** PRD §4 menetapkan palet terracotta dan netral hangat (`#FAF8F5`, `#C2410C`, `#14110F`, `#E7E0D6`, dll.) serta struk thermal 58mm.
* **Keputusan:** Token warna didefinisikan secara deklaratif di `resources/css/app.css` via direktif `@theme` Tailwind CSS 4, dan aturan `@media print` 58mm diatur agar otomatis menyembunyikan navigasi dan membatasi lebar kertas menjadi 52–58mm.

---

## 4. Mode Simulasi Aman Vision AI (`CioraClient`)
* **Konteks:** Endpoint publik Ciora memerlukan `CIORA_API_KEY`. Bila belum diisi di `.env`, aplikasi tidak boleh crash saat pengujian atau demo.
* **Keputusan:** `CioraClient` mendeteksi keberadaan API key. Jika belum ada, sistem mengaktifkan simulasi ekstraksi aman (mock) dan `php artisan ai:check` menginformasikan status tanpa membocorkan kredensial.
