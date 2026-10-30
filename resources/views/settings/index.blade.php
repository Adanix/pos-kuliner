@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ tab: 'profile', showUserModal: false }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Pengaturan Sistem</h1>
            <p class="text-sm text-[#575047] mt-0.5">Konfigurasi outlet, aturan operasional kasir, kuota AI, dan pengguna.</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-[#E7E0D6] flex space-x-6 text-sm font-semibold overflow-x-auto">
        <button @click="tab = 'profile'" :class="tab === 'profile' ? 'border-[#C2410C] text-[#C2410C]' : 'border-transparent text-[#575047] hover:text-[#14110F]'" class="py-3 border-b-2 whitespace-nowrap">
            Profil Outlet & Struk
        </button>
        <button @click="tab = 'operational'" :class="tab === 'operational' ? 'border-[#C2410C] text-[#C2410C]' : 'border-transparent text-[#575047] hover:text-[#14110F]'" class="py-3 border-b-2 whitespace-nowrap">
            Aturan Operasional
        </button>
        <button @click="tab = 'ai'" :class="tab === 'ai' ? 'border-[#C2410C] text-[#C2410C]' : 'border-transparent text-[#575047] hover:text-[#14110F]'" class="py-3 border-b-2 whitespace-nowrap">
            AI Vision & Privasi
        </button>
        <button @click="tab = 'users'" :class="tab === 'users' ? 'border-[#C2410C] text-[#C2410C]' : 'border-transparent text-[#575047] hover:text-[#14110F]'" class="py-3 border-b-2 whitespace-nowrap">
            Pengguna & Kasir
        </button>
        <button @click="tab = 'audit'" :class="tab === 'audit' ? 'border-[#C2410C] text-[#C2410C]' : 'border-transparent text-[#575047] hover:text-[#14110F]'" class="py-3 border-b-2 whitespace-nowrap">
            Audit Trail
        </button>
    </div>

    <!-- Form for Settings Updates -->
    <form method="POST" action="{{ route('settings.update') }}">
        @csrf
        @method('PUT')

        <!-- Tab 1: Profil Outlet -->
        <div x-show="tab === 'profile'" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm space-y-4 max-w-2xl">
            <h3 class="font-bold text-base text-[#14110F] mb-3">Informasi Outlet & Struk Thermal</h3>

            <div>
                <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Nama Outlet</label>
                <input type="text" name="outlet_name" value="{{ $outlet->name }}" required
                       class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ $outlet->phone }}" placeholder="08..."
                           class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Alamat Outlet</label>
                    <input type="text" name="address" value="{{ $outlet->address }}" placeholder="Jl. Raya No. 1..."
                           class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Catatan Kaki Struk (Receipt Footer)</label>
                <textarea name="receipt_footer" rows="2" class="w-full text-sm p-3 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">{{ $outlet->receipt_footer }}</textarea>
            </div>

            <div class="pt-3">
                <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-white bg-[#C2410C] hover:bg-[#9A3412] text-sm shadow-sm">
                    Simpan Pengaturan Profil
                </button>
            </div>
        </div>

        <!-- Tab 2: Operasional -->
        <div x-show="tab === 'operational'" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm space-y-4 max-w-2xl">
            <h3 class="font-bold text-base text-[#14110F] mb-3">Kebijakan Kasir & Pembulatan</h3>

            <div class="space-y-3">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                    <input type="checkbox" id="require_open_shift" name="require_open_shift" value="1" {{ $outlet->require_open_shift ? 'checked' : '' }}
                           class="rounded border-[#D3C9BD] text-[#C2410C]">
                    <label for="require_open_shift" class="text-xs font-medium text-[#14110F]">
                        <strong>Wajibkan Buka Sesi Kasir:</strong> Transaksi kasir tidak dapat dilakukan sebelum kasir membuka sesi kasir dengan modal kas awal.
                    </label>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                    <input type="checkbox" id="allow_negative_stock" name="allow_negative_stock" value="1" {{ $outlet->allow_negative_stock ? 'checked' : '' }}
                           class="rounded border-[#D3C9BD] text-[#C2410C]">
                    <label for="allow_negative_stock" class="text-xs font-medium text-[#14110F]">
                        <strong>Izinkan Stok Minus (Negative Stock):</strong> Tetap izinkan simpan transaksi meskipun stok bahan baku di sistem tidak mencukupi.
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Pembulatan Tunai</label>
                    <select name="rounding_mode" class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                        <option value="none" {{ $outlet->rounding_mode === 'none' ? 'selected' : '' }}>Tanpa Pembulatan</option>
                        <option value="nearest_100" {{ $outlet->rounding_mode === 'nearest_100' ? 'selected' : '' }}>Bulatkan ke Rp 100 Terdekat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Pajak Restoran (%)</label>
                    <input type="number" step="0.1" min="0" max="100" name="tax_percent" value="{{ (float) $outlet->tax_percent }}"
                           class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Biaya Layanan (%)</label>
                    <input type="number" step="0.1" min="0" max="100" name="service_charge_percent" value="{{ (float) $outlet->service_charge_percent }}"
                           class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Estimasi Komisi GoFood (%)</label>
                    <input type="number" step="0.1" min="0" max="100" name="commission_gofood" value="{{ $gofoodComm }}"
                           class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Estimasi Komisi ShopeeFood (%)</label>
                    <input type="number" step="0.1" min="0" max="100" name="commission_shopeefood" value="{{ $shopeefoodComm }}"
                           class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>
            </div>

            <!-- Keep profile fields hidden on other tabs -->
            <input type="hidden" name="outlet_name" value="{{ $outlet->name }}">

            <div class="pt-3">
                <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-white bg-[#C2410C] hover:bg-[#9A3412] text-sm shadow-sm">
                    Simpan Kebijakan Operasional
                </button>
            </div>
        </div>

        <!-- Tab 3: AI Vision & Privasi -->
        <div x-show="tab === 'ai'" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm space-y-4 max-w-2xl">
            <h3 class="font-bold text-base text-[#14110F] mb-3">Konfigurasi Vision AI & Privasi Pelanggan</h3>

            <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6] text-xs space-y-1">
                <p>• <strong>Protokol AI:</strong> {{ config('ai.protocol') }} (Vision Model: {{ config('ai.vision_model') }})</p>
                <p>• <strong>Status Kredensial:</strong> {{ config('ai.api_key') ? 'Terpasang di .env' : 'Simulasi/Mock Aman Aktif' }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Masa Retensi Screenshot (Hari)</label>
                    <input type="number" name="ai_retention_days" value="{{ $retentionDays }}" min="1"
                           class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>
            </div>

            <div class="pt-3">
                <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-white bg-[#C2410C] hover:bg-[#9A3412] text-sm shadow-sm">
                    Simpan Pengaturan AI
                </button>
            </div>
        </div>
    </form>

    <!-- Tab 4: Pengguna & Kasir -->
    <div x-show="tab === 'users'" class="space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="font-bold text-base text-[#14110F]">Manajemen Akun Kasir & Pemilik</h3>
            <button @click="showUserModal = true" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#C2410C] text-white">
                + Tambah Kasir Baru
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                    <tr>
                        <th class="px-3 py-2.5 rounded-l-lg">Nama Pengguna</th>
                        <th class="px-3 py-2.5">Email</th>
                        <th class="px-3 py-2.5">Peran (Role)</th>
                        <th class="px-3 py-2.5 text-center">Status</th>
                        <th class="px-3 py-2.5 text-right rounded-r-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E0D6]">
                    @foreach($users as $u)
                        <tr class="hover:bg-[#FAF8F5]">
                            <td class="px-3 py-3 font-semibold text-[#14110F]">{{ $u->name }}</td>
                            <td class="px-3 py-3 text-xs text-[#575047]">{{ $u->email }}</td>
                            <td class="px-3 py-3">
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ $u->isOwner() ? 'bg-[#FFF4EC] text-[#C2410C]' : 'bg-[#EFF6FF] text-[#2563EB]' }}">
                                    {{ $u->role->label() }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $u->is_active ? 'bg-[#ECFDF5] text-[#047857]' : 'bg-[#FEF2F2] text-[#DC2626]' }}">
                                    {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-right">
                                @if($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('settings.users.toggle', $u) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold {{ $u->is_active ? 'text-[#DC2626]' : 'text-[#047857]' }} hover:underline">
                                            {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Add User Modal -->
        <div x-show="showUserModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
            <div @click.away="showUserModal = false" class="bg-white w-full max-w-md rounded-2xl border border-[#E7E0D6] shadow-2xl p-6">
                <h3 class="text-base font-bold text-[#14110F] mb-4">Tambah Pengguna Baru</h3>
                <form method="POST" action="{{ route('settings.users.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Nama Lengkap</label>
                        <input type="text" name="name" required class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Email Login</label>
                        <input type="email" name="email" required class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Peran (Role)</label>
                            <select name="role" required class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                                <option value="cashier">Kasir</option>
                                <option value="owner">Pemilik (Owner)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">PIN Cepat (4-6 digit)</label>
                            <input type="password" name="pin" maxlength="6" placeholder="1234" class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Kata Sandi (Min 8 Karakter)</label>
                        <input type="password" name="password" required minlength="8" class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-[#E7E0D6]">
                        <button @click="showUserModal = false" type="button" class="px-4 py-2 rounded-xl border border-[#D3C9BD] text-xs font-semibold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[#C2410C] text-white text-xs font-bold hover:bg-[#9A3412]">Simpan Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tab 5: Audit Log -->
    <div x-show="tab === 'audit'" class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm space-y-3">
        <h3 class="font-bold text-base text-[#14110F]">Audit Trail Aktivitas Sistem</h3>
        <p class="text-xs text-[#575047]">Merekam seluruh aktivitas penting (perubahan harga, void transaksi, pergerakan stok, dan perubahan pengaturan).</p>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                    <tr>
                        <th class="px-3 py-2.5 rounded-l-lg">Waktu</th>
                        <th class="px-3 py-2.5">Pengguna</th>
                        <th class="px-3 py-2.5">Aksi</th>
                        <th class="px-3 py-2.5">Entitas</th>
                        <th class="px-3 py-2.5 rounded-r-lg">Ringkasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E0D6]">
                    @foreach($auditLogs as $log)
                        <tr class="hover:bg-[#FAF8F5]">
                            <td class="px-3 py-3 text-xs text-[#575047] whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-3 py-3 text-xs font-semibold text-[#14110F]">{{ $log->user?->name ?? 'Sistem' }}</td>
                            <td class="px-3 py-3 text-xs font-mono font-bold text-[#C2410C]">{{ $log->action }}</td>
                            <td class="px-3 py-3 text-xs text-[#575047]">{{ $log->entity_type }} #{{ $log->entity_id }}</td>
                            <td class="px-3 py-3 text-xs text-[#575047] max-w-sm truncate">{{ json_encode($log->summary) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
