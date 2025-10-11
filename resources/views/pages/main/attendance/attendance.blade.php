@extends('layouts.app')
@section('title', 'Manajemen Absensi')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
            <div class="flex space-x-3 mb-3 sm:mb-0">
                <a href="{{ route('attendance.index') }}"
                    class="inline-flex items-center px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="refresh" class="w-4 h-4 mr-2" />
                    <span>Perbarui</span>
                </a>
                <a href="{{ route('attendance.export') }}"
                    class="inline-flex items-center px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="download" class="w-4 h-4 mr-2" />
                    <span>Ekspor</span>
                </a>
            </div>
            <div>
                <a href="{{ route('attendance.check-in') }}"
                    class="inline-flex items-center px-6 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                    <span>Check-In Member</span>
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

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @include('components.partials.stat-card', [
                'title' => 'Total Check-in Hari Ini',
                'value' => $stats['total_checkins'],
                'icon' => 'user-check',
            ])
            @include('components.partials.stat-card', [
                'title' => 'Member Aktif',
                'value' => $stats['active_members'],
                'icon' => 'users',
            ])
            @include('components.partials.stat-card', [
                'title' => 'Sudah Check-in',
                'value' => $stats['checked_in_today'],
                'icon' => 'trending-up',
            ])
        </div>

        <!-- Today's Attendance Table -->
        <div class="bg-card text-card-foreground rounded-lg shadow-sm border border-border p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                <h3 class="text-lg font-semibold text-card-foreground">Absensi Hari Ini</h3>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <!-- Search Input -->
                    <form method="GET" action="{{ route('attendance.index') }}" class="flex gap-2">
                        <div class="relative ">
                            <input type="text" name="search" value="{{ $search }}"
                                class="pl-10 pr-10 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                                placeholder="Cari nama atau ID member..." />
                            <x-ui.icon name="search"
                                class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 transform -translate-y-1/2" />
                        </div>
                        <!-- Status Filter -->
                        <select name="status"
                            class="px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                            <option value="">Semua Status</option>
                            <option value="checkin" {{ $statusFilter === 'checkin' ? 'selected' : '' }}>Check In</option>
                            <option value="checkout" {{ $statusFilter === 'checkout' ? 'selected' : '' }}>Check Out
                            </option>
                        </select>
                        <button type="submit"
                            class="px-4 py-2 bg-background border border-border text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                            Filter
                        </button>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
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
                                Waktu Check-in</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Waktu Check-out</th>
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
                    <tbody id="attendance-table-body" class="bg-card divide-y divide-border">
                        @foreach ($attendances as $attendance)
                            <tr class="hover:bg-muted/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-card-foreground">
                                    {{ $attendance->member->member_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">
                                    {{ $attendance->member->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">
                                    {{ $attendance->check_in_time->format('H:i:s') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">
                                    {{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">
                                    {{ $attendance->creator->name ?? 'System' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($attendance->check_out_time)
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-chart-1/20 text-chart-1">Check
                                            Out</span>
                                    @else
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-chart-2/20 text-chart-2">Check
                                            In</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                        @if ($attendances->isEmpty())
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-muted-foreground">
                                    <x-ui.icon name="user-check" class="w-12 h-12 mx-auto mb-2" />
                                    <p>Tidak ada data absensi ditemukan.</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($attendances->hasPages())
                <div class="mt-6">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
