@extends('layouts.app')
@section('title', 'Analitik & Laporan')
@section('content')
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-foreground">Analitik & Laporan</h1>
      <p class="text-muted-foreground mt-1">Wawasan komprehensif tentang performa gym dan perilaku member</p>
    </div>
    <div class="mt-4 sm:mt-0 flex items-center space-x-3">
      <select class="px-4 py-2 border border-border bg-background text-foreground rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
        <option value="7">7 hari terakhir</option>
        <option value="30">30 hari terakhir</option>
        <option value="90">3 bulan terakhir</option>
        <option value="365">Tahun lalu</option>
      </select>
      <select class="px-4 py-2 border border-border bg-background text-foreground rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
        <option value="pdf">PDF</option>
        <option value="excel">Excel</option>
        <option value="csv">CSV</option>
      </select>
      @include('components.ui.button', ['slot' => 'Ekspor Laporan'])
    </div>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-card text-card-foreground rounded-lg shadow-sm border border-border p-6">
      <!-- Stat card content -->
    </div>
    <!-- ...repeat for each stat... -->
  </div>
  <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
    <div class="bg-card text-card-foreground rounded-lg shadow-sm border border-border p-6">
      <!-- Chart content -->
    </div>
    <!-- ...repeat for each chart... -->
  </div>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-card text-card-foreground rounded-lg shadow-sm border border-border p-6">
      <!-- Content -->
    </div>
    <div class="bg-card text-card-foreground rounded-lg shadow-sm border border-border p-6">
      <!-- Content -->
    </div>
  </div>
  <div class="bg-card text-card-foreground rounded-lg shadow-sm border border-border p-6">
    <div class="text-center py-8">
      <span class="icon-download w-12 h-12 text-gray-400 mx-auto mb-4"></span>
      <h3 class="text-lg font-semibold text-foreground mb-2">Ekspor Laporan Detail</h3>
      <p class="text-muted-foreground mb-6">Unduh laporan komprehensif dalam format pilihan Anda</p>
      <div class="flex justify-center space-x-4">
        @include('components.ui.button', ['variant' => 'outline', 'slot' => 'Ekspor sebagai PDF'])
        @include('components.ui.button', ['variant' => 'outline', 'slot' => 'Ekspor sebagai Excel'])
        @include('components.ui.button', ['variant' => 'outline', 'slot' => 'Ekspor sebagai CSV'])
      </div>
    </div>
  </div>
</div>
@endsection
