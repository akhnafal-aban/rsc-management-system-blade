@extends('layouts.app')
@section('title', 'Check In Member')
@section('content')
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tanggal Expired</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-card divide-y divide-border">
                @forelse ($members as $member)
                    <tr class="hover:bg-muted/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-card-foreground">
                            {{ $member->member_code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">
                            {{ $member->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">
                            {{ \Carbon\Carbon::parse($member->exp_date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form method="POST" action="{{ route('attendance.checkin') }}">
                                @csrf
                                <input type="hidden" name="member_id" value="{{ $member->id }}">
                                <button
                                    type="submit"
                                    class="px-4 py-1 text-xs bubblegum-button bubblegum-button-primary rounded-lg">
                                    Check In
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-muted-foreground">
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
@endsection