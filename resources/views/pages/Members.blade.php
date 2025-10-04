@extends('layouts.app')
@section('title', 'Manajemen Member')
@section('content')
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-foreground">Manajemen Member</h1>
      <p class="text-muted-foreground mt-1">Kelola data member gym dan detail informasinya</p>
    </div>
    <div class="mt-4 sm:mt-0 flex space-x-3">
      @include('components.UI.button', ['variant' => 'outline', 'slot' => 'Ekspor'])
      @include('components.UI.button', ['slot' => 'Tambah Member'])
    </div>
  </div>
  <div class="bg-card p-4 rounded-lg shadow-sm border border-border">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0 lg:space-x-4">
      <div class="flex-1 relative">
        <input type="text" placeholder="Cari member berdasarkan nama, email, atau ID..." class="w-full pl-10 pr-4 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent" />
      </div>
      <div class="flex space-x-3">
        <select class="px-4 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
          <option value="semua">Semua Status</option>
          <option value="Aktif">Aktif</option>
          <option value="Suspend">Suspend</option>
          <option value="Kedaluwarsa">Kedaluwarsa</option>
        </select>
      </div>
    </div>
  </div>
  @include('components.Members.member-table')
  <!-- Add/Edit/View Member Modals as Blade includes or components -->
</div>
@endsection
