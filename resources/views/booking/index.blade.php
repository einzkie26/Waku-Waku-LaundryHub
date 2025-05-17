@extends('layouts.app')

@section('title', 'Waku Laundry - Order')

@section('content')
<section class="booking-section py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div id="booking-form-wrapper">
                    @include('booking.steps.step1')
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
