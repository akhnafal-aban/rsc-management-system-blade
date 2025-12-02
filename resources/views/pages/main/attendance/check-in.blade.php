@extends('layouts.app')
@section('title', 'Check In Member')
@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Check In Member</h1>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('attendance.index') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="chevron-left" class="w-4 h-4 mr-2" />
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <div id="checkInApp" class="space-y-6"
            data-search-url="{{ route('member.search') }}"
            data-batch-url="{{ route('attendance.checkin.batch') }}"
            data-single-url="{{ route('attendance.checkin') }}">
            <div class="bg-card p-6 rounded-lg shadow-sm border border-border space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-card-foreground mb-1">Pencarian Member</h2>
                    {{-- <p class="text-sm text-muted-foreground">Masukkan kata kunci lalu tekan tombol <span class="font-semibold">Cari</span>. Hasil akan dimuat bertahap agar tetap ringan meski data member sangat besar.</p> --}}
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <x-ui.icon name="search"
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <input type="text" id="member_search_input"
                            class="w-full pl-10 pr-4 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="Ketik nama, ID member, atau nomor telepon..." autocomplete="off">
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="member_search_button"
                            class="inline-flex items-center justify-center px-5 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                            <x-ui.icon name="search" class="w-4 h-4 mr-2" />
                            Cari
                        </button>
                        <button type="button" id="member_search_clear"
                            class="inline-flex items-center justify-center px-5 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                            <x-ui.icon name="eraser" class="w-4 h-4 mr-2" />
                            Bersihkan
                        </button>
                    </div>
                </div>

                <div id="member_search_meta" class="hidden text-sm text-muted-foreground"></div>

                <div id="member_results_wrapper" class="hidden space-y-2">
                    <div class="border border-dashed border-border rounded-lg">
                        <div id="member_results_header"
                            class="flex items-center justify-between px-4 py-3 bg-muted/40 border-b border-border text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            <span>Hasil Pencarian</span>
                            <span id="member_results_count"></span>
                        </div>
                        <div id="member_results" class="max-h-72 overflow-y-auto overscroll-contain">
                            <div id="member_results_container" class="divide-y divide-border"></div>
                            <div id="member_results_sentinel" class="h-10"></div>
                        </div>
                    </div>
                    <div id="member_results_empty" class="hidden text-sm text-muted-foreground text-center py-4">
                        Tidak ada member yang cocok dengan pencarian.
                    </div>
                </div>
            </div>

            <div class="bg-card p-6 rounded-lg shadow-sm border border-border space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-card-foreground">Selected Batch Member</h2>
                        {{-- <p class="text-sm text-muted-foreground">Pilih beberapa member dari hasil pencarian untuk melakukan check-in massal menggunakan satu permintaan saja.</p> --}}
                    </div>
                    <span id="selected_counter"
                        class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-muted text-muted-foreground">
                        0 dipilih
                    </span>
                </div>

                <div id="selected_members_placeholder"
                    class="text-sm text-muted-foreground bg-muted/30 rounded-lg border border-border border-dashed px-4 py-6 text-center">
                    Belum ada member yang dipilih. Gunakan kolom pencarian lalu tekan tombol <span class="font-semibold">Tambah ke Batch</span> pada hasil yang tersedia.
                </div>

                <div id="selected_members_list" class="grid gap-3"></div>

                <div class="flex flex-col sm:flex-row gap-2 sm:justify-end sm:items-center">
                    <button type="button" id="selected_clear_all"
                        class="inline-flex items-center justify-center px-5 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                        <x-ui.icon name="x-circle" class="w-4 h-4 mr-2" />
                        Bersihkan Pilihan
                    </button>
                    <button type="button" id="batch_checkin_button"
                        class="inline-flex items-center justify-center px-6 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                        <x-ui.icon name="log-in" class="w-4 h-4 mr-2" />
                        Check In Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
