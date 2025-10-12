@extends('layouts.app')
@section('title', 'Detail Member - ' . $member->name)
@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Detail Member</h1>
                <p class="text-muted-foreground mt-1">{{ $member->name }} - {{ $member->member_code }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('member.index') }}" 
                    class="inline-flex items-center px-4 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="chevron-left" class="w-4 h-4 mr-2" />
                    <span>Kembali</span>
                </a>
                <a href="{{ route('member.edit', $member) }}" 
                    class="inline-flex items-center px-4 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                    <x-ui.icon name="edit" class="w-4 h-4 mr-2" />
                    <span>Edit</span>
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-chart-2/10 border border-chart-2/20 rounded-lg p-4">
                <div class="flex">
                    <x-ui.icon name="check" class="w-5 h-5 text-chart-2 mr-3 mt-0.5" />
                    <div>
                        <h3 class="text-sm font-medium text-chart-2">Berhasil!</h3>
                        <p class="mt-1 text-sm text-chart-2">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Informasi Member -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informasi Personal -->
                <div class="bg-card p-6 rounded-lg shadow-sm border border-border">
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
                            <p class="mt-1 text-sm text-card-foreground">{{ $member->exp_date ? $member->exp_date->format('d/m/Y') : '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Status</label>
                            @if($member->status->value === 'ACTIVE')
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-chart-2/20 text-chart-2">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-destructive/20 text-destructive">
                                    Tidak Aktif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Informasi Kehadiran -->
                <div class="bg-card p-6 rounded-lg shadow-sm border border-border">
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
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <!-- Status & Actions -->
                <div class="bg-card p-6 rounded-lg shadow-sm border border-border">
                    <div class="flex items-center mb-4">
                        <x-ui.icon name="user-check" class="w-5 h-5 text-chart-1 mr-2" />
                        <h2 class="text-lg font-semibold text-card-foreground">Status & Aksi</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-2">Status Member</label>
                            @if($member->status->value === 'ACTIVE')
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-chart-2/20 text-chart-2">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-destructive/20 text-destructive">
                                    Tidak Aktif
                                </span>
                            @endif
                        </div>

                        <div class="space-y-2">
                            @if($member->status->value === 'ACTIVE')
                                <form action="{{ route('member.suspend', $member) }}" method="POST" class="inline-block w-full">
                                    @csrf
                                    <button type="submit" 
                                        class="w-full inline-flex items-center justify-center px-3 py-2 border border-destructive text-destructive rounded-lg hover:bg-destructive/10 transition-colors">
                                        <x-ui.icon name="user-x" class="w-4 h-4 mr-2" />
                                        Nonaktifkan Member
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('member.activate', $member) }}" method="POST" class="inline-block w-full">
                                    @csrf
                                    <button type="submit" 
                                        class="w-full inline-flex items-center justify-center px-3 py-2 border border-chart-2 text-chart-2 rounded-lg hover:bg-chart-2/10 transition-colors">
                                        <x-ui.icon name="user-check" class="w-4 h-4 mr-2" />
                                        Aktifkan Member
                                    </button>
                                </form>
                            @endif
                            
                            <form action="{{ route('member.destroy', $member) }}" method="POST" class="inline-block w-full"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus member ini? Tindakan ini tidak dapat dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-3 py-2 border border-destructive text-destructive rounded-lg hover:bg-destructive/10 transition-colors">
                                    <x-ui.icon name="trash" class="w-4 h-4 mr-2" />
                                    Hapus Member
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Informasi Registrasi -->
                <div class="bg-card p-6 rounded-lg shadow-sm border border-border">
                    <div class="flex items-center mb-4">
                        <x-ui.icon name="calendar-event" class="w-5 h-5 text-chart-1 mr-2" />
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
