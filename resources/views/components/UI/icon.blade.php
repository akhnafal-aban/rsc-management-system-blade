@props(['name' => 'circle'])

@php
  $iconMap = [
    'calendar'      => 'tabler-calendar',
    'clock'         => 'tabler-clock',
    'user-check'    => 'tabler-user-check',
    'users'         => 'tabler-users',
    'trending-up'   => 'tabler-trending-up',
    'dollar-sign'   => 'tabler-currency-dollar',
    'bell'          => 'tabler-bell',
    'settings'      => 'tabler-settings',
    'user'          => 'tabler-user',
    'log-out'       => 'tabler-logout',
    'home'          => 'tabler-home',
    'bar-chart-3'   => 'tabler-chart-bar',
    'chevron-left'  => 'tabler-chevron-left',
    'chevron-right' => 'tabler-chevron-right',
    'search'        => 'tabler-search',
    'refresh'       => 'tabler-refresh',
    'download'      => 'tabler-download',
    'alert-circle'  => 'tabler-alert-circle',
    'loader'        => 'tabler-loader',
    'menu'          => 'tabler-menu-2',
  ];

  $iconName = $iconMap[$name] ?? 'tabler-circle';
@endphp

@svg($iconName, $attributes->merge(['class' => 'w-5 h-5'])->get('class'))