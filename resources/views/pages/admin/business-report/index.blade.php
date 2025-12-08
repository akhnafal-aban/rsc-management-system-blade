@extends('layouts.app')
@section('title', 'Laporan Bisnis')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-foreground">Laporan Bisnis</h1>
                <p class="text-sm text-muted-foreground mt-1">Ringkasan pemasukan, kunjungan, konversi, dan fasilitas</p>
            </div>
            <form method="GET" action="{{ route('admin.business-report.index') }}" class="flex flex-col sm:flex-row gap-3">
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent w-full sm:w-auto">
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent w-full sm:w-auto">
                <button type="submit"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                    Terapkan
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-partials.stat-card title="Total Pemasukan" value="Rp {{ number_format($report['revenue']['total'], 0, ',', '.') }}" />
            <x-partials.stat-card title="Pemasukan Member" value="Rp {{ number_format($report['revenue']['member_payments'], 0, ',', '.') }}" />
            <x-partials.stat-card title="Pemasukan Non-Member" value="Rp {{ number_format($report['revenue']['non_member_payments'], 0, ',', '.') }}" />
            <x-partials.stat-card title="Total Kunjungan" value="{{ $report['visits']['total'] }}" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-card border border-border rounded-lg p-4">
                <h3 class="text-sm font-semibold text-card-foreground mb-3">Kunjungan Harian</h3>
                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @foreach ($report['visits']['daily'] as $daily)
                        <div class="flex items-center justify-between text-sm border-b border-border py-2">
                            <span class="text-card-foreground">{{ \Carbon\Carbon::parse($daily['date'])->format('d M') }}</span>
                            <div class="text-right mr-4">
                                <p class="text-card-foreground font-semibold">{{ $daily['total'] }} total</p>
                                <p class="text-xs text-muted-foreground">Member: {{ $daily['member_visits'] }} | Non-member: {{ $daily['non_member_visits'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-card border border-border rounded-lg p-4">
                <h3 class="text-sm font-semibold text-card-foreground mb-3">Konversi Non-Member ke Member</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-card-foreground">{{ $report['conversions']['non_member_to_member'] }}</p>
                        <p class="text-sm text-muted-foreground">Konversi pada periode ini</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-semibold text-card-foreground">{{ number_format($report['conversions']['conversion_rate'], 1) }}%</p>
                        <p class="text-sm text-muted-foreground">Conversion Rate</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-card border border-border rounded-lg p-4">
                <h3 class="text-sm font-semibold text-card-foreground mb-3">Pendapatan per Kategori</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-card-foreground">Pendaftaran Membership</span>
                        <span class="font-semibold text-card-foreground">Rp {{ number_format($report['revenue']['by_category']['membership_registration'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-card-foreground">Perpanjangan Membership</span>
                        <span class="font-semibold text-card-foreground">Rp {{ number_format($report['revenue']['by_category']['membership_extension'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-card-foreground">Kunjungan Non-Member</span>
                        <span class="font-semibold text-card-foreground">Rp {{ number_format($report['revenue']['by_category']['non_member_visits'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-card border border-border rounded-lg p-4">
                <h3 class="text-sm font-semibold text-card-foreground mb-3">Ringkasan Member</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-card-foreground">{{ $report['members']['total'] }}</p>
                        <p class="text-sm text-muted-foreground">Member baru pada periode</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-semibold text-card-foreground">{{ $report['members']['active'] }}</p>
                        <p class="text-sm text-muted-foreground">Member aktif</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

