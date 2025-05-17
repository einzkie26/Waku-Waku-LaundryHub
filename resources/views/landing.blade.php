@extends('layouts.app')

@section('content')
    @include('partials.hero')
    @include('modal.notification')
    @include('modal.viewbook')
    @include('partials.services')
    @include('partials.how-it-works')
    @include('partials.pricing')

    </div>
</div>

<!-- Back to Top Button -->
<button id="backToTop" class="btn btn-primary back-to-top" title="Back to Top">
    <i class="fas fa-arrow-up"></i>
</button>

<style>
.back-to-top {
    position: fixed;
    bottom: 20px;
    right: 20px;
    display: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    text-align: center;
    line-height: 40px;
    font-size: 20px;
    z-index: 1000;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.back-to-top:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const backToTopButton = document.getElementById('backToTop');
    
    // Show button when page is scrolled down
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > window.innerHeight / 2) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    });
    
    // Scroll to top when button is clicked
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>
@endsection
