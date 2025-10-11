@extends('layouts.app')
@section('title', 'Manajemen Staf')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-foreground">Manajemen Staf</h1>
    <a href="#" class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90">Tambah Staf</a>
  </div>

  <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-border">
        <thead class="bg-muted/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Nama</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Peran</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-card divide-y divide-border">
          <tr class="hover:bg-muted/50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">Jane Doe</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">jane@example.com</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-chart-3/20 text-chart-3">Staff</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex items-center justify-end space-x-2">
                <button class="px-3 py-1 rounded-md border border-border hover:bg-muted">Promote</button>
                <button class="px-3 py-1 rounded-md border border-border hover:bg-muted">Suspend</button>
                <button class="px-3 py-1 rounded-md border border-destructive text-destructive hover:bg-destructive/10">Hapus</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection


