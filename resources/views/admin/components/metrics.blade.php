<div class="admin-metrics" style="--metric-columns:{{ count($items) }}">
    @foreach($items as $item)
        @if(isset($item['href']))<a class="admin-metric {{ ($item['featured'] ?? false) ? 'is-featured' : '' }}" href="{{ $item['href'] }}">@else<div class="admin-metric {{ ($item['featured'] ?? false) ? 'is-featured' : '' }}">@endif
            <div class="admin-metric-label"><span>{{ $item['label'] }}</span><i class="bi bi-{{ $item['icon'] }}" aria-hidden="true"></i></div>
            <div class="admin-metric-value">{{ $item['value'] }}</div>
            <div class="admin-metric-note"><span class="metric-dot"></span>{{ $item['note'] }}</div>
        @if(isset($item['href']))</a>@else</div>@endif
    @endforeach
</div>
