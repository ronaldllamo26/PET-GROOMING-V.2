<?php
// index.php
require_once 'config/auth.php';
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'views/admin/dashboard.php' : 'views/user/dashboard.php'));
    exit;
}
$rootPath = '';
$pageTitle = 'Home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/head.php'; ?>
</head>
<body>

<!-- ══ NAVBAR ══ -->
<nav class="site-nav">
  <div class="container">
    <a href="index.php" class="nav-brand">
      <svg class="nav-brand-logo" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="18" cy="18" r="18" fill="#3B2F2F"/>
        <path d="M12 22c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="#C4A882" stroke-width="1.5" stroke-linecap="round"/>
        <circle cx="13" cy="13" r="2" fill="#C4A882"/>
        <circle cx="23" cy="13" r="2" fill="#C4A882"/>
        <circle cx="10" cy="17" r="1.5" fill="#C4A882"/>
        <circle cx="26" cy="17" r="1.5" fill="#C4A882"/>
      </svg>
      <span class="nav-brand-text">PawCare</span>
    </a>

    <ul class="nav-links">
      <li><a href="#services">Services</a></li>
      <li><a href="#gallery">Gallery</a></li>
      <li><a href="#about">About</a></li>
      <li><a href="#testimonials">Reviews</a></li>
    </ul>

    <div class="nav-actions">
      <a href="login.php" class="btn-outline-site btn-sm-site">Sign In</a>
      <a href="register.php" class="btn-primary-site btn-sm-site">Book Now</a>
    </div>
  </div>
</nav>

<!-- ══ HERO ══ -->
<section class="hero">
  <img
    src="https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=1600&q=80&auto=format&fit=crop"
    alt="Golden Retriever being groomed"
    class="hero-image"
  />
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="container">
      <div class="row">
        <div class="col-lg-7 col-xl-6">
          <p class="hero-eyebrow">Professional Pet Grooming</p>
          <h1 class="hero-title">
            Expert Care<br/>
            for Your<br/>
            Beloved Pet
          </h1>
          <p class="hero-desc">
            Certified groomers dedicated to keeping your pet healthy, comfortable, and looking their very best — every visit.
          </p>
          <div class="hero-cta">
            <a href="register.php" class="btn-primary-site">Book an Appointment</a>
            <a href="#services" class="btn-outline-site" style="border-color:rgba(255,255,255,0.5);color:#fff;">View Services</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ STRIP STATS ══ -->
<section style="background: var(--cream-dark); border-bottom: 1px solid var(--gray-light);">
  <div class="container">
    <div class="row text-center py-4">
      <div class="col-4">
        <div style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--brown);">1,200+</div>
        <div style="font-size: 0.78rem; letter-spacing: 1px; text-transform: uppercase; color: var(--text-muted);">Happy Pets</div>
      </div>
      <div class="col-4" style="border-left: 1px solid var(--gray-light); border-right: 1px solid var(--gray-light);">
        <div style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--brown);">8 Yrs</div>
        <div style="font-size: 0.78rem; letter-spacing: 1px; text-transform: uppercase; color: var(--text-muted);">In Business</div>
      </div>
      <div class="col-4">
        <div style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--brown);">5.0</div>
        <div style="font-size: 0.78rem; letter-spacing: 1px; text-transform: uppercase; color: var(--text-muted);">Average Rating</div>
      </div>
    </div>
  </div>
</section>

