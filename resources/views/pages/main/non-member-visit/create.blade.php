@extends('layouts.app')
@section('title', 'Check-in Non-Member')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-foreground">Check-in Non-Member</h1>
                <p class="text-sm text-muted-foreground mt-1">Catat kunjungan non-member dengan biaya Rp 20.000</p>
            </div>
            <a href="{{ route('non-member-visit.index') }}" 
                class="inline-flex items-center justify-center px-4 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors w-full sm:w-auto">
                <x-ui.icon name="chevron-left" class="w-4 h-4 mr-2" />
                <span>Kembali</span>
            </a>
        </div>

        <form action="{{ route('non-member-visit.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-card p-6 rounded-lg shadow-sm border border-border space-y-4">
                <h2 class="text-lg font-semibold text-card-foreground">Informasi Pengunjung</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-card-foreground mb-2">Nama *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="Masukkan nama">
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-card-foreground mb-2">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="08123456789">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label for="email" class="block text-sm font-medium text-card-foreground mb-2">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="contoh@email.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="bg-card p-6 rounded-lg shadow-sm border border-border space-y-4">
                <h2 class="text-lg font-semibold text-card-foreground">Informasi Pembayaran</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-card-foreground mb-2">Jumlah (Rp)</label>
                        <input type="number" id="amount" name="amount" value="{{ old('amount', 20000) }}" min="0" step="1000"
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="20000">
                        @error('amount')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-muted-foreground">Default: Rp 20.000</p>
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-card-foreground mb-2">Metode Pembayaran *</label>
                        <select id="payment_method" name="payment_method" required
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                            <option value="CASH" {{ old('payment_method') == 'CASH' ? 'selected' : '' }}>Cash</option>
                            <option value="TRANSFER" {{ old('payment_method') == 'TRANSFER' ? 'selected' : '' }}>Transfer</option>
                            <option value="EWALLET" {{ old('payment_method') == 'EWALLET' ? 'selected' : '' }}>E-Wallet</option>
                        </select>
                        @error('payment_method')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-card-foreground mb-2">Catatan</label>
                        <textarea id="notes" name="notes" rows="3"
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="Tambahkan catatan jika diperlukan...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('non-member-visit.index') }}"
                    class="inline-flex items-center justify-center px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center px-6 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                    <x-ui.icon name="check" class="w-4 h-4 mr-2" />
                    Simpan
                </button>
            </div>
        </form>
    </div>
@endsection

