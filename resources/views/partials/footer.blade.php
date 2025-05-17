@unless (Request::is('settings') || Request::is('booking*') || Request::is('admin*'))
<footer class="footer bg-dark text-white py-3 mt-3">
    <div class="container text-center">
        <p>&copy; {{ date('Y') }} Waku Services. All rights reserved.</p>
    </div>
</footer>
@endunless
