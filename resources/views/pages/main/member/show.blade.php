@extends('layouts.app')
@section('title', 'Detail Member - ' . $member->name)
@section('content')
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <!-- Info -->
            <div>
                <h1 class="text-2xl font-bold text-foreground">Detail Member</h1>
                <p class="text-muted-foreground mt-1 text-sm sm:text-base">
                    {{ $member->name }} - {{ $member->member_code }}
                </p>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('member.index') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="chevron-left" class="w-4 h-4 mr-2" />
                    <span>Kembali</span>
                </a>
                <a href="{{ route('member.edit', $member) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                    <x-ui.icon name="edit" class="w-4 h-4 mr-2" />
                    <span>Edit</span>
                </a>
            </div>
        </div>



        <!-- Wrapper utama vertikal -->
        <div class="space-y-6">

            <!-- Bagian 1: Informasi Member + Aksi -->
            <div class="flex flex-col lg:flex-row gap-6">

                <!-- Informasi Member -->
                <div class="flex-1 bg-card p-6 rounded-lg shadow-sm border border-border">
                    <div class="flex items-center mb-4">
                        <x-ui.icon name="user" class="w-5 h-5 text-chart-1 mr-2" />
                        <h2 class="text-lg font-semibold text-card-foreground">Informasi Member</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Nama Lengkap</label>
                            <p class="mt-1 text-sm text-card-foreground">{{ $member->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Member Code</label>
                            <p class="mt-1 text-sm text-card-foreground font-mono">{{ $member->member_code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Email</label>
                            <p class="mt-1 text-sm text-card-foreground">{{ $member->email ?: '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Nomor Telepon</label>
                            <p class="mt-1 text-sm text-card-foreground">{{ $member->phone ?: '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Tanggal Kedaluwarsa</label>
                            <p class="mt-1 text-sm text-card-foreground">
                                {{ $member->exp_date ? $member->exp_date->format('d/m/Y') : '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Status</label>
                            @if ($member->status->value === 'ACTIVE')
                                <span class="mt-1 text-sm text-card-foreground text-green-200">Aktif</span>
                            @else
                                <span class="mt-1 text-sm text-card-foreground text-red-200">Tidak
                                    Aktif</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="w-full lg:w-1/3 bg-card p-6 rounded-lg shadow-sm border border-border">
                    <div class="flex items-center mb-4">
                        <x-ui.icon name="user-check" class="w-5 h-5 text-chart-1 mr-2" />
                        <h2 class="text-lg font-semibold text-card-foreground">Aksi</h2>
                    </div>
                    <div class="space-y-4">

                        <div class="space-y-2">
                            <!-- Tombol Perpanjang Membership -->
                            <a href="{{ route('member.extend') }}?member_id={{ $member->id }}"
                                class="w-full inline-flex items-center justify-center px-3 py-2 border border-primary text-primary rounded-lg hover:bg-primary/10 transition-colors">
                                <x-ui.icon name="calendar-plus" class="w-4 h-4 mr-2" />
                                Perpanjang Membership
                            </a>

                            @if ($member->status->value === 'ACTIVE')
                                <form action="{{ route('member.suspend', $member) }}" method="POST"
                                    class="inline-block w-full">
                                    @csrf
                                    <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-3 py-2 border border-destructive text-destructive rounded-lg hover:bg-destructive/10 transition-colors">
                                        <x-ui.icon name="user-x" class="w-4 h-4 mr-2" />
                                        Nonaktifkan Member
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('member.activate', $member) }}" method="POST"
                                    class="inline-block w-full">
                                    @csrf
                                    <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-3 py-2 border border-chart-2 text-chart-2 rounded-lg hover:bg-chart-2/10 transition-colors">
                                        <x-ui.icon name="user-check" class="w-4 h-4 mr-2" />
                                        Aktifkan Member
                                    </button>
                                </form>
                            @endif

                            <form id="delete-member-form" action="{{ route('member.destroy', $member) }}" method="POST"
                                class="inline-block w-full">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDeleteMember()"
                                    class="w-full inline-flex items-center justify-center px-3 py-2 border border-destructive text-destructive rounded-lg hover:bg-destructive/10 transition-colors">
                                    <x-ui.icon name="trash" class="w-4 h-4 mr-2" />
                                    Hapus Member
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian 2: Kehadiran + Registrasi -->
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Informasi Kehadiran -->
                <div class="flex-1 bg-card p-6 rounded-lg shadow-sm border border-border">
                    <div class="flex items-center mb-4">
                        <x-ui.icon name="calendar-event" class="w-5 h-5 text-chart-1 mr-2" />
                        <h2 class="text-lg font-semibold text-card-foreground">Informasi Kehadiran</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Check-in Terakhir</label>
                            <p class="mt-1 text-sm text-card-foreground">
                                {{ $member->last_check_in ? $member->last_check_in->format('d/m/Y H:i') : 'Belum pernah check-in' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Total Kunjungan</label>
                            <p class="mt-1 text-2xl font-bold text-card-foreground">{{ $member->total_visits }}</p>
                        </div>
                    </div>
                </div>

                <!-- Informasi Registrasi -->
                <div class="w-full lg:w-1/3 bg-card p-6 rounded-lg shadow-sm border border-border">
                    <div class="flex items-center mb-4">
                        <x-ui.icon name="calendar" class="w-5 h-5 text-chart-1 mr-2" />
                        <h2 class="text-lg font-semibold text-card-foreground">Informasi Registrasi</h2>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Tanggal Bergabung</label>
                            <p class="mt-1 text-sm text-card-foreground">{{ $member->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Terakhir Diperbarui</label>
                            <p class="mt-1 text-sm text-card-foreground">{{ $member->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

<script>
function confirmDeleteMember() {
    showConfirm(
        'Apakah Anda yakin ingin menghapus member ini? Tindakan ini tidak dapat dibatalkan.',
        function() {
            document.getElementById('delete-member-form').submit();
        },
        'Konfirmasi Hapus Member',
        'error'
    );
}
</script>
