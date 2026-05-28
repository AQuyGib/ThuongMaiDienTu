@php
    $tabs = [
        ['label' => 'Quản lý kho', 'route' => route('admin.inventory.index'), 'active' => request()->routeIs('admin.inventory.index')],
        ['label' => 'Biến động kho', 'route' => route('admin.inventory.movements'), 'active' => request()->routeIs('admin.inventory.movements')],
        ['label' => 'Cảnh báo tồn kho', 'route' => route('admin.inventory.warnings'), 'active' => request()->routeIs('admin.inventory.warnings')],
        ['label' => 'Phiếu nhập kho', 'route' => route('admin.purchase-orders.index'), 'active' => request()->routeIs('admin.purchase-orders.index') || request()->routeIs('admin.purchase-orders.show')],
        ['label' => 'Tạo phiếu nhập', 'route' => route('admin.purchase-orders.create'), 'active' => request()->routeIs('admin.purchase-orders.create')],
    ];
@endphp

<div class="page-tabs">
    @foreach($tabs as $tab)
        <a href="{{ $tab['route'] }}" class="page-tab {{ $tab['active'] ? 'active' : '' }}">{{ $tab['label'] }}</a>
    @endforeach
</div>