@extends('layouts.app')
@section('title', 'Penjadwalan Staf')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-foreground">Penjadwalan Staf</h1>
                <p class="text-sm text-muted-foreground mt-1">Atur shift pagi/sore dengan tampilan kalender bulanan</p>
            </div>
            <form method="GET" action="{{ route('admin.staff-schedule.index') }}" class="flex flex-col sm:flex-row gap-3">
                <input type="month" name="month" value="{{ $month }}"
                    class="px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent w-full sm:w-auto">
                <select name="user_id"
                    class="px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent w-full sm:w-auto">
                    <option value="">Semua Staf</option>
                    @foreach ($staffList as $staff)
                        <option value="{{ $staff->id }}" {{ request('user_id') == $staff->id ? 'selected' : '' }}>
                            {{ $staff->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                    Terapkan
                </button>
            </form>
        </div>

        <div class="bg-card p-6 rounded-lg shadow-sm border border-border space-y-6">
            <h2 class="text-lg font-semibold text-card-foreground">Tambah / Ubah Jadwal</h2>
            <form method="POST" action="{{ route('admin.staff-schedule.store') }}" class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                @csrf
                <div class="lg:col-span-2">
                    <label for="user_id" class="block text-sm font-medium text-card-foreground mb-2">Staf *</label>
                    <select id="user_id" name="user_id" required
                        class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                        <option value="">Pilih staf</option>
                        @foreach ($staffList as $staff)
                            <option value="{{ $staff->id }}" {{ old('user_id') == $staff->id ? 'selected' : '' }}>
                                {{ $staff->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="schedule_date" class="block text-sm font-medium text-card-foreground mb-2">Tanggal *</label>
                    <input type="date" id="schedule_date" name="schedule_date" value="{{ old('schedule_date', now()->toDateString()) }}" required
                        class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                    @error('schedule_date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="shift_type" class="block text-sm font-medium text-card-foreground mb-2">Shift *</label>
                    <select id="shift_type" name="shift_type" required
                        class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                        <option value="">Pilih shift</option>
                        @foreach ($shiftTypes as $shiftType)
                            <option value="{{ $shiftType->value }}" {{ old('shift_type') === $shiftType->value ? 'selected' : '' }}>
                                {{ $shiftType->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('shift_type')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="lg:col-span-4">
                    <label for="notes" class="block text-sm font-medium text-card-foreground mb-2">Catatan</label>
                    <textarea id="notes" name="notes" rows="2"
                        class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                        placeholder="Contoh: covering shift rekan yang cuti">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="lg:col-span-4 flex justify-end gap-3">
                    <button type="submit"
                        class="inline-flex items-center justify-center px-5 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                        <x-ui.icon name="save" class="w-4 h-4 mr-2" />
                        Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-card p-6 rounded-lg shadow-sm border border-border">
            <h2 class="text-lg font-semibold text-card-foreground mb-4">Kalender Bulanan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @php($grouped = $schedules->groupBy(fn ($s) => $s->schedule_date->format('Y-m-d')))
                @foreach ($grouped as $date => $daySchedules)
                    <div class="border border-border rounded-lg p-4 bg-muted/30">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-semibold text-card-foreground">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
                            <span class="text-xs text-muted-foreground">{{ $daySchedules->count() }} shift</span>
                        </div>
                        <div class="space-y-3">
                            @foreach ($daySchedules as $schedule)
                                <div class="rounded-lg border border-border bg-card/80 p-3 flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-card-foreground">{{ $schedule->user->name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ $schedule->shift_type->label() }}</p>
                                        @if ($schedule->notes)
                                            <p class="text-xs text-muted-foreground mt-1 line-clamp-2">{{ $schedule->notes }}</p>
                                        @endif
                                    </div>
                                    <form action="{{ route('admin.staff-schedule.destroy', $schedule->id) }}" method="POST"
                                        class="ml-3">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-destructive hover:text-destructive/80 p-1 rounded-lg bg-destructive/10"
                                            onclick="return confirm('Hapus jadwal ini?')">
                                            <x-ui.icon name="trash" class="w-4 h-4" />
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                @if ($grouped->isEmpty())
                    <div class="col-span-full text-center text-sm text-muted-foreground py-8">
                        Jadwal belum tersedia untuk bulan ini.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