<!-- ══ SERVICES ══ -->
<section class="py-section services-section" id="services">
  <div class="container">
    <div class="row mb-5">
      <div class="col-lg-5">
        <p class="section-eyebrow">What We Offer</p>
        <h2 class="section-title">Our Grooming Services</h2>
        <div class="divider-line"></div>
        <p class="section-sub">Every service is performed by our trained, certified groomers using only the finest pet-safe products.</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <div class="service-card">
          <img src="https://images.unsplash.com/photo-1516734212186-a967f81ad0d7?w=400&q=80&auto=format&fit=crop" alt="Bath & Dry" class="service-card-img"/>
          <div class="service-card-body">
            <h5 class="service-card-title">Bath & Dry</h5>
            <p class="service-card-desc">Gentle shampoo, conditioner, and professional blow-dry using pet-safe products.</p>
            <div class="service-price">₱350 <small>/ session</small></div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="service-card">
          <img src="https://images.unsplash.com/photo-1591946614720-90a587da4a36?w=400&q=80&auto=format&fit=crop" alt="Haircut" class="service-card-img"/>
          <div class="service-card-body">
            <h5 class="service-card-title">Haircut & Styling</h5>
            <p class="service-card-desc">Breed-specific cuts or custom styles crafted by our experienced pet stylists.</p>
            <div class="service-price">₱500 <small>/ session</small></div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="service-card">
          <img src="https://images.unsplash.com/photo-1611003228941-98852ba62227?w=400&q=80&auto=format&fit=crop" alt="Nail Trim" class="service-card-img"/>
          <div class="service-card-body">
            <h5 class="service-card-title">Nail Trimming</h5>
            <p class="service-card-desc">Safe clipping and filing to keep your pet comfortable and scratch-free.</p>
            <div class="service-price">₱150 <small>/ session</small></div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="service-card">
          <img src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400&q=80&auto=format&fit=crop" alt="Full Spa" class="service-card-img"/>
          <div class="service-card-body">
            <h5 class="service-card-title">Full Spa Package</h5>
            <p class="service-card-desc">Bath, haircut, nail trim, ear cleaning, and a finishing spritz of pet cologne.</p>
            <div class="service-price">₱900 <small>/ session</small></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ ABOUT / SPLIT ══ -->
<section class="py-section" id="about">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
          <img src="https://images.unsplash.com/photo-1530281700549-e82e7bf110d6?w=400&q=80&auto=format&fit=crop" alt="Dog" style="border-radius: var(--radius); width:100%; height: 280px; object-fit: cover;"/>
          <img src="https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&q=80&auto=format&fit=crop" alt="Cat" style="border-radius: var(--radius); width:100%; height: 280px; object-fit: cover; margin-top: 2rem;"/>
        </div>
      </div>
      <div class="col-lg-5 offset-lg-1">
        <p class="section-eyebrow">Our Story</p>
        <h2 class="section-title">Passionate About Pets Since 2017</h2>
        <div class="divider-line"></div>
        <p class="section-sub mb-3">
          PawCare was founded on a simple belief — every pet deserves to be treated with the same care and attention we give our own family. Our certified groomers bring years of experience and genuine love for animals to every session.
        </p>
        <p class="section-sub mb-4">
          We use only premium, pet-safe products and maintain the highest standards of hygiene and comfort in our studio. Your pet's well-being is always our top priority.
        </p>
        <a href="register.php" class="btn-primary-site">Schedule a Visit</a>
      </div>
    </div>
  </div>
</section>

<!-- ══ GALLERY ══ -->
<section class="py-section gallery-section bg-cream" id="gallery">
  <div class="container">
    <div class="row mb-4">
      <div class="col-lg-5">
        <p class="section-eyebrow">Our Clients</p>
        <h2 class="section-title">Fresh From the Studio</h2>
        <div class="divider-line"></div>
      </div>
    </div>
    <div class="gallery-grid">
      <div class="gallery-item tall">
        <img src="https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400&q=80&auto=format&fit=crop" alt="Dog after grooming"/>
      </div>
      <div class="gallery-item">
        <img src="https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=400&q=80&auto=format&fit=crop" alt="Dog portrait"/>
      </div>
      <div class="gallery-item">
        <img src="https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13?w=400&q=80&auto=format&fit=crop" alt="Cat portrait"/>
      </div>
      <div class="gallery-item">
        <img src="https://images.unsplash.com/photo-1583511655826-05700d52f4d9?w=400&q=80&auto=format&fit=crop" alt="Husky dog"/>
      </div>
      <div class="gallery-item">
        <img src="https://images.unsplash.com/photo-1561037404-61cd46aa615b?w=400&q=80&auto=format&fit=crop" alt="Labrador"/>
      </div>
      <div class="gallery-item">
        <img src="https://images.unsplash.com/photo-1495360010541-f48722b34f7d?w=400&q=80&auto=format&fit=crop" alt="Orange cat"/>
      </div>
      <div class="gallery-item tall">
        <img src="https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400&q=80&auto=format&fit=crop" alt="Cat groomed"/>
      </div>
    </div>
  </div>
</section>

