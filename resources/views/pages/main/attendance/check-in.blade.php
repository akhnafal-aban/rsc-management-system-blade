@extends('layouts.app')
@section('title', 'Check In Member')
@section('content')
<!-- Success/Error Messages -->
@if (session('success'))
    <div id="alert-message" class="bg-green-500 text-white p-4 rounded-lg mb-6">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div id="alert-message" class="bg-red-500 text-white p-4 rounded-lg mb-6">
        {{ session('error') }}
    </div>
@endif

<!-- Check-In Member Table -->
<div class="bg-card text-card-foreground rounded-lg shadow-sm border border-border p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h3 class="text-lg font-semibold text-card-foreground">Check In Member</h3>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <!-- Search Form -->
            <form method="GET" action="{{ route('attendance.check-in') }}" class="flex gap-2">
                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari nama atau ID member..."
                        class="pl-10 pr-10 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                    />
                    <x-ui.icon name="search"
                        class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 transform -translate-y-1/2" />
                </div>
                <button
                    type="submit"
                    class="px-4 py-2 bg-background border border-border text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    Cari
                </button>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-border">
            <thead class="bg-muted/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Member ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tanggal Expired</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-card divide-y divide-border">
                @forelse ($members as $member)
                    @php
                        $isActive = $member->status->value === 'ACTIVE' && $member->exp_date >= now()->toDateString();
                        $isExpired = $member->exp_date < now()->toDateString();
                        $hasCheckedInToday = $member->has_checked_in_today ?? false;
                    @endphp
                    <tr class="hover:bg-muted/50 transition-colors {{ !$isActive || $hasCheckedInToday ? 'opacity-75' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-card-foreground">
                            {{ $member->member_code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">
                            {{ $member->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                @if($member->status->value === 'ACTIVE')
                                    @if($isExpired)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                            EXPIRED
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            ACTIVE
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        INACTIVE
                                    </span>
                                @endif
                                
                                @if($hasCheckedInToday)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        ✓ Sudah Check-in Hari Ini
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground {{ $isExpired ? 'text-red-600 dark:text-red-400' : '' }}">
                            {{ \Carbon\Carbon::parse($member->exp_date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($hasCheckedInToday)
                                <button
                                    type="button"
                                    disabled
                                    class="px-4 py-1 text-xs bg-blue-100 text-blue-600 rounded-lg cursor-not-allowed dark:bg-blue-900 dark:text-blue-200"
                                    title="Member sudah check-in hari ini">
                                    Sudah Check-in
                                </button>
                            @elseif($isActive)
                                <form method="POST" action="{{ route('attendance.checkin') }}">
                                    @csrf
                                    <input type="hidden" name="member_id" value="{{ $member->id }}">
                                    <button
                                        type="submit"
                                        class="px-4 py-1 text-xs bubblegum-button bubblegum-button-primary rounded-lg">
                                        Check In
                                    </button>
                                </form>
                            @else
                                <button
                                    type="button"
                                    disabled
                                    class="px-4 py-1 text-xs bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed dark:bg-gray-800 dark:text-gray-600">
                                    Check In
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-muted-foreground">
                            <x-ui.icon name="user-search" class="w-12 h-12 mx-auto mb-2" />
                            <p>Tidak ada member ditemukan.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($members->hasPages())
        <div class="mt-6">
            {{ $members->links() }}
        </div>
    @endif
</div>

<script>
    // Auto-hide alert messages after 5 seconds
    document.addEventListener('DOMContentLoaded', () => {
        const alert = document.getElementById('alert-message');
        if (alert) {
            setTimeout(() => {
                alert.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(() => alert.remove(), 500); // remove from DOM after fade-out
            }, 5000);
        }
    });
</script>
@endsection