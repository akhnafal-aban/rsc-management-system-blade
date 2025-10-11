@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Dashboard</h1>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            @foreach ($stats ?? [] as $stat)
                @include('components.partials.stat-card', [
                    'title' => $stat['title'] ?? '',
                    'value' => $stat['value'] ?? '',
                    'change' => $stat['change'] ?? null,
                    'icon' => $stat['icon'] ?? null,
                ])
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6 h-30">
            @include('components.partials.stat-card', [
                'title' => 'Member Aktif',
                'value' => '1,234 dummy',
                'change' => null,
                'icon' => null,
            ])
            @include('components.partials.stat-card', [
                'title' => 'Check-in Hari Ini',
                'value' => '89 dummy',
                'change' => null,
                'icon' => null,
            ])
            @include('components.partials.stat-card', [
                'title' => 'Rata-rata Mingguan',
                'value' => '456 dummy',
                'change' => null,
                'icon' => null,
            ])
            @include('components.partials.stat-card', [
                'title' => 'Pendapatan Bulanan',
                'value' => 'Rp. 24,560,000,00',
                'change' => null,
                'icon' => null,
            ])
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <div class="bg-card rounded-lg shadow-sm border border-border p-6">
                <h3 class="text-lg font-semibold text-card-foreground mb-2">Tren Kehadiran Mingguan</h3>
                <p class="text-sm text-muted-foreground mb-4">Check-in dalam 7 hari terakhir</p>
                <div class="h-80 flex items-center justify-center text-muted-foreground">
                    <p>Chart akan ditampilkan di sini</p>
                </div>
            </div>

            <div class="bg-card rounded-lg shadow-sm border border-border p-6">
                <h3 class="text-lg font-semibold text-card-foreground mb-2">Distribusi Member</h3>
                <p class="text-sm text-muted-foreground mb-4">Sebaran member berdasarkan aktivitas</p>
                <div class="h-80 flex items-center justify-center text-muted-foreground">
                    <p>Chart akan ditampilkan di sini</p>
                </div>
            </div>

            <div class="bg-card rounded-lg shadow-sm border border-border p-6">
                <h3 class="text-lg font-semibold text-card-foreground mb-2">Aktivitas Hari Ini</h3>
                <p class="text-sm text-muted-foreground mb-4">Kehadiran saat ini vs target</p>
                <div class="h-80 flex items-center justify-center text-muted-foreground">
                    <p>Chart akan ditampilkan di sini</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-card rounded-lg shadow-sm border border-border p-6">
                <h3 class="text-lg font-semibold text-card-foreground mb-4">Check-in Terbaru</h3>
                <div class="space-y-3">
                    @foreach ($activities ?? [] as $activity)
                        <div class="flex items-center justify-between p-3 hover:bg-muted/50 rounded-lg transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-chart-1/20 rounded-full flex items-center justify-center">
                                    {{-- <x-icon name="user-check" class="w-4 h-4 text-chart-1" /> --}}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-card-foreground">{{ $activity['name'] ?? '' }}</p>
                                    <p class="text-xs text-muted-foreground">{{ $activity['time'] ?? '' }}</p>
                                </div>
                            </div>
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full {{ ($activity['type'] ?? '') === 'Check-in' ? 'bg-chart-2/20 text-chart-2' : 'bg-destructive/20 text-destructive' }}">{{ $activity['type'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-card rounded-lg shadow-sm border border-border p-6">
                <h3 class="text-lg font-semibold text-card-foreground mb-4">Aksi Cepat</h3>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        class="p-4 border-2 border-dashed border-border rounded-lg hover:border-chart-1 hover:bg-chart-1/10 transition-colors">
                        <span class="icon-user-check w-8 h-8 text-muted-foreground mx-auto mb-2"></span>
                        <span class="text-sm font-medium text-card-foreground">Check-in Member</span>
                    </button>
                    <button
                        class="p-4 border-2 border-dashed border-border rounded-lg hover:border-chart-2 hover:bg-chart-2/10 transition-colors">
                        <span class="icon-users w-8 h-8 text-muted-foreground mx-auto mb-2"></span>
                        <span class="text-sm font-medium text-card-foreground">Tambah Member</span>
                    </button>
                    <button
                        class="p-4 border-2 border-dashed border-border rounded-lg hover:border-chart-3 hover:bg-chart-3/10 transition-colors">
                        <span class="icon-trending-up w-8 h-8 text-muted-foreground mx-auto mb-2"></span>
                        <span class="text-sm font-medium text-card-foreground">Lihat Laporan</span>
                    </button>
                    <button
                        class="p-4 border-2 border-dashed border-border rounded-lg hover:border-chart-4 hover:bg-chart-4/10 transition-colors">
                        <span class="icon-dollar-sign w-8 h-8 text-muted-foreground mx-auto mb-2"></span>
                        <span class="text-sm font-medium text-card-foreground">Pembayaran</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
