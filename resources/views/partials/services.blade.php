<section id="services" class="services py-3">
    <div class="container">
        <h2 class="text-center mb-3" data-aos="fade-up">Our Services</h2>
        <div class="row justify-content-center">
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3" data-aos="fade-up" data-aos-delay="100">
                <div class="service-item text-center p-3 h-100 shadow-sm border rounded bg-white">
                    <img src="{{ asset('img/w&f.png') }}" alt="Wash & Fold" class="img-fluid mb-2" style="max-height:100px;">
                    <h3 class="h5">Wash & Fold</h3>
                    <p class="text-muted">Perfect for everyday laundry. Clean, folded, and ready.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3" data-aos="fade-up" data-aos-delay="200">
                <div class="service-item text-center p-3 h-100 shadow-sm border rounded bg-white">
                    <img src="{{ asset('img/drycleaning.png') }}" alt="Dry Cleaning" class="img-fluid mb-2" style="max-height:100px;">
                    <h3 class="h5">Dry Cleaning</h3>
                    <p class="text-muted">Professional dry cleaning for delicate fabrics.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3" data-aos="fade-up" data-aos-delay="300">
                <div class="service-item text-center p-3 h-100 shadow-sm border rounded bg-white">
                    <img src="{{ asset('img/xpress.png') }}" alt="Express Service" class="img-fluid mb-2" style="max-height:100px;">
                    <h3 class="h5">Express Service</h3>
                    <p class="text-muted">Fast and efficient laundry service when you're in a hurry.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3" data-aos="fade-up" data-aos-delay="400">
                <div class="service-item text-center p-3 h-100 shadow-sm border rounded bg-white">
                    <img src="{{ asset('img/ironing.png') }}" alt="Ironing Service" class="img-fluid mb-2" style="max-height:100px;">
                    <h3 class="h5">Ironing Service</h3>
                    <p class="text-muted">Get your clothes perfectly ironed and ready to wear.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
@media (max-width: 991px) {
    #services .service-item { margin-bottom: 2rem; }
}
@media (max-width: 767px) {
    #services .service-item img { max-width: 80px; }
    #services h2 { font-size: 1.5rem; }
}
.service-item {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.service-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
}
</style>
