@extends('layouts.app')
@section('title', 'Konfirmasi Shift')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-foreground">Konfirmasi Shift Hari Ini</h1>
                <p class="text-sm text-muted-foreground mt-1">Silakan konfirmasi shift Anda sebelum melanjutkan aktivitas</p>
            </div>
        </div>

        <div class="bg-card p-6 rounded-lg shadow-sm border border-border">
            @if($todaySchedule)
                <div class="mb-4 p-4 bg-primary/10 border border-primary/20 rounded-lg">
                    <p class="text-sm text-card-foreground">
                        <span class="font-semibold">Jadwal yang ditetapkan:</span> 
                        <span class="text-primary">{{ $todaySchedule->shift_type->label() }}</span>
                    </p>
                </div>
            @endif

            <form action="{{ route('staff.shift.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="shift_type" class="block text-sm font-medium text-card-foreground mb-2">Shift *</label>
                    <select id="shift_type" name="shift_type" required
                        class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                        <option value="">Pilih shift</option>
                        @foreach($shiftTypes as $shiftType)
                            <option value="{{ $shiftType->value }}" {{ $todaySchedule && $todaySchedule->shift_type === $shiftType ? 'selected' : '' }}>
                                {{ $shiftType->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('shift_type')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-card-foreground mb-2">Catatan (Opsional)</label>
                    <textarea id="notes" name="notes" rows="3"
                        class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                        placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button type="submit"
                        class="inline-flex items-center justify-center px-6 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                        <x-ui.icon name="check" class="w-4 h-4 mr-2" />
                        Konfirmasi Shift
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

