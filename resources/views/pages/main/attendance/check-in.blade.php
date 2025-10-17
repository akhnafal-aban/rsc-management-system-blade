@extends('layouts.app')
@section('title', 'Check In Member')
@section('content')

    <!-- Check-In Member Table -->
    <div class="bg-card text-card-foreground rounded-lg shadow-sm border border-border p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h3 class="text-lg font-semibold text-card-foreground">Check In Member</h3>
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <!-- Search Form -->
                <form method="GET" action="{{ route('attendance.check-in') }}"
                    class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ $search }}"
                            placeholder="Cari nama atau ID member..."
                            class="w-full pl-10 pr-4 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent" />
                        <x-ui.icon name="search"
                            class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 transform -translate-y-1/2" />
                    </div>
                    <button type="submit"
                        class="w-full sm:w-auto px-4 py-2 text-xs bubblegum-button bubblegum-button-primary rounded-lg">
                        Cari
                    </button>

                </form>
            </div>
        </div>

        {{-- Wrapper --}}
        <div class="bg-card text-card-foreground rounded-lg shadow-sm border border-border p-6">
            {{-- Tabel untuk desktop --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-border text-sm">
                    <thead class="bg-muted/50 sticky top-0 z-10">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider min-w-[90px]">
                                Member ID</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider min-w-[140px]">
                                Nama</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider min-w-[100px]">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider min-w-[120px]">
                                Tanggal Expired</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider min-w-[90px]">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        @forelse ($members as $member)
                            @php
                                $isActive =
                                    $member->status->value === 'ACTIVE' && $member->exp_date >= now()->toDateString();
                                $isExpired = $member->exp_date < now()->toDateString();
                                $hasCheckedInToday = $member->has_checked_in_today ?? false;
                            @endphp
                            <tr
                                class="hover:bg-muted/50 transition-colors {{ !$isActive || $hasCheckedInToday ? 'opacity-75' : '' }}">
                                <td class="px-6 py-4 text-sm font-medium">{{ $member->member_code }}</td>
                                <td class="px-6 py-4 text-sm">{{ $member->name }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        @if ($member->status->value === 'ACTIVE')
                                            @if ($isExpired)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                                    EXPIRED
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    ACTIVE
                                                </span>
                                            @endif
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                INACTIVE
                                            </span>
                                        @endif

                                        @if ($hasCheckedInToday)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                ✓ Sudah Check-in Hari Ini
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm {{ $isExpired ? 'text-red-600 dark:text-red-400' : '' }}">
                                    {{ \Carbon\Carbon::parse($member->exp_date)->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($hasCheckedInToday)
                                        <button type="button" disabled
                                            class="w-full sm:w-auto px-4 py-1 text-xs bg-blue-100 text-blue-600 rounded-lg cursor-not-allowed dark:bg-blue-900 dark:text-blue-200">
                                            Sudah Check-in
                                        </button>
                                    @elseif($isActive)
                                        <form method="POST" action="{{ route('attendance.checkin') }}">
                                            @csrf
                                            <input type="hidden" name="member_id" value="{{ $member->id }}">
                                            <button type="submit"
                                                class="w-full sm:w-auto px-4 py-1 text-xs bubblegum-button bubblegum-button-primary rounded-lg">
                                                Check In
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled
                                            class="w-full sm:w-auto px-4 py-1 text-xs bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed dark:bg-gray-800 dark:text-gray-600">
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

            {{-- Card View untuk Mobile --}}
            <div class="block sm:hidden space-y-4">
                @forelse ($members as $member)
                    @php
                        $isActive = $member->status->value === 'ACTIVE' && $member->exp_date >= now()->toDateString();
                        $isExpired = $member->exp_date < now()->toDateString();
                        $hasCheckedInToday = $member->has_checked_in_today ?? false;
                    @endphp
                    <div class="rounded-lg bg-card shadow-sm">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-semibold text-sm">{{ $member->name }}</h4>
                        </div>

                        <div class="flex justify-between items-center mb-2 text-xs">
                            <p class="text-muted-foreground">ID: {{ $member->member_code }}</p>
                            <span class="{{ $isExpired ? 'text-red-500' : 'text-green-500' }}">
                                {{ \Carbon\Carbon::parse($member->exp_date)->format('d M Y') }}
                            </span>
                        </div>


                        <div class="flex flex-col gap-2 mb-3">
                            @if ($member->status->value === 'ACTIVE')
                                @if ($isExpired)
                                    <span
                                        class="inline-flex items-center justify-center w-full px-2 py-0.5 rounded-full text-[11px] font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                        EXPIRED
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center justify-center w-full px-2 py-0.5 rounded-full text-[11px] font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        ACTIVE
                                    </span>
                                @endif
                            @else
                                <span
                                    class="inline-flex items-center justify-center w-full px-2 py-0.5 rounded-full text-[11px] font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    INACTIVE
                                </span>
                            @endif

                            @if ($hasCheckedInToday)
                                <span
                                    class="inline-flex items-center justify-center w-full px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    ✓ Sudah Check-in
                                </span>
                            @endif
                        </div>
                        <div>
                            @if ($hasCheckedInToday)
                                <button type="button" disabled
                                    class="w-full px-4 py-2 text-xs bg-blue-100 text-blue-600 rounded-lg cursor-not-allowed dark:bg-blue-900 dark:text-blue-200">
                                    Sudah Check-in
                                </button>
                            @elseif($isActive)
                                <form method="POST" action="{{ route('attendance.checkin') }}">
                                    @csrf
                                    <input type="hidden" name="member_id" value="{{ $member->id }}">
                                    <button type="submit"
                                        class="w-full px-4 py-2 text-xs bubblegum-button bubblegum-button-primary rounded-lg">
                                        Check In
                                    </button>
                                </form>
                            @else
                                <button type="button" disabled
                                    class="w-full px-4 py-2 text-xs bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed dark:bg-gray-800 dark:text-gray-600">
                                    Check In
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="border-b border-border"></div>
                @empty
                    <div class="text-center py-8 text-muted-foreground">
                        <x-ui.icon name="user-search" class="w-10 h-10 mx-auto mb-2" />
                        <p>Tidak ada member ditemukan.</p>
                    </div>
                @endforelse
            </div>
        </div>


        @if ($members->hasPages())
            <div class="mt-6">
                {{ $members->links() }}
            </div>
        @endif
    </div>

@endsection
