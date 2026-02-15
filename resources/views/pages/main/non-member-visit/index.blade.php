@extends('layouts.app')
@section('title', 'Riwayat Kunjungan Non-Member')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-foreground">Riwayat Kunjungan Non-Member</h1>
                <p class="text-sm text-muted-foreground mt-1">Daftar semua kunjungan non-member</p>
            </div>
            <a href="{{ route('non-member-visit.create') }}" 
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors w-full sm:w-auto">
                <x-ui.icon name="plus" class="w-4 h-4 mr-2" />
                <span>Tambah Kunjungan</span>
            </a>
        </div>

        <div class="bg-card p-6 rounded-lg shadow-sm border border-border">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Nama</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Telepon</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Waktu Kunjungan</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Jumlah</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Metode</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Dibuat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visits as $visit)
                            <tr class="border-b border-border hover:bg-muted/30">
                                <td class="py-3 px-4 text-sm text-card-foreground">{{ $visit->name }}</td>
                                <td class="py-3 px-4 text-sm text-card-foreground">{{ $visit->phone ?? '-' }}</td>
                                <td class="py-3 px-4 text-sm text-card-foreground">{{ $visit->visit_time->format('d M Y H:i') }}</td>
                                <td class="py-3 px-4 text-sm text-card-foreground">Rp {{ number_format($visit->amount, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-sm text-card-foreground">{{ $visit->payment_method }}</td>
                                <td class="py-3 px-4 text-sm text-card-foreground">{{ $visit->creator->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-4 text-center text-sm text-muted-foreground">
                                    Belum ada kunjungan non-member
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $visits->links() }}
            </div>
        </div>
    </div>
@endsection

