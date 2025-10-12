@extends('layouts.app')
@section('title', 'Tambah Member')
@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Tambah Member Baru</h1>
                <p class="text-muted-foreground mt-1">Masukkan informasi member untuk mendaftarkan anggota baru</p>
            </div>
            <a href="{{ route('member.index') }}" 
                class="inline-flex items-center px-4 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                <x-ui.icon name="chevron-left" class="w-4 h-4 mr-2" />
                <span>Kembali</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-destructive/10 border border-destructive/20 rounded-lg p-4">
                <div class="flex">
                    <x-ui.icon name="alert-circle" class="w-5 h-5 text-destructive mr-3 mt-0.5" />
                    <div>
                        <h3 class="text-sm font-medium text-destructive">Terdapat kesalahan:</h3>
                        <ul class="mt-2 text-sm text-destructive list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('member.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="bg-card p-6 rounded-lg shadow-sm border border-border">
                <h2 class="text-lg font-semibold text-card-foreground mb-4">Informasi Member</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-card-foreground mb-2">Nama Lengkap *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="Masukkan nama lengkap">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-card-foreground mb-2">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="contoh@email.com">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-card-foreground mb-2">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="08123456789">
                    </div>

                    <div>
                        <label for="exp_date" class="block text-sm font-medium text-card-foreground mb-2">Tanggal Kedaluwarsa *</label>
                        <input type="date" id="exp_date" name="exp_date" value="{{ old('exp_date') }}" required
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-card-foreground mb-2">Status</label>
                        <select id="status" name="status"
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                            <option value="ACTIVE" {{ old('status', 'ACTIVE') == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                            <option value="INACTIVE" {{ old('status') == 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('member.index') }}" 
                    class="px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                    <x-ui.icon name="plus" class="w-4 h-4 mr-2 inline" />
                    Tambah Member
                </button>
            </div>
        </form>
    </div>
@endsection