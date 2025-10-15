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
    'bell-notification' => 'tabler-bell-filled',
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
    'plus'          => 'tabler-plus',
    'eye'           => 'tabler-eye',
    'edit'          => 'tabler-edit',
    'trash'         => 'tabler-trash',
    'user-x'        => 'tabler-user-x',
    'user-check'    => 'tabler-user-check',
    'phone'         => 'tabler-phone',
    'mail'          => 'tabler-mail',
    'map-pin'       => 'tabler-map-pin',
    'calendar-event' => 'tabler-calendar-event',
    'id'            => 'tabler-id',
    'info-circle'   => 'tabler-info-circle',
    'check'         => 'tabler-check',
    'x'             => 'tabler-x',
  ];

  $iconName = $iconMap[$name] ?? 'tabler-circle';
@endphp

@svg($iconName, $attributes->merge(['class' => 'w-5 h-5'])->get('class'))