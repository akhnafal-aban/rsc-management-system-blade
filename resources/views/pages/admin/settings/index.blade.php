@extends('layouts.app')
@section('title', 'Pengaturan')
@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Pengaturan Harga Membership</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola paket membership dan biaya tambahan dari satu tempat terpusat.
                </p>
            </div>
        </div>

        {{-- Paket Membership --}}
        <div class="bg-card border border-border rounded-lg p-6 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-card-foreground">Paket Membership</h2>
            </div>

            <div class="space-y-4">
                @forelse ($packages as $key => $package)
                    <div class="border border-border rounded-lg p-4">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Key Paket</p>
                                <p class="text-base font-semibold text-card-foreground">{{ $key }}</p>
                            </div>
                            <div class="flex flex-col md:items-end gap-1">
                                <p class="text-sm text-muted-foreground">Harga Final</p>
                                <p class="text-lg font-bold text-primary">
                                    Rp {{ number_format($package['final_price'], 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Harga dasar Rp {{ number_format($package['base_price'], 0, ',', '.') }}
                                    @if ($package['discount_percent'] > 0)
                                        • Diskon {{ $package['discount_percent'] }}%
                                    @endif
                                    • {{ $package['duration_days'] }} hari
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.settings.packages.update', ['packageKey' => $key]) }}"
                            class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="text-sm font-medium text-card-foreground mb-1 block">Label Paket</label>
                                <input type="text" name="label" value="{{ old('label', $package['label']) }}"
                                    class="mt-1 w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                                    required>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-card-foreground mb-1 block">Harga Dasar (Rp)</label>
                                <input type="number" name="price" min="0"
                                    value="{{ old('price', $package['base_price']) }}"
                                    class="mt-1 w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                                    required>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-card-foreground mb-1 block">Diskon (%)</label>
                                <input type="number" name="discount_percent" min="0" max="100"
                                    value="{{ old('discount_percent', $package['discount_percent']) }}"
                                    class="mt-1 w-full rounded-md border border-border bg-background px-3 py-2 text-sm">
                            </div>

                            <div>
                                <label class="text-sm font-medium text-card-foreground mb-1 block">Durasi (hari)</label>
                                <input type="number" name="duration_days" min="1"
                                    value="{{ old('duration_days', $package['duration_days']) }}"
                                    class="mt-1 w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                                    required>
                            </div>

                            <div class="md:col-span-4 flex justify-end mt-2">
                                <button type="submit"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-md bubblegum-button-primary text-chart-2-foreground text-sm">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-muted-foreground">
                        Belum ada paket membership terdaftar. Tambahkan paket baru di bawah.
                    </p>
                @endforelse
            </div>

            {{-- Tambah Paket Baru --}}
            <div class="border border-dashed border-border rounded-lg p-4 mt-4">
                <h3 class="text-sm font-semibold text-card-foreground mb-3">Tambah Paket Membership Baru</h3>

                <form method="POST" action="{{ route('admin.settings.packages.store') }}"
                    class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    @csrf

                    <div>
                        <label class="text-sm font-medium text-card-foreground mb-1 block">Key Paket</label>
                        <input type="text" name="key" value="{{ old('key') }}"
                            class="mt-1 w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                            placeholder="contoh: 6_month" required>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-card-foreground mb-1 block">Label</label>
                        <input type="text" name="label" value="{{ old('label') }}"
                            class="mt-1 w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                            placeholder="contoh: 6 Bulan" required>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-card-foreground mb-1 block">Harga Dasar (Rp)</label>
                        <input type="number" name="price" min="0" value="{{ old('price') }}"
                            class="mt-1 w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                            placeholder="contoh: 750000" required>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-card-foreground mb-1 block">Diskon (%)</label>
                        <input type="number" name="discount_percent" min="0" max="100"
                            value="{{ old('discount_percent', 0) }}"
                            class="mt-1 w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                            placeholder="opsional">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-card-foreground mb-1 block">Durasi (hari)</label>
                        <input type="number" name="duration_days" min="1" value="{{ old('duration_days') }}"
                            class="mt-1 w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                            placeholder="contoh: 180" required>
                    </div>

                    <div class="md:col-span-5 flex justify-end mt-2">
                        <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-md border border-border text-sm hover:bg-muted/60 transition-colors">
                            + Tambah Paket
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Biaya Non-Package --}}
        <div class="bg-card border border-border rounded-lg p-6 space-y-4">
            <h2 class="text-lg font-semibold text-card-foreground">Biaya Non-Package</h2>

            <form method="POST" action="{{ route('admin.settings.fees.update') }}"
                class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-sm font-medium text-card-foreground mb-1 block">Biaya Pendaftaran Member Baru
                        (Rp)</label>
                    <input type="number" name="new_member_fee" min="0"
                        value="{{ old('new_member_fee', $fees['new_member_fee'] ?? 0) }}"
                        class="mt-1 w-full rounded-md border border-border bg-background px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="text-sm font-medium text-card-foreground mb-1 block">Kunjungan Harian Non-Member
                        (Rp)</label>
                    <input type="number" name="non_member_visit_daily" min="0"
                        value="{{ old('non_member_visit_daily', $fees['non_member_visit_daily'] ?? 0) }}"
                        class="mt-1 w-full rounded-md border border-border bg-background px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="text-sm font-medium text-card-foreground mb-1 block">Berenang Non-Member (Rp)</label>
                    <input type="number" name="non_member_swim" min="0"
                        value="{{ old('non_member_swim', $fees['non_member_swim'] ?? 0) }}"
                        class="mt-1 w-full rounded-md border border-border bg-background px-3 py-2 text-sm">
                </div>

                <div class="md:col-span-3 flex justify-end mt-2">
                    <button type="submit"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-md bubblegum-button-primary text-chart-2-foreground text-sm">
                        Simpan Biaya
                    </button>
                </div>
            </form>

            <p class="text-xs text-muted-foreground">
                Semua harga di atas digunakan secara otomatis di seluruh sistem (registrasi member, perpanjangan,
                dan kunjungan non-member).
            </p>
        </div>
    </div>
@endsection