<!-- ══ WHY US ══ -->
<section class="features-section">
  <div class="container">
    <div class="row g-0">
      <div class="col-md-3 feature-item">
        <svg class="feature-icon" fill="none" stroke="#C4A882" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="feature-title">Certified Groomers</div>
        <p class="feature-desc">All our staff are professionally trained and certified in pet grooming and handling.</p>
      </div>
      <div class="col-md-3 feature-item">
        <svg class="feature-icon" fill="none" stroke="#C4A882" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
        <div class="feature-title">Pet-Safe Products</div>
        <p class="feature-desc">We use only hypoallergenic, vet-approved grooming products that are gentle on all breeds.</p>
      </div>
      <div class="col-md-3 feature-item">
        <svg class="feature-icon" fill="none" stroke="#C4A882" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
        <div class="feature-title">Easy Booking</div>
        <p class="feature-desc">Schedule and manage your appointments online — anytime, from any device.</p>
      </div>
      <div class="col-md-3 feature-item">
        <svg class="feature-icon" fill="none" stroke="#C4A882" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
        <div class="feature-title">5-Star Rated</div>
        <p class="feature-desc">Consistently rated 5 stars by our clients for quality, care, and professionalism.</p>
      </div>
    </div>
  </div>
</section>

<!-- ══ TESTIMONIALS ══ -->
<section class="py-section" id="testimonials">
  <div class="container">
    <div class="text-center mb-5">
      <p class="section-eyebrow">Client Reviews</p>
      <h2 class="section-title">What Pet Owners Say</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="testi-card">
          <div class="testi-stars">★ ★ ★ ★ ★</div>
          <p class="testi-text">"My golden retriever Max has never looked so handsome. The groomers are incredibly gentle and professional. We come back every month without hesitation."</p>
          <div>
            <div class="testi-author-name">Maria Santos</div>
            <div class="testi-author-sub">Golden Retriever Owner</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testi-card">
          <div class="testi-stars">★ ★ ★ ★ ★</div>
          <p class="testi-text">"The online booking system is seamless and the staff always remembers my Shih Tzu's preferences. PawCare is truly the best grooming studio in the area."</p>
          <div>
            <div class="testi-author-name">Jose Reyes</div>
            <div class="testi-author-sub">Shih Tzu Owner</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testi-card">
          <div class="testi-stars">★ ★ ★ ★ ★</div>
          <p class="testi-text">"My cat Mochi used to dread bath time. After just one visit to PawCare, she came home calm and clean. Their patience with nervous pets is remarkable."</p>
          <div>
            <div class="testi-author-name">Ana Cruz</div>
            <div class="testi-author-sub">Cat Owner</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ CTA ══ -->
<section class="cta-banner">
  <div class="container">
    <div class="row justify-content-center text-center">
      <div class="col-lg-6">
        <p class="section-eyebrow">Get Started</p>
        <h2 class="section-title mb-3">Ready to Book Your Pet's Next Session?</h2>
        <p class="section-sub mb-4">Create a free account and schedule an appointment in under two minutes.</p>
        <div class="d-flex gap-3 justify-content-center">
          <a href="register.php" class="btn-primary-site">Create Free Account</a>
          <a href="login.php" class="btn-outline-site">Sign In</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ FOOTER ══ -->
<footer class="site-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand">PawCare</div>
        <p class="footer-desc mt-2">Premium pet grooming studio. Because every pet deserves to look and feel their absolute best.</p>
      </div>
      <div class="col-lg-2 offset-lg-2">
        <div class="footer-heading">Navigate</div>
        <a href="#services" class="footer-link">Services</a>
        <a href="#gallery" class="footer-link">Gallery</a>
        <a href="#about" class="footer-link">About</a>
        <a href="#testimonials" class="footer-link">Reviews</a>
      </div>
      <div class="col-lg-2">
        <div class="footer-heading">Account</div>
        <a href="login.php" class="footer-link">Sign In</a>
        <a href="register.php" class="footer-link">Register</a>
      </div>
      <div class="col-lg-2">
        <div class="footer-heading">Contact</div>
        <a class="footer-link">123 Pet Street</a>
        <a class="footer-link">Quezon City, Metro Manila</a>
        <a class="footer-link">0917-123-4567</a>
        <a class="footer-link">hello@pawcare.ph</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> PawCare Grooming Studio. All rights reserved.</span>
      <span>Made with care in the Philippines</span>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
  });
});
</script>
</body>
</html>
