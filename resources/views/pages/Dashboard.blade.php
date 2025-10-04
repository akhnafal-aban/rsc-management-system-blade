@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-foreground">Dashboard</h1>
    </div>
    <div class="mt-4 sm:mt-0 flex items-center space-x-2 text-sm text-muted-foreground">
      <span class="icon-calendar w-4 h-4"></span>
      <span><!-- Tanggal --></span>
      <span class="icon-clock w-4 h-4 ml-4"></span>
      <span class="font-mono"><!-- Jam --></span>
    </div>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
    @include('components.Dashboard.stat-card')
    <!-- ...repeat for each stat... -->
  </div>
  <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
    @include('components.Dashboard.chart-card')
    <!-- ...repeat for each chart... -->
  </div>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-card rounded-lg shadow-sm border border-border p-6">
      <h3 class="text-lg font-semibold text-card-foreground mb-4">Check-in Terbaru</h3>
      <div class="space-y-3">
        <!-- Recent activity rows -->
      </div>
    </div>
    <div class="bg-card rounded-lg shadow-sm border border-border p-6">
      <h3 class="text-lg font-semibold text-card-foreground mb-4">Aksi Cepat</h3>
      <div class="grid grid-cols-2 gap-3">
        <button class="p-4 border-2 border-dashed border-border rounded-lg hover:border-chart-1 hover:bg-chart-1/10 transition-colors">
          <span class="icon-user-check w-8 h-8 text-muted-foreground mx-auto mb-2"></span>
          <span class="text-sm font-medium text-card-foreground">Check-in Member</span>
        </button>
        <!-- ...other quick actions... -->
      </div>
    </div>
  </div>
</div>
@endsection
