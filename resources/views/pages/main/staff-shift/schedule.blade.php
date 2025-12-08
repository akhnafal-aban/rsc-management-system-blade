@extends('layouts.app')
@section('title', 'Jadwal Shift Saya')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-foreground">Jadwal Shift Saya</h1>
                <p class="text-sm text-muted-foreground mt-1">Lihat jadwal shift kerja Anda</p>
            </div>
        </div>

        <div class="bg-card p-6 rounded-lg shadow-sm border border-border">
            <form method="GET" action="{{ route('staff.shift.schedule') }}" class="mb-4">
                <div class="flex gap-3">
                    <input type="month" name="month" value="{{ $month }}" 
                        class="px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                    <button type="submit"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                        Filter
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Tanggal</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Shift</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-card-foreground">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr class="border-b border-border hover:bg-muted/30">
                                <td class="py-3 px-4 text-sm text-card-foreground">{{ $schedule->schedule_date->format('d M Y') }}</td>
                                <td class="py-3 px-4 text-sm text-card-foreground">{{ $schedule->shift_type->label() }}</td>
                                <td class="py-3 px-4 text-sm text-card-foreground">{{ $schedule->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 px-4 text-center text-sm text-muted-foreground">
                                    Belum ada jadwal untuk bulan ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

