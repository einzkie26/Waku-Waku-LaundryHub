// index.js

window.onload = function() {
    // Ensure the page loads at the top and doesn't jump to any section
    if (window.location.hash) {
        history.pushState("", document.title, window.location.pathname + window.location.search);
    }
    window.scrollTo(0, 0); // Scroll to top of the page
};


//booking.js

document.querySelectorAll('.service-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
    });
});

document.querySelectorAll('.time-slot').forEach(slot => {
    slot.addEventListener('click', function() {
        document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
        this.classList.add('selected');
    });
});
