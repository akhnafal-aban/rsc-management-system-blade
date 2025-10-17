@extends('layouts.app')
@section('title', 'Manajemen Absensi')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-row items-center justify-between mb-4 flex-wrap gap-3">
            <h1 class="text-2xl font-bold text-foreground">Absensi</h1>

            <div class="flex flex-wrap justify-end gap-3">
                {{-- Tombol Perbarui --}}
                <a href="{{ route('attendance.index') }}"
                    class="inline-flex items-center justify-center px-3 sm:px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="refresh" class="w-4 h-4 mr-0 sm:mr-2" />
                    <span class="hidden sm:inline">Perbarui</span>
                </a>

                {{-- Tombol Ekspor --}}
                <a href="{{ route('attendance.export', ['date' => $dateFilter]) }}"
                    class="inline-flex items-center justify-center px-3 sm:px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="download" class="w-4 h-4 mr-0 sm:mr-2" />
                    <span class="hidden sm:inline">Ekspor</span>
                </a>

                {{-- Tombol Check-In Member --}}
                <a href="{{ route('attendance.check-in') }}"
                    class="inline-flex items-center justify-center px-3 sm:px-6 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                    <x-ui.icon name="log-in" class="w-5 h-5 mr-0 sm:mr-2" />
                    <span class="hidden sm:inline">Check-In Member</span>
                </a>
            </div>
        </div>


        <!-- Success/Error Messages -->
        @if (session('success'))
            <div id="alert-message" class="bg-green-500 text-white p-4 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div id="alert-message" class="bg-red-500 text-white p-4 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <script>
            // otomatis hilang setelah 5 detik (5000 ms)
            document.addEventListener('DOMContentLoaded', () => {
                const alert = document.getElementById('alert-message');
                if (alert) {
                    setTimeout(() => {
                        alert.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                        setTimeout(() => alert.remove(), 500); // hapus dari DOM setelah fade-out
                    }, 5000);
                }
            });
        </script>

        <!-- Today's Attendance Table -->
        <div class="bg-card text-card-foreground rounded-lg shadow-sm border border-border p-4 sm:p-6">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
                <h3 class="text-lg font-semibold text-card-foreground">
                    Absensi
                    {{ $dateFilter === now()->format('Y-m-d') ? 'Hari Ini' : \Carbon\Carbon::parse($dateFilter)->format('d M Y') }}
                </h3>

                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                    {{-- Quick Date Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <a href="{{ route('attendance.index', ['date' => now()->format('Y-m-d')] + request()->except('date')) }}"
                            class="inline-flex items-center justify-center w-full sm:w-auto px-3 py-2 text-sm font-medium
        bg-{{ $dateFilter === now()->format('Y-m-d') ? 'primary' : 'background' }}
        text-{{ $dateFilter === now()->format('Y-m-d') ? 'primary-foreground' : 'foreground' }}
        border border-border rounded-md hover:bg-muted/50 transition-colors">
                            Hari Ini
                        </a>

                        <a href="{{ route('attendance.index', ['date' => now()->subDay()->format('Y-m-d')] + request()->except('date')) }}"
                            class="inline-flex items-center justify-center w-full sm:w-auto px-3 py-2 text-sm font-medium
        bg-{{ $dateFilter === now()->subDay()->format('Y-m-d') ? 'primary' : 'background' }}
        text-{{ $dateFilter === now()->subDay()->format('Y-m-d') ? 'primary-foreground' : 'foreground' }}
        border border-border rounded-md hover:bg-muted/50 transition-colors">
                            Kemarin
                        </a>
                    </div>


                    {{-- Search & Filter --}}
                    <form method="GET" action="{{ route('attendance.index') }}"
                        class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                        {{-- Search --}}
                        <div class="relative w-full sm:max-w-xs">
                            <input type="text" name="search" value="{{ $search }}"
                                class="w-full pl-10 pr-10 py-2 bg-input border border-border rounded-lg 
                                text-foreground placeholder:text-muted-foreground
                                focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent text-sm"
                                placeholder="Cari nama atau ID member..." />
                            <x-ui.icon name="search"
                                class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 transform -translate-y-1/2" />
                        </div>

                        {{-- Date Filter --}}
                        <input type="date" name="date" value="{{ $dateFilter }}"
                            class="px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring w-full sm:w-auto text-sm" />

                        {{-- Status Filter --}}
                        <select name="status"
                            class="px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring w-full sm:w-auto text-sm">
                            <option value="">Semua Status</option>
                            <option value="checkin" {{ $statusFilter === 'checkin' ? 'selected' : '' }}>Check In</option>
                            <option value="checkout" {{ $statusFilter === 'checkout' ? 'selected' : '' }}>Check Out
                            </option>
                        </select>

                        <button type="submit"
                            class="px-4 py-2 bg-background border border-border text-foreground rounded-lg hover:bg-muted/50 transition-colors w-full sm:w-auto text-sm">
                            Filter
                        </button>
                    </form>
                </div>
            </div>

            {{-- Table Section --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Member ID</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Nama</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Check-in</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Check-out</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Staff</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        @foreach ($attendances as $attendance)
                            <tr class="hover:bg-muted/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium">{{ $attendance->member->member_code }}</td>
                                <td class="px-6 py-4 text-sm">{{ $attendance->member->name }}</td>
                                <td class="px-6 py-4 text-sm">{{ $attendance->check_in_time->format('H:i:s') }}</td>
                                <td class="px-6 py-4 text-sm">
                                    {{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $attendance->creator->name ?? 'System' }}</td>
                                <td class="px-6 py-4">
                                    @if ($attendance->check_out_time)
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-chart-1/20 text-chart-1">
                                            Check Out
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-chart-2/20 text-chart-2">
                                            Check In
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if (!$attendance->check_out_time)
                                        <form method="POST" action="{{ route('attendance.checkout') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                                            <button type="submit"
                                                class="px-3 py-1 text-xs bg-chart-1 text-chart-1-foreground rounded-lg hover:bg-chart-1/90 transition-colors"
                                                onclick="return confirm('Apakah Anda yakin ingin check-out member ini?')">
                                                Check Out
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-muted-foreground">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card View --}}
            <div class="sm:hidden space-y-3">
                @forelse ($attendances as $attendance)
                    <div class="border border-border rounded-lg p-4 bg-background shadow-sm">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-semibold">{{ $attendance->member->name }}</span>
                            @if ($attendance->check_out_time)
                                <span class="text-xs px-2 py-1 rounded-full bg-chart-1/20 text-chart-1">Check Out</span>
                            @else
                                <span class="text-xs px-2 py-1 rounded-full bg-chart-2/20 text-chart-2">Check In</span>
                            @endif
                        </div>
                        <p class="text-xs text-muted-foreground mb-1">ID: {{ $attendance->member->member_code }}</p>
                        <p class="text-xs mb-1">Check-in: {{ $attendance->check_in_time->format('H:i:s') }}</p>
                        <p class="text-xs mb-1">Check-out:
                            {{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : '-' }}</p>
                        <p class="text-xs text-muted-foreground mb-3">Staff: {{ $attendance->creator->name ?? 'System' }}
                        </p>

                        @if (!$attendance->check_out_time)
                            <form method="POST" action="{{ route('attendance.checkout') }}">
                                @csrf
                                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                                <button type="submit"
                                    class="w-full text-xs py-2 rounded-md bg-chart-1 text-chart-1-foreground hover:bg-chart-1/90 transition-colors"
                                    onclick="return confirm('Check-out member ini?')">
                                    Check Out
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-muted-foreground">Selesai</span>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-muted-foreground py-12">
                        <x-ui.icon name="user-check" class="w-10 h-10 mx-auto mb-2" />
                        <p class="text-sm">Tidak ada data absensi ditemukan.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($attendances->hasPages())
                <div class="mt-6">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
