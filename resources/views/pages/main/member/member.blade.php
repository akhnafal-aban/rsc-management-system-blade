@extends('layouts.app')
@section('title', 'Manajemen Member')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <h1 class="text-2xl font-bold text-foreground">Members Management</h1>
            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <a href="#"
                    class="inline-flex items-center justify-center px-5 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors w-full sm:w-auto">
                    <x-ui.icon name="download" class="w-4 h-4 mr-2" />
                    <span>Ekspor</span>
                </a>
                <a href="{{ route('member.extend') }}"
                    class="inline-flex items-center justify-center px-5 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors w-full sm:w-auto">
                    <x-ui.icon name="calendar-plus" class="w-4 h-4 mr-2" />
                    <span>Perpanjang Membership</span>
                </a>
                <a href="{{ route('member.create') }}"
                    class="inline-flex items-center justify-center px-5 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors w-full sm:w-auto">
                    <x-ui.icon name="plus" class="w-4 h-4 mr-2" />
                    <span>Tambah Member</span>
                </a>
            </div>
        </div>
        
        <form method="GET" action="{{ route('member.index') }}" class="bg-card p-4 rounded-lg shadow-sm border border-border">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0 lg:space-x-4">
                <div class="flex-1 relative">
                    <x-ui.icon name="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari member berdasarkan nama, email, atau ID..."
                        class="w-full pl-10 pr-4 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent" />
                </div>
                <div class="flex space-x-3">
                    <select name="status" class="px-4 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                        <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                        <x-ui.icon name="search" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </form>
        <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" class="rounded border-border text-primary focus:ring-ring" />
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Member</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Tanggal Expired</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Check-in Terakhir</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Total Kunjungan</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        @forelse($members as $member)
                            @php
                                $isExpired = $member->exp_date < now()->toDateString();
                            @endphp
                            <tr class="hover:bg-muted/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" class="rounded border-border text-primary focus:ring-ring" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-chart-1/20 rounded-full flex items-center justify-center">
                                            <span class="text-chart-1 font-medium text-sm">{{ substr($member->name, 0, 2) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-card-foreground">{{ $member->name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ $member->member_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($member->status->value === 'ACTIVE')
                                        @if($isExpired)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                                Aktif (Expired)
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-chart-2/20 text-chart-2">Aktif</span>
                                        @endif
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-destructive/20 text-destructive">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $isExpired ? 'text-red-600 dark:text-red-400' : 'text-card-foreground' }}">
                                    {{ \Carbon\Carbon::parse($member->exp_date)->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">
                                    {{ $member->last_check_in ? $member->last_check_in->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">
                                    {{ $member->total_visits }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-1">
                                        <a href="{{ route('member.show', $member) }}" 
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-background border border-border text-foreground hover:bg-muted/50 transition-colors"
                                            title="Lihat Detail">
                                            <x-ui.icon name="eye" class="w-3 h-3" />
                                        </a>
                                        <a href="{{ route('member.edit', $member) }}" 
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-background border border-border text-foreground hover:bg-muted/50 transition-colors"
                                            title="Edit">
                                            <x-ui.icon name="edit" class="w-3 h-3" />
                                        </a>
                                        @if($member->status->value === 'ACTIVE')
                                            <form action="{{ route('member.suspend', $member) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-background border border-border text-foreground hover:bg-muted/50 transition-colors"
                                                    title="Nonaktifkan"
                                                    onclick="return confirm('Apakah Anda yakin ingin menonaktifkan member ini?')">
                                                    <x-ui.icon name="user-x" class="w-3 h-3" />
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('member.activate', $member) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-background border border-border text-foreground hover:bg-muted/50 transition-colors"
                                                    title="Aktifkan"
                                                    onclick="return confirm('Apakah Anda yakin ingin mengaktifkan member ini?')">
                                                    <x-ui.icon name="user-check" class="w-3 h-3" />
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('member.destroy', $member) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-background border border-border text-destructive hover:bg-destructive/10 transition-colors"
                                                title="Hapus"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus member ini? Tindakan ini tidak dapat dibatalkan.')">
                                                <x-ui.icon name="trash" class="w-3 h-3" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-muted-foreground">
                                    <x-ui.icon name="users" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/50" />
                                    <p>Tidak ada member yang ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($members->hasPages())
            <div class="flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                    Menampilkan {{ $members->firstItem() }} sampai {{ $members->lastItem() }} dari {{ $members->total() }} member
                </div>
                <div class="flex items-center space-x-2">
                    {{ $members->appends(request()->query())->links() }}
                </div>
            </div>
        @endif

    </div>
@endsection
