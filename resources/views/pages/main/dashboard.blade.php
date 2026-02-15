@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-row items-center justify-between mb-4 flex-wrap gap-3">
            <h1 class="text-2xl font-bold text-foreground">Dashboard</h1>
        
            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center justify-center px-3 sm:px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="refresh" class="w-4 h-4 mr-0 sm:mr-2" />
                    <span class="hidden sm:inline">Perbarui</span>
                </a>
            </div>
        </div>

        <!-- Aksi Cepat -->
        <div class="bg-card rounded-lg shadow-sm border border-border p-6">
            <h3 class="text-lg font-semibold text-card-foreground mb-4">Aksi Cepat</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <a href="{{ route('attendance.check-in') }}"
                    class="p-4 border-2 border-dashed border-border rounded-lg hover:border-chart-1 hover:bg-chart-1/10 transition-colors text-center">
                    <x-ui.icon name="user-check" class="w-8 h-8 text-muted-foreground mx-auto mb-2 block" />
                    <span class="text-sm font-medium text-card-foreground">Check-in Member</span>
                </a>
                <a href="{{ route('member.create') }}"
                    class="p-4 border-2 border-dashed border-border rounded-lg hover:border-chart-2 hover:bg-chart-2/10 transition-colors text-center">
                    <x-ui.icon name="users" class="w-8 h-8 text-muted-foreground mx-auto mb-2 block" />
                    <span class="text-sm font-medium text-card-foreground">Tambah Member</span>
                </a>
                <a href="{{ route('non-member-visit.index') }}"
                    class="p-4 border-2 border-dashed border-border rounded-lg hover:border-chart-3 hover:bg-chart-3/10 transition-colors text-center">
                    <x-ui.icon name="user-plus" class="w-8 h-8 text-muted-foreground mx-auto mb-2 block" />
                    <span class="text-sm font-medium text-card-foreground">Non-Member Visit</span>
                </a>
                <a href="#"
                    class="p-4 border-2 border-dashed border-border rounded-lg hover:border-chart-4 hover:bg-chart-4/10 transition-colors text-center">
                    <x-ui.icon name="dollar-sign" class="w-8 h-8 text-muted-foreground mx-auto mb-2 block" />
                    <span class="text-sm font-medium text-card-foreground">Coming soon </span>
                </a>
            </div>
        </div>

        <!-- Ringkasan Informasi -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            @foreach ($stats ?? [] as $stat)
                @include('components.partials.stat-card', [
                    'title' => $stat['title'] ?? '',
                    'value' => $stat['value'] ?? '',
                    'change' => $stat['change'] ?? '',
                    'icon' => $stat['icon'] ?? null,
                ])
            @endforeach
        </div>

        <!-- Insight Manager -->
        <div class="bg-card rounded-lg shadow-sm border border-border p-6">
            <h3 class="text-lg font-semibold text-card-foreground mb-4">Insight Baru</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($insights ?? [] as $insight)
                    <div class="p-4 rounded-lg border border-border {{ $insight['type'] === 'success' ? 'bg-green-50 dark:bg-green-900/20' : ($insight['type'] === 'warning' ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-blue-50 dark:bg-blue-900/20') }}">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 rounded-full {{ $insight['type'] === 'success' ? 'bg-green-100 dark:bg-green-800' : ($insight['type'] === 'warning' ? 'bg-yellow-100 dark:bg-yellow-800' : 'bg-blue-100 dark:bg-blue-800') }}">
                                <span class="{{ $insight['icon'] }} w-5 h-5 {{ $insight['type'] === 'success' ? 'text-green-600 dark:text-green-400' : ($insight['type'] === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : 'text-blue-600 dark:text-blue-400') }}"></span>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-card-foreground">{{ $insight['title'] }}</h4>
                                <p class="text-xs text-muted-foreground mt-1">{{ $insight['content'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <div class="bg-card rounded-lg shadow-sm border border-border p-6">
                <h3 class="text-lg font-semibold text-card-foreground mb-2">Tren Kehadiran Mingguan</h3>
                <p class="text-sm text-muted-foreground mb-4">Check-in dalam 7 hari terakhir</p>
                <div class="h-80">
                    <canvas id="weeklyTrendChart"></canvas>
                </div>
            </div>

            <div class="bg-card rounded-lg shadow-sm border border-border p-6">
                <h3 class="text-lg font-semibold text-card-foreground mb-2">Distribusi Member</h3>
                <p class="text-sm text-muted-foreground mb-4">Sebaran member berdasarkan aktivitas</p>
                <div class="h-80">
                    <canvas id="memberDistributionChart"></canvas>
                </div>
            </div>

            <div class="bg-card rounded-lg shadow-sm border border-border p-6">
                <h3 class="text-lg font-semibold text-card-foreground mb-2">Aktivitas Hari Ini</h3>
                <p class="text-sm text-muted-foreground mb-4">Kehadiran saat ini vs target</p>
                <div class="h-80">
                    <canvas id="dailyActivityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Check-in Terbaru -->
        <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
            <div class="bg-card rounded-lg shadow-sm border border-border p-6">
                <h3 class="text-lg font-semibold text-card-foreground mb-4">Check-in Terbaru</h3>
                <div class="space-y-3">
                    @forelse ($activities ?? [] as $activity)
                        <div class="flex items-center justify-between p-3 hover:bg-muted/50 rounded-lg transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-chart-1/20 rounded-full flex items-center justify-center">
                                    <span class="icon-user-check w-4 h-4 text-chart-1"></span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-card-foreground">{{ $activity['name'] ?? '' }}</p>
                                    <p class="text-xs text-muted-foreground">{{ $activity['time'] ?? '' }}</p>
                                </div>
                            </div>
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full {{ ($activity['type'] ?? '') === 'Check-in' ? 'bg-chart-2/20 text-chart-2' : 'bg-destructive/20 text-destructive' }}">{{ $activity['type'] ?? '' }}</span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-muted-foreground">
                            <span class="icon-activity w-12 h-12 mx-auto mb-2 block"></span>
                            <p>Belum ada aktivitas terbaru</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Debug: Log chart data
            console.log('Charts data:', @json($charts));
            console.log('Weekly trend:', @json($charts['weekly_trend']));
            console.log('Member distribution:', @json($charts['member_distribution']));
            console.log('Daily activity:', @json($charts['daily_activity']));
            // Weekly Trend Chart
            const weeklyTrendCtx = document.getElementById('weeklyTrendChart');
            if (weeklyTrendCtx) {
                const weeklyTrendChart = new Chart(weeklyTrendCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: @json($charts['weekly_trend']['labels']),
                        datasets: [{
                            label: 'Check-in',
                            data: @json($charts['weekly_trend']['data']),
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // Member Distribution Chart
            const memberDistCtx = document.getElementById('memberDistributionChart');
            if (memberDistCtx) {
                new Chart(memberDistCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: @json($charts['member_distribution']['labels']),
                        datasets: [{
                            data: @json($charts['member_distribution']['data']),
                            backgroundColor: @json($charts['member_distribution']['colors']),
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            }
                        }
                    }
                });
            }

            // Daily Activity Chart
            const dailyActivityCtx = document.getElementById('dailyActivityChart');
            if (dailyActivityCtx) {
                const dailyData = @json($charts['daily_activity']);
                new Chart(dailyActivityCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Tercapai', 'Sisa'],
                        datasets: [{
                            data: [dailyData.current || 0, Math.max(0, (dailyData.target || 100) - (dailyData.current || 0))],
                            backgroundColor: ['#10B981', '#E5E7EB'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
@endsection
