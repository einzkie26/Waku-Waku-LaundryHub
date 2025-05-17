<section id="hero" class="hero py-2 bg-light">
    <div class="container text-center mb-2" data-aos="fade" data-aos-duration="900">
        <h1 class="display-4 fw-bold mb-2" data-aos="fade-up" data-aos-delay="200">Your Time, Our Laundry. Perfectly Cleaned.</h1>
        <p class="lead mb-2" data-aos="fade-up" data-aos-delay="300">We provide fast, reliable, and affordable laundry services.</p>
        <a href="#services" class="btn btn-primary btn-lg hero-btn" data-aos="fade-up" data-aos-delay="400">View Services</a>
    </div>
</section>
<style>
.hero {
    background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('../img/bg.jpg');
    background-size: cover;
    background-position: center;
    color: white;
    text-align: center;
    padding: 70px 0;
}

@media (max-width: 768px) {
    #hero h1 { font-size: 2rem; }
    #hero p { font-size: 1rem; }
}
.hero-btn {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.hero-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>
