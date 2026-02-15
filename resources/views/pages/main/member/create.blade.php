@extends('layouts.app')
@section('title', 'Tambah Member')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-foreground">Tambah Member Baru</h1>
            </div>
            <a href="{{ route('member.index') }}" 
                class="inline-flex items-center justify-center px-4 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors w-full sm:w-auto">
                <x-ui.icon name="chevron-left" class="w-4 h-4 mr-2" />
                <span>Kembali</span>
            </a>
        </div>


        <form action="{{ route('member.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="bg-card p-4 sm:p-6 rounded-lg shadow-sm border border-border">
                <h2 class="text-lg font-semibold text-card-foreground mb-4">Informasi Member</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-card-foreground mb-2">Nama Lengkap *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="Masukkan nama lengkap">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-card-foreground mb-2">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="contoh@email.com">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-card-foreground mb-2">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="08123456789">
                    </div>

                    <div>
                        <label for="package_key" class="block text-sm font-medium text-card-foreground mb-2">Paket Membership *</label>
                        <select id="package_key" name="package_key" required
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                            <option value="">Pilih paket membership</option>
                            @foreach ($packages as $key => $package)
                                <option value="{{ $key }}" {{ old('package_key') == $key ? 'selected' : '' }}>
                                    {{ $package['label'] ?? $key }}
                                    - Rp {{ number_format($package['final_price'], 0, ',', '.') }}
                                    ({{ $package['duration_days'] }} hari)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-card-foreground mb-2">Metode Pembayaran *</label>
                        <select id="payment_method" name="payment_method" required
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                            <option value="">Pilih metode pembayaran</option>
                            <option value="CASH" {{ old('payment_method') == 'CASH' ? 'selected' : '' }}>Tunai (CASH)</option>
                            <option value="TRANSFER" {{ old('payment_method') == 'TRANSFER' ? 'selected' : '' }}>Transfer Bank</option>
                            <option value="EWALLET" {{ old('payment_method') == 'EWALLET' ? 'selected' : '' }}>E-Wallet</option>
                        </select>
                    </div>

                </div>

                <div class="mt-6">
                    <label for="payment_notes" class="block text-sm font-medium text-card-foreground mb-2">Catatan Pembayaran</label>
                    <textarea id="payment_notes" name="payment_notes" rows="3"
                        class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                        placeholder="Catatan tambahan untuk pembayaran (opsional)">{{ old('payment_notes') }}</textarea>
                </div>
            </div>

            <!-- Biaya Pendaftaran dan Membership -->
            <div class="bg-card p-4 sm:p-6 rounded-lg shadow-sm border border-border">
                <h2 class="text-lg font-semibold text-card-foreground mb-4">Rincian Biaya</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-border">
                        <span class="text-sm text-card-foreground">Biaya Pendaftaran Member Baru</span>
                        <span class="font-semibold text-card-foreground">
                            Rp {{ number_format($packages ? app(\App\Services\MemberService::class)->getRegistrationFee() : 0, 0, ',', '.') }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-border">
                        <span class="text-sm text-card-foreground">Biaya Membership</span>
                        <span class="font-semibold text-card-foreground" id="membership-cost">Rp 0</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-3 bg-muted/50 rounded-lg px-3">
                        <span class="text-base font-semibold text-card-foreground">Total Biaya</span>
                        <span class="text-lg font-bold text-primary" id="total-cost">
                            Rp {{ number_format(app(\App\Services\MemberService::class)->getRegistrationFee(), 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        <x-ui.icon name="info" class="w-4 h-4 inline mr-1" />
                        <strong>Informasi:</strong> Setiap member baru dikenakan biaya pendaftaran
                        Rp {{ number_format(app(\App\Services\MemberService::class)->getRegistrationFee(), 0, ',', '.') }}
                        + biaya membership sesuai paket yang dipilih.
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
                <a href="{{ route('member.index') }}" 
                    class="w-full sm:w-auto px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors text-center">
                    Batal
                </a>
                <button type="submit"
                    class="w-full sm:w-auto px-6 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors flex items-center justify-center">
                    <x-ui.icon name="plus" class="w-4 h-4 mr-2" />
                    Tambah Member
                </button>
            </div>
        </form>
    </div>

    <script>
        const packages = @json($packages);
        const registrationFee = {{ app(\App\Services\MemberService::class)->getRegistrationFee() }};

        function updateCosts() {
            const select = document.getElementById('package_key');
            const selectedKey = select.value;
            const membershipCostElement = document.getElementById('membership-cost');
            const totalCostElement = document.getElementById('total-cost');

            if (selectedKey && packages[selectedKey]) {
                const membershipCost = packages[selectedKey].final_price;
                const totalCost = registrationFee + membershipCost;

                membershipCostElement.textContent = `Rp ${membershipCost.toLocaleString('id-ID')}`;
                totalCostElement.textContent = `Rp ${totalCost.toLocaleString('id-ID')}`;
            } else {
                membershipCostElement.textContent = 'Rp 0';
                totalCostElement.textContent = `Rp ${registrationFee.toLocaleString('id-ID')}`;
            }
        }

        document.getElementById('package_key').addEventListener('change', updateCosts);
        document.addEventListener('DOMContentLoaded', updateCosts);
    </script>
@endsection