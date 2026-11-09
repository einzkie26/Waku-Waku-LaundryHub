@php
    $detergentBrand = $bookingOld('detergent_brand', '');
    $softenerBrand = $bookingOld('softener_brand', 'none');
    $sachets = is_numeric($bookingOld('detergent_sachets')) ? $bookingOld('detergent_sachets') : 0;
    $cups = is_numeric($bookingOld('softener_cups')) ? $bookingOld('softener_cups') : 0;
    $liquidsAllowed = \App\Support\LaundryProducts::supports($selectedService);
    $cupMl = \App\Support\LaundryProducts::CUP_MICROLITRES / 1000;
@endphp
<div data-liquid-products @if(!$liquidsAllowed) hidden @endif>
    <fieldset class="product-group">
        <legend class="product-heading"><span>Liquid detergent</span><span class="product-rate">₱{{ \App\Support\LaundryProducts::DETERGENT_SACHET_CENTS / 100 }} / sachet</span></legend>
        <div class="product-choices detergent-choices">
            @foreach(\App\Support\LaundryProducts::DETERGENTS as $key => $brand)
                <label class="product-choice">
                    <input type="radio" name="detergent_brand" value="{{ $key }}" @checked($detergentBrand === $key) @disabled(!$liquidsAllowed) required>
                    <span class="product-choice-body"><img src="{{ asset('img/laundry-brands/'.$brand['logo']) }}" alt="" width="64" height="48"><strong>{{ $brand['name'] }}</strong></span>
                </label>
            @endforeach
            <label class="product-choice"><input type="radio" name="detergent_brand" value="own" @checked($detergentBrand === 'own') @disabled(!$liquidsAllowed) required><span class="product-choice-body product-choice-own"><i class="bi bi-bag-check" aria-hidden="true"></i><strong>Bring my own</strong><small>No added charge</small></span></label>
        </div>
        @error('detergent_brand', $bookingErrorBag)<p class="text-danger small mt-2 mb-0">{{ $message }}</p>@enderror
        <div class="sachet-quantity" data-detergent-controls @if(!$detergentBrand || $detergentBrand === 'own') hidden @endif>
            <div class="sachet-preview" aria-hidden="true"><i class="bi bi-bag-fill"></i><span data-sachet-count>{{ $sachets ?: 1 }}</span></div>
            <div class="quantity-field"><label class="form-label" for="detergent-sachets">Number of sachets</label><div class="quantity-stepper"><button type="button" data-quantity-target="detergent-sachets" data-quantity-step="-1" aria-label="Remove one sachet">−</button><input class="form-control" id="detergent-sachets" name="detergent_sachets" type="number" min="1" max="50" step="1" value="{{ $sachets ?: 1 }}" @disabled(!$liquidsAllowed || !$detergentBrand || $detergentBrand === 'own')><button type="button" data-quantity-target="detergent-sachets" data-quantity-step="1" aria-label="Add one sachet">+</button></div></div>
            <strong class="quantity-price" data-detergent-price>₱0.00</strong>
        </div>
        @error('detergent_sachets', $bookingErrorBag)<p class="text-danger small mt-2 mb-0">{{ $message }}</p>@enderror
    </fieldset>

    <fieldset class="product-group">
        <legend class="product-heading"><span>Fabric softener</span><span class="product-rate">₱{{ \App\Support\LaundryProducts::SOFTENER_LITRE_CENTS / 100 }} / liter</span></legend>
        <div class="product-choices softener-choices">
            @foreach(\App\Support\LaundryProducts::SOFTENERS as $key => $brand)
                <label class="product-choice"><input type="radio" name="softener_brand" value="{{ $key }}" @checked($softenerBrand === $key) @disabled(!$liquidsAllowed)><span class="product-choice-body"><img src="{{ asset('img/laundry-brands/'.$brand['logo']) }}" alt="" width="64" height="48"><strong>{{ $brand['name'] }}</strong></span></label>
            @endforeach
            <label class="product-choice"><input type="radio" name="softener_brand" value="none" @checked(!$softenerBrand || $softenerBrand === 'none') @disabled(!$liquidsAllowed)><span class="product-choice-body product-choice-own"><i class="bi bi-slash-circle" aria-hidden="true"></i><strong>No softener</strong><small>No added charge</small></span></label>
        </div>
        @error('softener_brand', $bookingErrorBag)<p class="text-danger small mt-2 mb-0">{{ $message }}</p>@enderror
        <div class="softener-measure" data-softener-controls @if(!$softenerBrand || $softenerBrand === 'none') hidden @endif>
            <div class="cup-control" data-cup-control role="slider" tabindex="0" aria-label="Fabric softener cups" aria-valuemin="0.5" aria-valuemax="1000" aria-valuenow="{{ $cups ?: 1 }}" aria-describedby="cup-instructions cup-unit">
                <div class="measuring-cup" aria-hidden="true">
                    <div class="cup-liquid-clip"><div class="cup-liquid"><span class="cup-wave"></span><i></i><i></i></div></div>
                    <svg class="cup-outline" viewBox="0 0 160 170" focusable="false"><path class="cup-handle" d="M119 50 H132 Q151 50 151 68 V94 Q151 114 128 114 H114"/><path class="cup-glass" d="M23 22 H125 L113 148 Q112 154 105 154 H42 Q35 154 34 148 Z"/><path class="cup-lip" d="M18 22 H129 M40 117 H53 M37 78 H53 M34 40 H53"/><text x="57" y="43">{{ $cupMl }} mL</text><text x="57" y="81">{{ round($cupMl * 2 / 3, 3) }} mL</text><text x="57" y="120">{{ round($cupMl / 3, 3) }} mL</text></svg>
                    <span class="cup-multiplier" data-cup-multiplier>×1</span>
                </div>
                <span class="cup-swipe-hint"><i class="bi bi-arrow-down-up" aria-hidden="true"></i>Swipe to measure</span>
            </div>
            <div class="cup-quantity">
                <div class="cup-unit" id="cup-unit">1 cup = {{ $cupMl }} mL</div>
                <label class="form-label" for="softener-cups">Number of cups</label>
                <div class="quantity-stepper"><button type="button" data-quantity-target="softener-cups" data-quantity-step="-0.5" aria-label="Remove half a cup">−</button><input class="form-control" id="softener-cups" name="softener_cups" type="number" min="0.5" max="1000" step="0.5" value="{{ $cups ?: 1 }}" @disabled(!$liquidsAllowed || !$softenerBrand || $softenerBrand === 'none')><button type="button" data-quantity-target="softener-cups" data-quantity-step="0.5" aria-label="Add half a cup">+</button></div>
                <p class="form-text" id="cup-instructions">Hold + / − or swipe the cup up or down.</p>
                <div class="cup-amount"><output data-softener-volume>1.5 mL total</output><strong data-softener-price>₱0.00</strong></div>
            </div>
        </div>
        @error('softener_cups', $bookingErrorBag)<p class="text-danger small mt-2 mb-0">{{ $message }}</p>@enderror
    </fieldset>
</div>
