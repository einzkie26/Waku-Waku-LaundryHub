@if($errors->getBag($bag)->any())
    <div class="alert alert-danger" role="alert" tabindex="-1" data-modal-errors>
        <strong>Please check these details.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->getBag($bag)->all() as $message)<li>{{ $message }}</li>@endforeach
        </ul>
    </div>
@endif
