@extends('layouts.app')
@section('title', 'Manajemen Member')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-row items-center justify-between mb-4 flex-wrap gap-3">
            <h1 class="text-2xl font-bold text-foreground">Members</h1>

            <div class="flex flex-wrap justify-end gap-3">
                {{-- Tombol Ekspor --}}
                <a href="#"
                    class="inline-flex items-center justify-center px-3 sm:px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="download" class="w-4 h-4 mr-0 sm:mr-2" />
                    <span class="hidden sm:inline">Ekspor</span>
                </a>

                {{-- Tombol Perpanjang Membership --}}
                <a href="{{ route('member.extend') }}"
                    class="inline-flex items-center justify-center px-3 sm:px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="calendar-plus" class="w-4 h-4 mr-0 sm:mr-2" />
                    <span class="hidden sm:inline">Perpanjang</span>
                </a>

                {{-- Tombol Tambah Member --}}
                <a href="{{ route('member.create') }}"
                    class="inline-flex items-center justify-center px-3 sm:px-6 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                    <x-ui.icon name="plus" class="w-5 h-5 mr-0 sm:mr-2" />
                    <span class="hidden sm:inline">Tambah Member</span>
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('member.index') }}"
            class="bg-card p-4 rounded-lg shadow-sm border border-border">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-2">

                {{-- Kolom Pencarian --}}
                <div class="flex-1 relative w-full">
                    <x-ui.icon name="search"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama, email, atau ID..."
                        class="w-full pl-10 pr-4 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent text-sm" />
                </div>

                {{-- Filter dan Tombol Submit --}}
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-2 w-full sm:w-auto">
                    <select name="status"
                        class="w-full sm:w-auto px-4 py-2 mb-1 bg-input border border-border rounded-lg text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                        <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif
                        </option>
                    </select>

                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors text-sm">
                        <x-ui.icon name="search" class="w-4 h-4 sm:mr-1" />
                        <span class="ml-2">Cari</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="rounded-lg shadow-sm overflow-hidden">
            {{-- TABLE VIEW - Desktop --}}
            <div class="bg-card hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/50">
                        <tr>
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
                            @php $isExpired = $member->exp_date < now()->toDateString(); @endphp
                            <tr class="hover:bg-muted/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-chart-1/20 rounded-full flex items-center justify-center">
                                            <span
                                                class="text-chart-1 font-medium text-sm">{{ substr($member->name, 0, 2) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-card-foreground">{{ $member->name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ $member->member_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($member->status->value === 'ACTIVE')
                                        @if ($isExpired)
                                            <span
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                                Aktif (Expired)
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-chart-2/20 text-chart-2">Aktif</span>
                                        @endif
                                    @else
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-destructive/20 text-destructive">Tidak
                                            Aktif</span>
                                    @endif
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm {{ $isExpired ? 'text-red-600 dark:text-red-400' : 'text-card-foreground' }}">
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
                                        {{-- Semua routes dipertahankan --}}
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
                                        @if ($member->status->value === 'ACTIVE')
                                            <form action="{{ route('member.suspend', $member) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                <button type="button"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-background border border-border text-foreground hover:bg-muted/50 transition-colors"
                                                    title="Nonaktifkan"
                                                    onclick="confirmDeactivateMember({{ $member->id }})">
                                                    <x-ui.icon name="user-x" class="w-3 h-3" />
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('member.activate', $member) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                <button type="button"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-background border border-border text-foreground hover:bg-muted/50 transition-colors"
                                                    title="Aktifkan"
                                                    onclick="confirmActivateMember({{ $member->id }})">
                                                    <x-ui.icon name="user-check" class="w-3 h-3" />
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('member.destroy', $member) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-background border border-border text-destructive hover:bg-destructive/10 transition-colors"
                                                title="Hapus"
                                                onclick="confirmDeleteMember({{ $member->id }})">
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

            {{-- CARD VIEW - Mobile --}}
            <div class="block sm:hidden">
                @forelse ($members as $member)
                    @php
                        $isExpired = $member->exp_date < now()->toDateString();
                        $statusColor = $isExpired ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700';
                        $statusText =
                            $member->status->value === 'ACTIVE'
                                ? ($isExpired
                                    ? 'Aktif (Expired)'
                                    : 'Aktif')
                                : 'Tidak Aktif';
                    @endphp

                    <div class="mb-3 rounded-lg border border-border bg-card shadow-sm transition-all hover:shadow-md">
                        <!-- Header Card - Center Aligned -->
                        <div class="text-center p-3 border-b border-border bg-muted/30">
                            <h4 class="font-semibold text-sm">{{ $member->name }}</h4>
                            <p class="text-xs text-muted-foreground mt-0.5">ID: {{ $member->member_code }}</p>
                        </div>

                        <!-- Content Card -->
                        <div class="p-3">
                            <!-- Status & Exp Date - Full Width -->
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <div class="bg-muted/25 rounded p-2">
                                    <p class="text-xs text-muted-foreground mb-0.5">Status</p>
                                    <p class="text-xs font-medium {{ $isExpired ? 'text-red-200' : 'text-green-200' }}">
                                        {{ $statusText }}
                                    </p>
                                </div>
                                <div class="bg-muted/25 rounded p-2">
                                    <p class="text-xs text-muted-foreground mb-0.5">Expired</p>
                                    <p class="text-xs font-medium {{ $isExpired ? 'text-red-200' : 'text-green-200' }}">
                                        {{ \Carbon\Carbon::parse($member->exp_date)->format('d M Y') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Info Section -->
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <div class="bg-muted/25 rounded p-2">
                                    <p class="text-xs text-muted-foreground mb-0.5">Check-in Terakhir</p>
                                    <p class="text-xs font-medium">
                                        {{ $member->last_check_in ? $member->last_check_in->format('d/m/Y') : '-' }}</p>
                                </div>
                                <div class="bg-muted/25 rounded p-2">
                                    <p class="text-xs text-muted-foreground mb-0.5">Total Kunjungan</p>
                                    <p class="text-xs font-medium">{{ $member->total_visits }}</p>
                                </div>
                            </div>

                            <!-- Action Buttons - Full Width with Original Colors -->
                            <div class="flex justify-between gap-1 pt-2 border-t border-border">
                                <a href="{{ route('member.show', $member) }}"
                                    class="flex-1 flex justify-center items-center py-2 text-[11px] border border-border rounded bg-background hover:bg-muted/50 transition-colors"
                                    title="Lihat Detail">
                                    <x-ui.icon name="eye" class="w-3 h-3" />
                                </a>
                                <a href="{{ route('member.edit', $member) }}"
                                    class="flex-1 flex justify-center items-center py-2 text-[11px] border border-border rounded bg-background hover:bg-muted/50 transition-colors"
                                    title="Edit">
                                    <x-ui.icon name="edit" class="w-3 h-3" />
                                </a>
                                @if ($member->status->value === 'ACTIVE')
                                    <form action="{{ route('member.suspend', $member) }}" method="POST"
                                        class="inline flex-1">
                                        @csrf
                                        <button type="button"
                                            class="w-full flex justify-center items-center py-2 text-[11px] border border-border rounded bg-background hover:bg-muted/50 transition-colors"
                                            title="Nonaktifkan"
                                            onclick="confirmDeactivateMember({{ $member->id }})">
                                            <x-ui.icon name="user-x" class="w-3 h-3" />
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('member.activate', $member) }}" method="POST"
                                        class="inline flex-1">
                                        @csrf
                                        <button type="button"
                                            class="w-full flex justify-center items-center py-2 text-[11px] border border-border rounded bg-background hover:bg-muted/50 transition-colors"
                                            title="Aktifkan"
                                            onclick="confirmActivateMember({{ $member->id }})">
                                            <x-ui.icon name="user-check" class="w-3 h-3" />
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('member.destroy', $member) }}" method="POST"
                                    class="inline flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="w-full flex justify-center items-center py-2 text-[11px] border border-border rounded bg-background text-destructive hover:bg-destructive/10 transition-colors"
                                        title="Hapus"
                                        onclick="confirmDeleteMember({{ $member->id }})">
                                        <x-ui.icon name="trash" class="w-3 h-3" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="text-center py-8 text-muted-foreground bg-muted/25 rounded-lg border border-dashed border-border">
                        <x-ui.icon name="users" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/50" />
                        <p>Tidak ada member yang ditemukan.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($members->hasPages())
                <div
                    class="flex flex-col sm:flex-row items-center justify-between gap-2 mt-4 text-sm text-muted-foreground">
                    <div class="bg-muted/20 rounded-lg px-3 py-2">
                        Menampilkan {{ $members->firstItem() }} sampai {{ $members->lastItem() }} dari
                        {{ $members->total() }} member
                    </div>
                    <div class="bg-muted/20 rounded-lg px-3 py-2">
                        {{ $members->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif

        </div>
    @endsection

<script>
function confirmDeactivateMember(memberId) {
    showConfirm(
        'Apakah Anda yakin ingin menonaktifkan member ini?',
        function() {
            const form = document.querySelector(`form[action*="/member/${memberId}/suspend"]`);
            if (form) form.submit();
        },
        'Konfirmasi Nonaktifkan Member',
        'warning'
    );
}

function confirmActivateMember(memberId) {
    showConfirm(
        'Apakah Anda yakin ingin mengaktifkan member ini?',
        function() {
            const form = document.querySelector(`form[action*="/member/${memberId}/activate"]`);
            if (form) form.submit();
        },
        'Konfirmasi Aktifkan Member',
        'info'
    );
}

function confirmDeleteMember(memberId) {
    showConfirm(
        'Apakah Anda yakin ingin menghapus member ini? Tindakan ini tidak dapat dibatalkan.',
        function() {
            const form = document.querySelector(`form[action*="/member/${memberId}"][method="POST"]`);
            if (form) {
                // Add DELETE method
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                form.submit();
            }
        },
        'Konfirmasi Hapus Member',
        'error'
    );
}
</script>
