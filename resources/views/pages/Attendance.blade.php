@extends('layouts.app')
@section('title', 'Manajemen Absensi')
@section('content')
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-foreground">Manajemen Absensi</h1>
      <p class="text-muted-foreground mt-1">Scan barcode member dan pantau kehadiran</p>
    </div>
    <div class="mt-4 sm:mt-0 flex space-x-3">
      @include('components.UI.button', ['variant' => 'outline', 'slot' => 'Perbarui'])
      @include('components.UI.button', ['variant' => 'outline', 'slot' => 'Ekspor'])
    </div>
  </div>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Scanner Section (optional, can be included as a component) -->
    <!-- @include('components.Attendance.camera-scanner') -->
    <div class="lg:col-span-2">
      @include('components.UI.card', ['slot' =>
      '<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h3 class="text-lg font-semibold text-card-foreground">Absensi Hari Ini</h3>
        <div class="mt-4 sm:mt-0 flex space-x-3">
          <div class="relative">
            <input type="text" placeholder="Cari member..." class="pl-10 pr-4 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent" />
          </div>
          <select class="px-4 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
            <option value="semua">Semua Status</option>
            <option value="check in">Check In</option>
            <option value="check out">Check Out</option>
          </select>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-border">
          <thead class="bg-muted/50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Member ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Nama</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Waktu Check-in</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Waktu Check-out</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
            </tr>
          </thead>
          <tbody class="bg-card divide-y divide-border">
            <!-- Data rows go here, use Blade @foreach if dynamic -->
            <tr class="hover:bg-muted/50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-card-foreground">MB001</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">John Smith</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">09:30</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">-</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-chart-2/20 text-chart-2">Check In</span>
              </td>
            </tr>
            <!-- ...more rows... -->
          </tbody>
        </table>
      </div>
      <div class="text-center py-12">
        <p class="text-muted-foreground">Tidak ada data absensi ditemukan</p>
      </div>'
      ])
    </div>
  </div>
</div>
@endsection
