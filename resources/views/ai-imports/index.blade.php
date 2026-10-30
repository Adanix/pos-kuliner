@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="aiImportUploader()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FFF4EC] text-[#C2410C] border border-[#FFE6D5]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#C2410C] animate-pulse"></span>
                    Powered by Ciora Vision AI
                </span>
                <span class="text-xs text-[#786E64]">• GoFood &amp; ShopeeFood</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">AI Impor Pesanan Online</h1>
            <p class="text-sm text-[#575047] mt-0.5">Ekstrak otomatis daftar menu, kuantitas, dan subtotal pesanan dari tangkapan layar struk / aplikasi driver.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-[#786E64] bg-[#F4F0EA] px-3 py-1.5 rounded-xl border border-[#E7E0D6] flex items-center gap-1.5">
                <kbd class="px-1.5 py-0.5 rounded bg-white border border-[#D3C9BD] text-[10px] font-mono shadow-2xs font-bold">Ctrl</kbd> + <kbd class="px-1.5 py-0.5 rounded bg-white border border-[#D3C9BD] text-[10px] font-mono shadow-2xs font-bold">V</kbd>
                <span>Bisa langsung paste screenshot</span>
            </span>
        </div>
    </div>

    <!-- Upload Card with Drag & Drop + Clipboard Paste -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm">
        <form method="POST" action="{{ route('impor.store') }}" enctype="multipart/form-data" @submit="handleSubmit($event)" class="space-y-5">
            @csrf

            <!-- Interactive Dropzone -->
            <div @dragover.prevent="isDragging = true"
                 @dragleave.prevent="isDragging = false"
                 @drop.prevent="handleDrop($event)"
                 @paste.window="handlePaste($event)"
                 @click="$refs.fileInput.click()"
                 :class="isDragging ? 'border-[#C2410C] bg-[#FFF4EC]/70 ring-4 ring-[#C2410C]/10' : (hasFile ? 'border-[#047857] bg-[#ECFDF5]/30' : 'border-[#D3C9BD] bg-[#FAF8F5] hover:border-[#C2410C]/60 hover:bg-[#FFF9F5]')"
                 class="relative border-2 border-dashed rounded-2xl p-6 sm:p-8 text-center cursor-pointer transition-all duration-200">
                
                <input type="file" x-ref="fileInput" name="screenshot" accept="image/jpeg,image/png,image/webp"
                       @change="handleFileSelect($event)" class="hidden" required>

                <!-- When No File Selected -->
                <div x-show="!hasFile" class="space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-white shadow-2xs border border-[#E7E0D6] text-[#C2410C] flex items-center justify-center mx-auto text-2xl">
                        📸
                    </div>
                    <div>
                        <p class="text-sm font-bold text-[#14110F]">
                            Klik untuk memilih gambar atau seret &amp; letakkan screenshot ke sini
                        </p>
                        <p class="text-xs text-[#575047] mt-1">
                            Mendukung file JPG, PNG, atau WebP (maksimal 8MB). Anda juga dapat menekan <kbd class="px-1.5 py-0.5 rounded bg-white border border-[#D3C9BD] text-[10px] font-mono shadow-2xs font-bold">Ctrl + V</kbd> untuk menempelkan gambar dari clipboard.
                        </p>
                    </div>
                    <div class="inline-flex items-center gap-2 text-xs font-semibold text-[#C2410C] bg-white px-3 py-1.5 rounded-xl border border-[#E7E0D6] shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Pilih Tangkapan Layar Struk</span>
                    </div>
                </div>

                <!-- When File Selected: Live Preview -->
                <div x-show="hasFile" x-cloak class="flex flex-col sm:flex-row items-center gap-4 justify-center text-left" @click.stop>
                    <div class="relative shrink-0">
                        <img :src="previewUrl" alt="Pratinjau" class="w-24 h-28 object-contain rounded-xl bg-white border border-[#D3C9BD] shadow-sm p-1">
                        <span class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-[#047857] text-white text-xs flex items-center justify-center font-bold shadow">
                            ✓
                        </span>
                    </div>
                    <div class="space-y-1">
                        <div class="text-xs font-bold text-[#047857] flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>File Siap Diekstrak</span>
                        </div>
                        <div class="font-bold text-sm text-[#14110F] max-w-xs sm:max-w-md truncate" x-text="fileName"></div>
                        <div class="text-xs text-[#575047]" x-text="fileSizeText"></div>
                        <div class="pt-2 flex items-center gap-2">
                            <button type="button" @click="$refs.fileInput.click()"
                                    class="text-xs font-semibold text-[#C2410C] hover:underline cursor-pointer">
                                Ganti Gambar
                            </button>
                            <span class="text-[#D3C9BD]">•</span>
                            <button type="button" @click="clearFile()"
                                    class="text-xs font-semibold text-[#DC2626] hover:underline cursor-pointer">
                                Hapus File
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Options & Action Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end pt-1">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-[#575047] uppercase mb-1.5">Pilihan Kanal Aplikasi</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="channel" value="auto" x-model="selectedChannel" class="peer sr-only">
                            <div class="p-2.5 rounded-xl border text-center text-xs font-semibold transition peer-checked:border-[#C2410C] peer-checked:bg-[#FFF4EC] peer-checked:text-[#C2410C] border-[#D3C9BD] bg-[#FAF8F5] text-[#575047] hover:bg-[#F4F0EA]">
                                ✨ Otomatis
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="channel" value="gofood" x-model="selectedChannel" class="peer sr-only">
                            <div class="p-2.5 rounded-xl border text-center text-xs font-semibold transition peer-checked:border-[#00AA13] peer-checked:bg-[#E8F8EC] peer-checked:text-[#00880F] border-[#D3C9BD] bg-[#FAF8F5] text-[#575047] hover:bg-[#F4F0EA]">
                                🟢 GoFood
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="channel" value="shopeefood" x-model="selectedChannel" class="peer sr-only">
                            <div class="p-2.5 rounded-xl border text-center text-xs font-semibold transition peer-checked:border-[#EE4D2D] peer-checked:bg-[#FFF2EE] peer-checked:text-[#EE4D2D] border-[#D3C9BD] bg-[#FAF8F5] text-[#575047] hover:bg-[#F4F0EA]">
                                🟠 ShopeeFood
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit" :disabled="!hasFile || isSubmitting"
                            class="w-full py-3 px-5 rounded-xl font-bold text-white bg-[#C2410C] hover:bg-[#9A3412] text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Ekstrak dengan AI</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Processing Fullscreen Loading Modal -->
    <div x-show="isSubmitting" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="bg-white w-full max-w-md rounded-2xl border border-[#E7E0D6] shadow-2xl p-6 sm:p-8 text-center space-y-4">
            <div class="relative w-16 h-16 mx-auto">
                <div class="w-16 h-16 rounded-full border-4 border-[#FFF4EC] border-t-[#C2410C] animate-spin"></div>
                <div class="absolute inset-0 flex items-center justify-center text-xl">
                    ⚡
                </div>
            </div>
            <div>
                <h3 class="text-lg font-bold text-[#14110F]">Menganalisis Struk dengan Ciora AI</h3>
                <p class="text-xs text-[#575047] mt-1" x-text="loadingStepText"></p>
            </div>
            <div class="w-full bg-[#FAF8F5] rounded-full h-2 border border-[#E7E0D6] overflow-hidden">
                <div class="bg-gradient-to-r from-[#C2410C] to-[#EA580C] h-full transition-all duration-700 animate-pulse" :style="'width: ' + loadingProgress + '%'"></div>
            </div>
            <p class="text-[11px] text-[#786E64] italic">
                AI Vision sedang mendeteksi nomor pesanan, daftar item, kuantitas, dan total harga.
            </p>
        </div>
    </div>

    <!-- Import History Table -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-base text-[#14110F]">Riwayat Impor Screenshot</h3>
                <p class="text-xs text-[#575047]">Semua tangkapan layar yang pernah diunggah untuk pencatatan transaksi online.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-[#FAF8F5] border border-[#E7E0D6] text-[#575047]">
                Total: {{ $imports->total() }} Impor
            </span>
        </div>

        @if($imports->isEmpty())
            <div class="p-12 text-center text-[#9E9385] text-sm space-y-2">
                <div class="text-3xl">📭</div>
                <div class="font-medium text-[#14110F]">Belum ada screenshot yang diimpor</div>
                <div class="text-xs text-[#786E64]">Unggah tangkapan layar pesanan GoFood atau ShopeeFood pertama Anda pada kotak di atas.</div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Pratinjau</th>
                            <th class="px-3 py-2.5">Waktu</th>
                            <th class="px-3 py-2.5">Kanal</th>
                            <th class="px-3 py-2.5">ID Platform</th>
                            <th class="px-3 py-2.5">Ringkasan Item</th>
                            <th class="px-3 py-2.5 text-center">Status AI</th>
                            <th class="px-3 py-2.5 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7E0D6]">
                        @foreach($imports as $imp)
                            @php
                                $norm = $imp->normalized_extracted;
                                $itemCount = count($norm['items'] ?? []);
                                $channel = $norm['channel'] ?? ($imp->channel_detected ?? $imp->channel_selected ?? 'gofood');
                            @endphp
                            <tr class="hover:bg-[#FAF8F5] transition">
                                <td class="px-3 py-2.5">
                                    <a href="{{ route('impor.review', $imp) }}" class="block w-12 h-14 rounded-lg bg-[#FAF8F5] border border-[#E7E0D6] overflow-hidden hover:opacity-80 transition group shrink-0">
                                        <img src="{{ route('impor.image', $imp) }}" alt="Thumbnail" class="w-full h-full object-cover">
                                    </a>
                                </td>
                                <td class="px-3 py-3 text-xs text-[#575047] whitespace-nowrap">
                                    <div class="font-bold text-[#14110F]">{{ $imp->created_at->timezone('Asia/Jakarta')->format('d/m/Y') }}</div>
                                    <div class="text-[11px] text-[#786E64]">{{ $imp->created_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @if(strtolower($channel) === 'shopeefood')
                                        <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-[#FFF2EE] text-[#EE4D2D] border border-[#FFD5C8]">
                                            <span>🟠</span> ShopeeFood
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-[#E8F8EC] text-[#00880F] border border-[#C6F0D0]">
                                            <span>🟢</span> GoFood
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs font-mono font-bold text-[#14110F] whitespace-nowrap">
                                    {{ $imp->platform_order_id ?: ($norm['platform_order_id'] ?? '-') }}
                                </td>
                                <td class="px-3 py-3 text-xs text-[#575047]">
                                    @if($itemCount > 0)
                                        <div class="font-semibold text-[#14110F]">{{ $itemCount }} item terdeteksi</div>
                                        <div class="text-[11px] text-[#786E64] truncate max-w-[200px]">
                                            {{ $norm['items'][0]['source_name'] ?? 'Item' }}{{ $itemCount > 1 ? ', +' . ($itemCount - 1) . ' lainnya' : '' }}
                                        </div>
                                    @else
                                        <span class="text-[11px] text-[#9E9385] italic">Belum ada item</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center whitespace-nowrap">
                                    @php
                                        $statusClass = match($imp->status->value) {
                                            'saved' => 'bg-[#ECFDF5] text-[#047857] border border-[#A7F3D0]',
                                            'needs_review', 'ready' => 'bg-[#FFFBEB] text-[#B45309] border border-[#FDE68A]',
                                            'duplicate' => 'bg-[#FFF4EC] text-[#C2410C] border border-[#FFE6D5]',
                                            'failed' => 'bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA]',
                                            default => 'bg-[#FAF8F5] text-[#575047] border border-[#D3C9BD]',
                                        };
                                    @endphp
                                    <span class="text-[11px] px-2.5 py-1 rounded-full font-bold {{ $statusClass }}">
                                        {{ $imp->status->label() }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($imp->status->value === 'saved' && $imp->transaction)
                                            <a href="{{ route('transactions.show', $imp->transaction) }}"
                                               class="px-3 py-1.5 rounded-xl bg-[#ECFDF5] text-[#047857] hover:bg-[#D1FAE5] text-xs font-bold transition flex items-center gap-1">
                                                <span>Lihat Transaksi</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            </a>
                                        @else
                                            <a href="{{ route('impor.review', $imp) }}"
                                               class="px-3.5 py-1.5 rounded-xl bg-[#C2410C] text-white hover:bg-[#9A3412] text-xs font-bold transition shadow-2xs flex items-center gap-1">
                                                <span>Review &amp; Simpan</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        @endif

                                        @if($imp->status->value !== 'saved')
                                            <form method="POST" action="{{ route('impor.destroy', $imp) }}"
                                                  onsubmit="return confirm('Hapus screenshot impor ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Impor"
                                                        class="p-1.5 rounded-lg text-[#DC2626] hover:bg-[#FEF2F2] transition cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $imports->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function aiImportUploader() {
    return {
        isDragging: false,
        hasFile: false,
        fileName: '',
        fileSizeText: '',
        previewUrl: null,
        selectedChannel: 'auto',
        isSubmitting: false,
        loadingProgress: 20,
        loadingStepText: 'Mengunggah tangkapan layar...',

        handleFileSelect(e) {
            const files = e.target.files;
            if (files && files[0]) {
                this.setFile(files[0]);
            }
        },

        handleDrop(e) {
            this.isDragging = false;
            const files = e.dataTransfer.files;
            if (files && files[0]) {
                this.setFile(files[0]);
                // Sync to file input
                const dt = new DataTransfer();
                dt.items.add(files[0]);
                this.$refs.fileInput.files = dt.files;
            }
        },

        handlePaste(e) {
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            for (const item of items) {
                if (item.type.indexOf('image') !== -1) {
                    const file = item.getAsFile();
                    this.setFile(file);
                    // Sync to file input
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    this.$refs.fileInput.files = dt.files;
                    if (window.showAlert) {
                        window.showAlert('Tangkapan layar dari clipboard berhasil ditempel!', 'Screenshot Ditempel', 'success');
                    }
                    break;
                }
            }
        },

        setFile(file) {
            if (!file) return;
            this.hasFile = true;
            this.fileName = file.name || 'screenshot-clipboard.png';
            this.fileSizeText = (file.size / 1024 > 1024)
                ? (file.size / (1024 * 1024)).toFixed(2) + ' MB'
                : (file.size / 1024).toFixed(0) + ' KB';
            
            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
            }
            this.previewUrl = URL.createObjectURL(file);
        },

        clearFile() {
            this.hasFile = false;
            this.fileName = '';
            this.fileSizeText = '';
            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = null;
            }
            this.$refs.fileInput.value = '';
        },

        handleSubmit(e) {
            if (!this.hasFile) {
                e.preventDefault();
                return;
            }
            this.isSubmitting = true;
            this.loadingProgress = 30;
            this.loadingStepText = 'Mengunggah tangkapan layar ke server...';

            setTimeout(() => {
                this.loadingProgress = 60;
                this.loadingStepText = 'Mengirim ke Ciora Vision AI (ciora.id)...';
            }, 1200);

            setTimeout(() => {
                this.loadingProgress = 85;
                this.loadingStepText = 'Mengekstrak nomor order, daftar item menu & kuantitas...';
            }, 3000);
        }
    };
}
</script>
@endsection
