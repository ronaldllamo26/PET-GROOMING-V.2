<?php
require_once 'config/auth.php';
require_once 'config/database.php';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'views/admin/dashboard.php' : 'views/user/dashboard.php'));
    exit;
}

// ✅ Dynamic services from database
$pdo      = getPDO();
$services = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY price ASC")->fetchAll();

$rootPath  = '';
$pageTitle = 'PetMalu — Premium Pet Grooming Studio';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/head.php'; ?>
  <style>
    /* ── MARQUEE ── */
    .marquee-strip {
      background: var(--tan);
      padding: 0.85rem 0;
      overflow: hidden;
      white-space: nowrap;
    }
    .marquee-inner {
      display: inline-flex;
      animation: marquee 20s linear infinite;
    }
    .marquee-item {
      font-family: var(--font-body);
      font-size: 0.68rem;
      font-weight: 500;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      color: var(--white);
      padding: 0 2.5rem;
      display: inline-flex;
      align-items: center;
      gap: 1rem;
    }
    .marquee-dot {
      width: 3px;
      height: 3px;
      border-radius: 50%;
      background: rgba(255,255,255,0.5);
      flex-shrink: 0;
    }
    @keyframes marquee {
      0% { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }

    /* ── HOW IT WORKS ── */
    .how-section {
      padding: 6rem 0;
      background: var(--brown);
    }
    .step-card { text-align: center; padding: 0 1.5rem; }
    .step-number {
      font-family: var(--font-display);
      font-size: 3.5rem;
      font-weight: 700;
      color: rgba(196,168,130,0.15);
      line-height: 1;
      margin-bottom: 0.8rem;
    }
    .step-icon {
      width: 50px;
      height: 50px;
      background: rgba(196,168,130,0.12);
      border: 1px solid rgba(196,168,130,0.25);
      border-radius: var(--radius);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.2rem;
    }
    .step-icon svg { width: 20px; height: 20px; color: var(--tan); }
    .step-title {
      font-family: var(--font-display);
      font-size: 1.15rem;
      font-weight: 600;
      color: var(--white);
      margin-bottom: 0.5rem;
    }
    .step-desc { font-size: 0.83rem; color: rgba(255,255,255,0.55); line-height: 1.7; }
    .step-arrow {
      display: flex;
      align-items: center;
      justify-content: center;
      padding-top: 1rem;
    }
    .step-arrow svg { width: 18px; height: 18px; color: rgba(196,168,130,0.25); }

    /* ── SCROLL HINT ── */
    .hero-scroll-hint {
      position: absolute;
      bottom: 2.5rem;
      left: 50%;
      transform: translateX(-50%);
      z-index: 2;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      color: rgba(255,255,255,0.35);
      font-size: 0.62rem;
      letter-spacing: 2px;
      text-transform: uppercase;
    }
    .scroll-line {
      width: 1px;
      height: 36px;
      background: linear-gradient(to bottom, rgba(255,255,255,0.35), transparent);
      animation: scrollPulse 2s ease-in-out infinite;
    }
    @keyframes scrollPulse {
      0%, 100% { opacity: 0.4; }
      50% { opacity: 0.9; }
    }

    /* ── CONTACT SECTION ── */
    .contact-section {
      padding: 5rem 0;
      background: var(--white);
      border-top: 1px solid var(--gray-light);
    }
    .contact-item {
      display: flex;
      align-items: flex-start;
      gap: 1rem;
      margin-bottom: 1.6rem;
    }
    .contact-icon {
      width: 38px;
      height: 38px;
      background: var(--cream);
      border: 1px solid var(--gray-light);
      border-radius: var(--radius);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .contact-icon svg { width: 16px; height: 16px; color: var(--brown-light); }
    .contact-label {
      font-size: 0.65rem;
      font-weight: 600;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      color: var(--text-muted);
      margin-bottom: 0.15rem;
    }
    .contact-value { font-size: 0.9rem; color: var(--text); font-weight: 500; }
    .hours-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.4rem 1.5rem;
      font-size: 0.83rem;
    }
    .hours-day { color: var(--text-muted); }
    .hours-time { color: var(--text); font-weight: 500; text-align: right; }

    @media (max-width: 768px) {
      .step-arrow { display: none; }
    }
  </style>
</head>
<body>

<!-- ── NAVBAR ── -->
<nav class="site-nav">
  <div class="container">
    <a href="index.php" class="nav-brand">
      <svg class="nav-brand-logo" viewBox="0 0 36 36" fill="none">
        <circle cx="18" cy="18" r="18" fill="#3B2F2F"/>
        <path d="M12 22c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="#C4A882" stroke-width="1.5" stroke-linecap="round"/>
        <circle cx="13" cy="13" r="2" fill="#C4A882"/>
        <circle cx="23" cy="13" r="2" fill="#C4A882"/>
        <circle cx="10" cy="17" r="1.5" fill="#C4A882"/>
        <circle cx="26" cy="17" r="1.5" fill="#C4A882"/>
      </svg>
      <span class="nav-brand-text">PetMalu</span>
    </a>
    <ul class="nav-links">
      <li><a href="#services">Services</a></li>
      <li><a href="#how">How It Works</a></li>
      <li><a href="#gallery">Gallery</a></li>
      <li><a href="#about">About</a></li>
      <li><a href="#contact">Contact</a></li>
    </ul>
    <div class="nav-actions">
      <a href="login.php" class="btn-outline-site btn-sm-site">Sign In</a>
      <a href="register.php" class="btn-primary-site btn-sm-site">Book Now</a>
    </div>
  </div>
</nav>

<!-- ── HERO ── -->
<section class="hero" style="position:relative;">
  <img src="https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=1600&q=80&auto=format&fit=crop" alt="PetMalu Grooming" class="hero-image"/>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="container">
      <div class="row">
        <div class="col-lg-7 col-xl-6">
          <p class="hero-eyebrow">PetMalu Grooming Studio</p>
          <h1 class="hero-title">
            Your pet deserves<br/>
            the <em style="font-style:italic;color:var(--tan);">best care.</em>
          </h1>
          <p class="hero-desc">
            Professional grooming services designed for the comfort and well-being of your beloved companions. Book your session today.
          </p>
          <div class="hero-cta">
            <a href="register.php" class="btn-primary-site" style="padding:0.85rem 2rem;">Book a Session</a>
            <a href="#services" class="btn-outline-site" style="padding:0.85rem 2rem;border-color:rgba(255,255,255,0.4);color:#fff;">View Services</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="hero-scroll-hint">
    <div class="scroll-line"></div>
    <span>Scroll</span>
  </div>
</section>

<!-- ── MARQUEE ── -->
<div class="marquee-strip">
  <div class="marquee-inner">
    <?php for ($i = 0; $i < 2; $i++): ?>
    <span class="marquee-item">Professional Grooming <span class="marquee-dot"></span></span>
    <span class="marquee-item">Certified Groomers <span class="marquee-dot"></span></span>
    <span class="marquee-item">Safe & Hygienic <span class="marquee-dot"></span></span>
    <span class="marquee-item">Online Booking <span class="marquee-dot"></span></span>
    <span class="marquee-item">Pet-Friendly Studio <span class="marquee-dot"></span></span>
    <span class="marquee-item">Trusted by Pet Owners <span class="marquee-dot"></span></span>
    <span class="marquee-item">PetMalu Grooming <span class="marquee-dot"></span></span>
    <?php endfor; ?>
  </div>
</div>

<!-- ── STATS STRIP ── -->
<section style="background:var(--cream-dark);border-bottom:1px solid var(--gray-light);">
  <div class="container">
    <div class="row text-center py-4">
      <div class="col-4">
        <div style="font-family:var(--font-display);font-size:2rem;font-weight:700;color:var(--brown);">1,200+</div>
        <div style="font-size:0.72rem;letter-spacing:1.5px;text-transform:uppercase;color:var(--text-muted);">Happy Pets</div>
      </div>
      <div class="col-4" style="border-left:1px solid var(--gray-light);border-right:1px solid var(--gray-light);">
        <div style="font-family:var(--font-display);font-size:2rem;font-weight:700;color:var(--brown);">1 Yrs</div>
        <div style="font-size:0.72rem;letter-spacing:1.5px;text-transform:uppercase;color:var(--text-muted);">In Business</div>
      </div>
      <div class="col-4">
        <div style="font-family:var(--font-display);font-size:2rem;font-weight:700;color:var(--brown);">5.0</div>
        <div style="font-size:0.72rem;letter-spacing:1.5px;text-transform:uppercase;color:var(--text-muted);">Average Rating</div>
      </div>
    </div>
  </div>
</section>

<!-- ── SERVICES ── -->
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
    <?php
    $svcImgs = [
      'https://images.unsplash.com/photo-1516734212186-a967f81ad0d7?w=600&q=80&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1591946614720-90a587da4a36?w=600&q=80&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=600&q=80&auto=format&fit=crop',
      'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=600&q=80&auto=format&fit=crop',
    ];
    ?>
    <div class="row g-4">
      <?php if (empty($services)): ?>
        <!-- Fallback static cards if no services in DB -->
        <?php
        $staticSvcs = [
          ['name' => 'Bath & Dry', 'desc' => 'Gentle shampoo, conditioner, and professional blow-dry using pet-safe products.', 'price' => '350.00'],
          ['name' => 'Haircut & Styling', 'desc' => 'Breed-specific cuts or custom styles crafted by our experienced pet stylists.', 'price' => '500.00'],
          ['name' => 'Nail Trimming', 'desc' => 'Safe clipping and filing to keep your pet comfortable and scratch-free.', 'price' => '150.00'],
          ['name' => 'Full Spa Package', 'desc' => 'Bath, haircut, nail trim, ear cleaning, and a finishing spritz of pet cologne.', 'price' => '900.00'],
        ];
        foreach ($staticSvcs as $idx => $s): ?>
        <div class="col-md-6 col-lg-3">
          <div class="service-card">
            <img src="<?= $svcImgs[$idx % 4] ?>" alt="<?= htmlspecialchars($s['name']) ?>" class="service-card-img"/>
            <div class="service-card-body">
              <h5 class="service-card-title"><?= htmlspecialchars($s['name']) ?></h5>
              <p class="service-card-desc"><?= htmlspecialchars($s['desc']) ?></p>
              <div class="d-flex align-items-center justify-content-between">
                <div class="service-price">₱<?= $s['price'] ?> <small>/ session</small></div>
                <a href="register.php" class="btn-primary-site btn-sm-site">Book</a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <?php foreach ($services as $idx => $s): ?>
        <div class="col-md-6 col-lg-3">
          <div class="service-card">
            <img src="<?= $svcImgs[$idx % 4] ?>" alt="<?= htmlspecialchars($s['name']) ?>" class="service-card-img"/>
            <div class="service-card-body">
              <h5 class="service-card-title"><?= htmlspecialchars($s['name']) ?></h5>
              <p class="service-card-desc"><?= htmlspecialchars($s['description'] ?? 'Professional grooming service for your beloved pet.') ?></p>
              <div class="d-flex align-items-center justify-content-between">
                <div class="service-price">₱<?= number_format($s['price'],2) ?> <small>/ session</small></div>
                <a href="register.php" class="btn-primary-site btn-sm-site">Book</a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ── HOW IT WORKS ── -->
<section class="how-section" id="how">
  <div class="container">
    <div class="text-center mb-5">
      <p class="section-eyebrow" style="color:var(--tan);">Simple Process</p>
      <h2 class="section-title" style="color:var(--white);">How It Works</h2>
      <div class="divider-line" style="margin:1.2rem auto;"></div>
    </div>
    <div class="row align-items-start justify-content-center">
      <?php
      $steps = [
        ['num'=>'01','title'=>'Create an Account','desc'=>'Sign up in under a minute. Verify your email and you\'re ready to go.','icon'=>'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
        ['num'=>'02','title'=>'Choose a Service','desc'=>'Browse our grooming packages and pick the one that suits your pet best.','icon'=>'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z'],
        ['num'=>'03','title'=>'Book Your Slot','desc'=>'Pick your preferred date and time. We\'ll confirm your booking via email.','icon'=>'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
        ['num'=>'04','title'=>'Visit & Enjoy','desc'=>'Bring your pet in and let us do the rest. Pick them up fresh and happy.','icon'=>'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z'],
      ];
      foreach ($steps as $i => $step): ?>

      <div class="col-md-3 col-6" style="position:relative;">
        <div class="step-card">
          <div class="step-number"><?= $step['num'] ?></div>
          <div class="step-icon">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="<?= $step['icon'] ?>"/>
            </svg>
          </div>
          <h4 class="step-title"><?= $step['title'] ?></h4>
          <p class="step-desc"><?= $step['desc'] ?></p>
        </div>

        <!-- Arrow between steps -->
        <?php if ($i < 3): ?>
        <div style="position:absolute;top:60px;right:-12px;z-index:2;display:none;" class="d-md-block">
          <svg fill="none" stroke="rgba(196,168,130,0.25)" stroke-width="1.5" viewBox="0 0 24 24" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
          </svg>
        </div>
        <?php endif; ?>
      </div>

      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── GALLERY ── -->
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

<!-- ── ABOUT ── -->
<section class="py-section" id="about">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <img src="https://images.unsplash.com/photo-1530281700549-e82e7bf110d6?w=400&q=80&auto=format&fit=crop" alt="Dog" style="border-radius:var(--radius);width:100%;height:280px;object-fit:cover;"/>
          <img src="https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&q=80&auto=format&fit=crop" alt="Cat" style="border-radius:var(--radius);width:100%;height:280px;object-fit:cover;margin-top:2rem;"/>
        </div>
      </div>
      <div class="col-lg-5 offset-lg-1">
        <p class="section-eyebrow">Our Story</p>
        <h2 class="section-title">Passionate About Pets Since 2026</h2>
        <div class="divider-line"></div>
        <p class="section-sub mb-3">PetMalu was founded on a simple belief — every pet deserves to be treated with the same care and attention we give our own family. Our certified groomers bring years of experience and genuine love for animals to every session.</p>
        <p class="section-sub mb-4">We use only premium, pet-safe products and maintain the highest standards of hygiene and comfort in our studio. Your pet's well-being is always our top priority.</p>
        <a href="register.php" class="btn-primary-site">Schedule a Visit</a>
      </div>
    </div>
  </div>
</section>

<!-- ── WHY US ── -->
<section class="features-section">
  <div class="container">
    <div class="row g-0">
      <?php
      $features = [
        ['icon'=>'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z','title'=>'Certified Groomers','desc'=>'All our staff are professionally trained and certified in pet grooming and handling.'],
        ['icon'=>'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z','title'=>'Pet-Safe Products','desc'=>'We use only hypoallergenic, vet-approved grooming products gentle on all breeds.'],
        ['icon'=>'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5','title'=>'Easy Online Booking','desc'=>'Schedule and manage your appointments online — anytime, from any device.'],
        ['icon'=>'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z','title'=>'5-Star Rated','desc'=>'Consistently rated 5 stars by our clients for quality, care, and professionalism.'],
      ];
      foreach ($features as $f): ?>
      <div class="col-md-3 feature-item">
        <svg class="feature-icon" fill="none" stroke="#C4A882" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="<?= $f['icon'] ?>"/>
        </svg>
        <div class="feature-title"><?= $f['title'] ?></div>
        <p class="feature-desc"><?= $f['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── TESTIMONIALS ── -->
<section class="py-section" id="testimonials">
  <div class="container">
    <div class="text-center mb-5">
      <p class="section-eyebrow">Client Reviews</p>
      <h2 class="section-title">What Pet Owners Say</h2>
      <div class="divider-line" style="margin:1rem auto;"></div>
    </div>
    <div class="row g-4">
      <?php
      $testis = [
        ['name'=>'Lebron James','sub'=>'Golden Retriever Owner','text'=>'My golden retriever Max has never looked so handsome. The groomers are incredibly gentle and professional. We come back every month without hesitation.'],
        ['name'=>'Jose Reyes','sub'=>'Shih Tzu Owner','text'=>'The online booking system is seamless and the staff always remembers my Shih Tzu\'s preferences. PetMalu is truly the best grooming studio in the area.'],
        ['name'=>'Ana Cruz','sub'=>'Cat Owner','text'=>'My cat Mochi used to dread bath time. After just one visit to PetMalu, she came home calm and clean. Their patience with nervous pets is remarkable.'],
      ];
      foreach ($testis as $t): ?>
      <div class="col-md-4">
        <div class="testi-card">
          <div class="testi-stars">
            <?php for ($i = 0; $i < 5; $i++): ?>
            <svg fill="var(--tan)" viewBox="0 0 24 24" width="14" height="14" style="display:inline-block;">
              <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
            </svg>
            <?php endfor; ?>
          </div>
          <p class="testi-text">"<?= $t['text'] ?>"</p>
          <div style="display:flex;align-items:center;gap:0.8rem;">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($t['name']) ?>&background=EDE6DC&color=3B2F2F&size=64" style="width:36px;height:36px;border-radius:50%;border:1px solid var(--gray-light);" alt="<?= $t['name'] ?>"/>
            <div>
              <div class="testi-author-name"><?= $t['name'] ?></div>
              <div class="testi-author-sub"><?= $t['sub'] ?></div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── CTA ── -->
<section class="cta-banner">
  <div class="container">
    <div class="row justify-content-center text-center">
      <div class="col-lg-6">
        <p class="section-eyebrow">Get Started</p>
        <h2 class="section-title mb-3">Ready to Book Your Pet's Next Session?</h2>
        <div class="divider-line" style="margin:1rem auto 1.8rem;"></div>
        <p class="section-sub mb-4">Create a free account and schedule an appointment in under two minutes.</p>
        <div class="d-flex gap-3 justify-content-center">
          <a href="register.php" class="btn-primary-site">Create Free Account</a>
          <a href="login.php" class="btn-outline-site">Sign In</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CONTACT ── -->
<section class="contact-section" id="contact">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-4">
        <p class="section-eyebrow">Get In Touch</p>
        <h2 class="section-title" style="margin-bottom:1rem;">Visit Us</h2>
        <div class="divider-line"></div>
        <p class="section-sub" style="margin:1.2rem 0 2rem;">Have questions? We'd love to hear from you.</p>
        <div class="contact-item">
          <div class="contact-icon">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
            </svg>
          </div>
          <div>
            <p class="contact-label">Address</p>
            <p class="contact-value">123 Grooming Street, Quezon City</p>
          </div>
        </div>
        <div class="contact-item">
          <div class="contact-icon">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
            </svg>
          </div>
          <div>
            <p class="contact-label">Phone</p>
            <p class="contact-value">0917-123-4567</p>
          </div>
        </div>
        <div class="contact-item">
          <div class="contact-icon">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
            </svg>
          </div>
          <div>
            <p class="contact-label">Email</p>
            <p class="contact-value">hello@petmalu.ph</p>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <p class="section-eyebrow">Schedule</p>
        <h2 class="section-title" style="font-size:1.4rem;margin-bottom:1rem;">Business Hours</h2>
        <div class="divider-line"></div>
        <div class="hours-grid" style="margin-top:1.5rem;">
          <span class="hours-day">Monday – Friday</span><span class="hours-time">9:00 AM – 5:00 PM</span>
          <span class="hours-day">Saturday</span><span class="hours-time">9:00 AM – 3:00 PM</span>
          <span class="hours-day">Sunday</span><span class="hours-time" style="color:var(--danger);">Closed</span>
        </div>
      </div>

      <div class="col-lg-4">
        <p class="section-eyebrow">Reminders</p>
        <h2 class="section-title" style="font-size:1.4rem;margin-bottom:1rem;">Before You Visit</h2>
        <div class="divider-line"></div>
        <ul style="list-style:none;padding:0;margin-top:1.5rem;">
          <?php foreach ([
            'Please arrive 10 minutes before your scheduled time.',
            'Bring your pet\'s vaccination records on first visit.',
            'Cancellations must be made 24 hours in advance.',
            'Deposit is required to confirm your booking.',
          ] as $note): ?>
          <li style="display:flex;align-items:flex-start;gap:0.6rem;margin-bottom:0.8rem;font-size:0.84rem;color:var(--text-muted);">
            <svg fill="none" stroke="var(--tan)" stroke-width="2" viewBox="0 0 24 24" width="14" height="14" style="flex-shrink:0;margin-top:3px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
            <?= $note ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ── FOOTER ── -->
<footer class="site-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand">PetMalu</div>
        <p class="footer-desc mt-2">Premium pet grooming studio. Because every pet deserves to look and feel their absolute best.</p>
      </div>
      <div class="col-lg-2 offset-lg-2">
        <div class="footer-heading">Navigate</div>
        <a href="#services" class="footer-link">Services</a>
        <a href="#how" class="footer-link">How It Works</a>
        <a href="#gallery" class="footer-link">Gallery</a>
        <a href="#about" class="footer-link">About</a>
        <a href="#contact" class="footer-link">Contact</a>
      </div>
      <div class="col-lg-2">
        <div class="footer-heading">Account</div>
        <a href="login.php" class="footer-link">Sign In</a>
        <a href="register.php" class="footer-link">Book Now</a>
      </div>
      <div class="col-lg-2">
        <div class="footer-heading">Contact</div>
        <span class="footer-link">123 Grooming Street</span>
        <span class="footer-link">Quezon City, Metro Manila</span>
        <span class="footer-link">0917-123-4567</span>
        <span class="footer-link">hello@petmalu.ph</span>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> PetMalu Grooming Studio. All rights reserved.</span>
      <span>Made with care in the Philippines</span>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
  });
});

// Navbar shadow on scroll
window.addEventListener('scroll', () => {
  const nav = document.querySelector('.site-nav');
  nav.style.boxShadow = window.scrollY > 50
    ? '0 2px 20px rgba(44,36,32,0.1)'
    : 'none';
});
</script>
</body>
</html>