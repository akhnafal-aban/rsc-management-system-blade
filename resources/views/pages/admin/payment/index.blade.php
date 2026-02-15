@extends('layouts.app')
@section('title', 'Riwayat Pembayaran')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-foreground">Riwayat Pembayaran</h1>
                <p class="text-sm text-muted-foreground mt-1">Semua riwayat pemasukan dari membership, perpanjangan, dan kunjungan non-member</p>
            </div>
        </div>

        <div class="bg-card p-6 rounded-lg shadow-sm border border-border">
            <form method="GET" action="{{ route('admin.payment.index') }}" class="mb-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-card-foreground mb-2">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-card-foreground mb-2">Tanggal Akhir</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                            Filter
                        </button>
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Tanggal</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Jenis</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Member/Non-Member</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Jumlah</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Metode</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="border-b border-border hover:bg-muted/30">
                                <td class="py-3 px-4 text-sm text-card-foreground">{{ $payment['created_at']->format('d M Y H:i') }}</td>
                                <td class="py-3 px-4 text-sm text-card-foreground">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $payment['type'] === 'member' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $payment['type_label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-sm text-card-foreground">{{ $payment['member_name'] }}</td>
                                <td class="py-3 px-4 text-sm text-card-foreground font-semibold">Rp {{ number_format($payment['amount'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-sm text-card-foreground">{{ $payment['payment_method'] }}</td>
                                <td class="py-3 px-4 text-sm text-card-foreground">{{ $payment['notes'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-4 text-center text-sm text-muted-foreground">
                                    Tidak ada data pembayaran
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
@endsection

