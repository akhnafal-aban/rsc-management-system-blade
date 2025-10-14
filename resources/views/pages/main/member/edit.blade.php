@extends('layouts.app')
@section('title', 'Edit Member - ' . $member->name)
@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Edit Member</h1>
                <p class="text-muted-foreground mt-1">{{ $member->name }} - {{ $member->member_code }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('member.show', $member) }}" 
                    class="inline-flex items-center px-4 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="chevron-left" class="w-4 h-4 mr-2" />
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-destructive/10 border border-destructive/20 rounded-lg p-4">
                <div class="flex">
                    <x-ui.icon name="alert-circle" class="w-5 h-5 text-destructive mr-3 mt-0.5" />
                    <div>
                        <h3 class="text-sm font-medium text-destructive">Terdapat kesalahan:</h3>
                        <ul class="mt-2 text-sm text-destructive list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('member.update', $member) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- Grid Utama -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Lengkap -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-card-foreground mb-2">
                            Nama Lengkap <span class="text-destructive">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $member->name) }}" required
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground 
                            placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="Masukkan nama lengkap">
                    </div>
            
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-card-foreground mb-2">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $member->email) }}"
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground 
                            placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="contoh@email.com">
                    </div>
                </div>
            
                <!-- Bagian Nomor Telepon + Metode Pembayaran & Perpanjang Membership -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kiri: Telepon dan Pembayaran -->
                    <div class="space-y-6">
                        <!-- Nomor Telepon -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-card-foreground mb-2">Nomor Telepon</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $member->phone) }}"
                                class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground 
                                placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                                placeholder="08123456789">
                        </div>
            
                        <!-- Metode Pembayaran -->
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-card-foreground mb-2">
                                Metode Pembayaran <span class="text-destructive">*</span>
                            </label>
                            <select id="payment_method" name="payment_method" required
                                class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground 
                                focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                                <option value="">Pilih metode pembayaran</option>
                                <option value="CASH" {{ old('payment_method', $member->payments->first()?->method) == 'CASH' ? 'selected' : '' }}>Tunai (CASH)</option>
                                <option value="TRANSFER" {{ old('payment_method', $member->payments->first()?->method) == 'TRANSFER' ? 'selected' : '' }}>Transfer Bank</option>
                                <option value="EWALLET" {{ old('payment_method', $member->payments->first()?->method) == 'EWALLET' ? 'selected' : '' }}>E-Wallet</option>
                            </select>
                        </div>
                    </div>
            
                    <!-- Kanan: Perpanjang Membership -->
                    <div>
                        <label for="membership_duration" class="block text-sm font-medium text-card-foreground mb-2">
                            Perpanjang Membership <span class="text-destructive">*</span>
                        </label>
            
                        <div class="bg-muted/30 border border-border rounded-lg p-3 mb-3">
                            <p class="text-sm text-muted-foreground">
                                <strong>Masa Aktif Saat Ini:</strong>
                                {{ $member->exp_date ? \Carbon\Carbon::parse($member->exp_date)->format('d M Y') : 'Tidak ada' }}
                            </p>
                            <p class="text-xs text-muted-foreground mt-1">
                                Durasi yang dipilih akan ditambahkan ke masa aktif saat ini.
                            </p>
                        </div>
            
                        <select id="membership_duration" name="membership_duration" required
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground 
                            focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                            <option value="">Pilih durasi perpanjangan</option>
                            <option value="1" {{ old('membership_duration') == '1' ? 'selected' : '' }}>1 Bulan - Rp 150.000</option>
                            <option value="3" {{ old('membership_duration') == '3' ? 'selected' : '' }}>3 Bulan - Rp 400.000</option>
                            <option value="6" {{ old('membership_duration') == '6' ? 'selected' : '' }}>6 Bulan - Rp 750.000</option>
                            <option value="12" {{ old('membership_duration') == '12' ? 'selected' : '' }}>12 Bulan - Rp 1.400.000</option>
                        </select>
                    </div>
                </div>
            
                <!-- Catatan Pembayaran -->
                <div>
                    <label for="payment_notes" class="block text-sm font-medium text-card-foreground mb-2">Catatan Pembayaran</label>
                    <textarea id="payment_notes" name="payment_notes" rows="3"
                        class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground 
                        placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                        placeholder="Catatan untuk pembayaran perpanjangan membership (opsional)">{{ old('payment_notes') }}</textarea>
                </div>
            </div>
            
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('member.show', $member) }}" 
                    class="px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                    <x-ui.icon name="edit" class="w-4 h-4 mr-2 inline" />
                    Perpanjang Membership
                </button>
            </div>
        </form>
    </div>
@endsection