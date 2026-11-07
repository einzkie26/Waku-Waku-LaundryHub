@if(session('success'))
    <div class="alert alert-success mt-4" role="status">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger mt-4" role="alert">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger mt-4" role="alert" tabindex="-1" id="validation-errors">
        <strong>Please check these details.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif
