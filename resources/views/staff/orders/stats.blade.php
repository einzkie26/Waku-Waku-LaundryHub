<div class="stats-grid">
    @foreach([
        ['active', 'In progress', 'basket', 'active'],
        ['ready', 'Ready for delivery', 'bag-check', 'Ready'],
        ['unpaid', 'Payment still due', 'wallet2', 'unpaid'],
        ['completed', ($todayOnly ?? false) ? 'Completed today' : 'Completed orders', 'check2-circle', 'Completed']
    ] as [$key, $label, $icon, $filter])
    <a class="surface stat-card" href="{{ route('staff.orders.index', ['status' => $filter]) }}"><span class="stat-icon"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></span><span class="stat-value">{{ $counts[$key] }}</span><span class="stat-label">{{ $label }}</span></a>
    @endforeach
</div>
