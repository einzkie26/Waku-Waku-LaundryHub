@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/hero-laundry.css') }}?v={{ filemtime(public_path('css/hero-laundry.css')) }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('js/hero-laundry.js') }}?v={{ filemtime(public_path('js/hero-laundry.js')) }}" defer></script>
@endpush

@section('content')
<section class="hero-section" data-laundry-hero>
    <div class="container hero-grid">
        <div class="hero-copy">
            <div class="eyebrow">Fresh clothes. A lighter day.</div>
            <h1>A little less laundry.<br>A little <em>more life.</em></h1>
            <p class="hero-description">Leave the laundry to us. Book your pickup, follow its progress, and get back to the things you love.</p>
            <div class="hero-actions">
                <a href="{{ route('booking.create') }}" class="btn btn-primary" data-hero-machine="wash">Book a pickup <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></a>
                <h4 class="hero-or">or</h4>
                <a href="{{ auth()->check() ? route('customer.orders.history') : '#services' }}" class="btn btn-light" data-hero-machine="spin">{{ auth()->check() ? 'Track my laundry' : 'Spin and wash your clothes' }}</a>
            </div>
        </div>
        <div class="hero-art" role="img" aria-label="A Waku-Waku washing machine with clothes and water behind its glass door" data-washer data-machine-mode="idle">
            <div class="art-tag art-tag-top"><i class="bi bi-stars me-2" aria-hidden="true"></i>A fresh start for your clothes</div>
            <div class="washer-motion" aria-hidden="true">
                <div class="washer">
                    <div class="washer-top">
                        <div class="washer-drawer"></div>
                        <div class="washer-dial"></div>
                        <div class="washer-readout"><small data-washer-cycle>Ready</small><b data-washer-value>00:00</b></div>
                        <div class="washer-light"></div>
                    </div>
                    <div class="washer-porthole">
                        <div class="washer-door">
                            <div class="washer-drum" data-washer-drum>
                                <div class="washer-drum-texture"></div>
                                <span class="washer-cloth washer-cloth-mint"></span>
                                <span class="washer-cloth washer-cloth-cream"></span>
                                <span class="washer-cloth washer-cloth-sage"></span>
                            </div>
                            <div class="washer-water">
                                <svg class="washer-wave washer-wave-back" viewBox="0 0 480 160" preserveAspectRatio="none" focusable="false"><path d="M0 44 C40 20 80 20 120 44 S200 68 240 44 C280 20 320 20 360 44 S440 68 480 44 V160 H0 Z" /></svg>
                                <svg class="washer-wave washer-wave-front" viewBox="0 0 480 160" preserveAspectRatio="none" focusable="false"><path d="M0 44 C40 20 80 20 120 44 S200 68 240 44 C280 20 320 20 360 44 S440 68 480 44 V160 H0 Z" /></svg>
                            </div>
                            <div class="washer-bubbles"><i></i><i></i><i></i><i></i></div>
                            <div class="washer-glass"></div>
                        </div>
                        <div class="washer-handle"></div>
                    </div>
                    <div class="washer-base"><span class="washer-brand">WAKU-WAKU</span><span class="washer-vent"></span><span class="washer-filter"></span></div>
                    <div class="washer-foot washer-foot-left"></div><div class="washer-foot washer-foot-right"></div>
                </div>
            </div>
            <div class="art-tag art-tag-bottom"><i class="bi bi-basket me-2" aria-hidden="true"></i>From your basket, back to you.</div>
            <div class="art-sparkle" aria-hidden="true">✳</div>
        </div>
    </div>
</section>
@auth
    @if($activeOrders->isNotEmpty())
    <section class="container section-space">
        <div class="section-heading"><div><div class="eyebrow">Your laundry, at a glance</div><h2>In good hands.</h2></div><a href="{{ route('customer.orders.history') }}">View all my orders <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
        @foreach($activeOrders as $order) @include('customer.orders.card', ['order' => $order]) @endforeach
    </section>
    @endif
@endauth
<section id="services" class="container section-space">
    <div class="section-heading"><div><div class="eyebrow">A little care goes a long way</div><h2>The right clean for every load.</h2></div></div>
    <div class="service-grid">
        @foreach(\App\Support\LaundryWorkflow::SERVICES as $name => $service)
        <article class="service-tile">
            <div class="service-symbol"><i class="bi bi-{{ $service['icon'] }}" aria-hidden="true"></i></div>
            <h3>{{ $name }}</h3><p>{{ $service['description'] }}</p>
            <strong>₱{{ number_format($service['price'], 0) }}<small> / kg</small></strong>
            <a href="{{ route('booking.create', ['service' => $name]) }}">Choose {{ $name }} <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </article>
        @endforeach
    </div>
    <p class="form-text mt-3">Your total is the service price × weight, plus any optional extras you choose. You will see the full breakdown before confirming.</p>
</section>
<section id="how-it-works" class="container section-space">
    <div class="how-panel">
        <div class="eyebrow">From to-do to ta-da</div><h2>Three steps to a fresher day.</h2>
        <div class="how-grid">
            <div><div class="how-number">01</div><h3>Choose & schedule</h3><p>Choose a service, enter the weight, and pick a collection time. Check your total and confirm.</p></div>
            <div><div class="how-number">02</div><h3>We pick up & clean</h3><p>Have your laundry ready at your saved address. Follow pickup and cleaning updates in My orders.</p></div>
            <div><div class="how-number">03</div><h3>Fresh laundry, back to you</h3><p>When it is ready, our team arranges delivery. Check your payment status to see what is still due.</p></div>
        </div>
    </div>
</section>
@endsection
