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
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">

                {{-- Kolom Pencarian --}}
                <div class="flex-1 relative w-full">
                    <x-ui.icon name="search"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama, email, atau ID..."
                        class="w-full pl-10 pr-4 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent text-sm" />
                </div>

                {{-- Filter dan Tombol Submit --}}
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <select name="status"
                        class="w-full sm:w-auto px-4 py-2 bg-input border border-border rounded-lg text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                        <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif
                        </option>
                    </select>

                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors text-sm">
                        <x-ui.icon name="search" class="w-4 h-4 sm:mr-1" />
                        <span class="hidden sm:inline">Cari</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
            {{-- TABLE VIEW - Desktop --}}
            <div class="hidden sm:block overflow-x-auto">
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
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-background border border-border text-foreground hover:bg-muted/50 transition-colors"
                                                    title="Nonaktifkan"
                                                    onclick="return confirm('Apakah Anda yakin ingin menonaktifkan member ini?')">
                                                    <x-ui.icon name="user-x" class="w-3 h-3" />
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('member.activate', $member) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-background border border-border text-foreground hover:bg-muted/50 transition-colors"
                                                    title="Aktifkan"
                                                    onclick="return confirm('Apakah Anda yakin ingin mengaktifkan member ini?')">
                                                    <x-ui.icon name="user-check" class="w-3 h-3" />
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('member.destroy', $member) }}" method="POST"
                                            class="inline">
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

            {{-- CARD VIEW - Mobile --}}
            <div class="block sm:hidden divide-y divide-border">
                @forelse ($members as $member)
                    @php $isExpired = $member->exp_date < now()->toDateString(); @endphp
                    <div class="p-3">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="font-semibold text-sm">{{ $member->name }}</h4>
                            <span class="text-[11px] {{ $isExpired ? 'text-red-500' : 'text-green-500' }}">
                                {{ \Carbon\Carbon::parse($member->exp_date)->format('d M Y') }}
                            </span>
                        </div>
                        <p class="text-xs text-muted-foreground mb-1">ID: {{ $member->member_code }}</p>
                        <p class="text-xs text-muted-foreground mb-2">Check-in Terakhir:
                            {{ $member->last_check_in ? $member->last_check_in->format('d/m/Y') : '-' }}</p>

                        <div class="flex justify-between items-center text-xs mb-2">
                            <span class="{{ $isExpired ? 'text-red-500' : 'text-green-600' }}">
                                {{ $member->status->value === 'ACTIVE' ? ($isExpired ? 'Aktif (Expired)' : 'Aktif') : 'Tidak Aktif' }}
                            </span>
                            <span class="text-muted-foreground">Kunjungan: {{ $member->total_visits }}</span>
                        </div>

                        <div class="flex flex-wrap justify-end gap-1 pt-1">
                            {{-- Semua routes tetap sama persis --}}
                            <a href="{{ route('member.show', $member) }}"
                                class="px-3 py-1 text-[11px] border border-border rounded bg-background hover:bg-muted/50 transition-colors">
                                <x-ui.icon name="eye" class="w-3 h-3" />
                            </a>
                            <a href="{{ route('member.edit', $member) }}"
                                class="px-3 py-1 text-[11px] border border-border rounded bg-background hover:bg-muted/50 transition-colors">
                                <x-ui.icon name="edit" class="w-3 h-3" />
                            </a>
                            @if ($member->status->value === 'ACTIVE')
                                <form action="{{ route('member.suspend', $member) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1 text-[11px] border border-border rounded bg-background hover:bg-muted/50 transition-colors"
                                        onclick="return confirm('Apakah Anda yakin ingin menonaktifkan member ini?')">
                                        <x-ui.icon name="user-x" class="w-3 h-3" />
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('member.activate', $member) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1 text-[11px] border border-border rounded bg-background hover:bg-muted/50 transition-colors"
                                        onclick="return confirm('Apakah Anda yakin ingin mengaktifkan member ini?')">
                                        <x-ui.icon name="user-check" class="w-3 h-3" />
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('member.destroy', $member) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1 text-[11px] border border-border rounded bg-background text-destructive hover:bg-destructive/10 transition-colors"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus member ini?')">
                                    <x-ui.icon name="trash" class="w-3 h-3" />
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-muted-foreground">
                        <x-ui.icon name="users" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/50" />
                        <p>Tidak ada member yang ditemukan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pagination --}}
        @if ($members->hasPages())
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 mt-4 text-sm text-muted-foreground">
                <div>Menampilkan {{ $members->firstItem() }} sampai {{ $members->lastItem() }} dari
                    {{ $members->total() }} member</div>
                <div>{{ $members->appends(request()->query())->links() }}</div>
            </div>
        @endif

    </div>
@endsection
