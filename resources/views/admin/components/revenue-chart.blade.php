@php($maximum = max(1, $report['series']->max('revenue')))
<div class="admin-chart" data-chart>
    <div class="admin-chart-readout"><strong>₱{{ number_format($report['totalRevenue'], 2) }}</strong><span>{{ $report['totalPaidOrders'] }} {{ \Illuminate\Support\Str::plural('payment', $report['totalPaidOrders']) }}</span></div>
    <div class="admin-chart-grid" role="group" aria-label="Payments received by day">
        @foreach($report['series'] as $bar)
        @php($caption = $bar['label'].' · ₱'.number_format($bar['revenue'], 2).' · '.$bar['orders'].' '.\Illuminate\Support\Str::plural('payment', $bar['orders']))
        <div class="admin-bar-slot"><button class="admin-chart-bar {{ $loop->last ? 'is-latest' : '' }}" style="height:{{ max(1.5, ($bar['revenue'] / $maximum) * 100) }}%" type="button" data-chart-bar data-caption="{{ $caption }}" aria-label="{{ $caption }}" title="{{ $caption }}"></button></div>
        @endforeach
    </div>
    <div class="admin-chart-labels {{ $report['days'] > 7 ? 'is-long' : '' }}" aria-hidden="true">@foreach($report['series'] as $bar)<span>{{ \Carbon\Carbon::parse($bar['date'])->format('j M') }}</span>@endforeach</div>
    <p class="form-text mb-0 mt-3" data-chart-caption aria-live="polite">Select a day to see its total.</p>
</div>
