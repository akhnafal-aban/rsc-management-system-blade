<div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-border">
      <thead class="bg-muted/50">
        <tr>
          <th class="px-6 py-3 text-left">
            <input type="checkbox" class="rounded border-border text-primary focus:ring-ring" />
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Member</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Check-in Terakhir</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Kunjungan</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">Aksi</th>
        </tr>
      </thead>
      <tbody class="bg-card divide-y divide-border">
        <!-- Use @foreach for dynamic rows -->
        <tr class="hover:bg-muted/50 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <input type="checkbox" class="rounded border-border text-primary focus:ring-ring" />
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-chart-1/20 rounded-full flex items-center justify-center">
                <span class="text-chart-1 font-medium text-sm">JS</span>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-card-foreground">John Smith</div>
                <div class="text-sm text-muted-foreground">john.smith@email.com</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-chart-2/20 text-chart-2">Aktif</span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">15/01/2024</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">45</td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <div class="flex items-center justify-end space-x-2">
              @include('components.ui.button', ['variant' => 'ghost', 'size' => 'sm', 'slot' => 'Lihat'])
              @include('components.ui.button', ['variant' => 'ghost', 'size' => 'sm', 'slot' => 'Edit'])
              @include('components.ui.button', ['variant' => 'ghost', 'size' => 'sm', 'slot' => 'Suspend'])
              @include('components.ui.button', ['variant' => 'ghost', 'size' => 'sm', 'slot' => 'Hapus'])
            </div>
          </td>
        </tr>
        <!-- ...more rows... -->
      </tbody>
    </table>
  </div>
</div>
