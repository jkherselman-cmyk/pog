// POG African Safaris — shared JS

// Inject header/footer into pages that include <div id="site-header"></div> etc.
const NAV = [
  ['index.html','Home'],['about.html','About'],['packages.html','Packages'],
  ['accommodation.html','Accommodation'],['eastern-cape.html','Eastern Cape'],
  ['gallery.html','Gallery'],['faq.html','FAQ'],['contact.html','Contact']
];

function currentPage(){
  const p = location.pathname.split('/').pop() || 'index.html';
  return p;
}

function renderHeader(){
  const el = document.getElementById('site-header');
  if(!el) return;
  const cur = currentPage();
  const links = NAV.map(([h,l])=>`<a href="${h}" class="${h===cur?'active':''}">${l}</a>`).join('');
  const mlinks = NAV.map(([h,l])=>`<a href="${h}">${l}</a>`).join('');
  el.innerHTML = `
    <header class="site-header">
      <div class="container">
        <a href="index.html" class="brand">POG African Safaris</a>
        <nav class="nav-desktop">${links}</nav>
        <div class="hide-mobile"><a class="btn btn-gold" href="contact.html">Book Now</a></div>
        <button class="menu-btn hide-desktop" aria-label="Open menu" onclick="document.getElementById('mobile-menu').classList.add('open')">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
      </div>
    </header>
    <div id="mobile-menu" class="mobile-menu">
      <div class="mobile-menu-head">
        <span class="brand">POG African Safaris</span>
        <button class="menu-btn" aria-label="Close menu" onclick="document.getElementById('mobile-menu').classList.remove('open')" style="color:var(--cream)">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <nav>${mlinks}</nav>
      <div class="foot"><a class="btn btn-gold" href="contact.html">Book Now</a></div>
    </div>`;
}

function renderFooter(){
  const el = document.getElementById('site-footer');
  if(!el) return;
  const quick = NAV.map(([h,l])=>`<li><a href="${h}">${l}</a></li>`).join('');
  const legal = [
    ['legal/terms.html','Terms & Conditions'],
    ['legal/booking.html','Booking Agreement'],
    ['legal/firearms.html','Firearms Import Guide'],
    ['legal/packing-list.html','Packing List']
  ];
  const prefix = location.pathname.includes('/legal/') ? '../' : '';
  const legalLinks = legal.map(([h,l])=>`<a href="${prefix}${h}">${l}</a>`).join('');
  const quickPrefixed = NAV.map(([h,l])=>`<li><a href="${prefix}${h}">${l}</a></li>`).join('');

  el.innerHTML = `
    <footer class="site-footer">
      <div class="container">
        <div class="footer-grid">
          <div>
            <h3>POG African Safaris</h3>
            <p class="tagline">"Arrive as a guest. Hunt among friends. Leave as family."</p>
            <p style="font-size:.9rem;color:rgba(245,239,224,.7)">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
          </div>
          <div>
            <h4>Quick Links</h4>
            <ul>${quickPrefixed}</ul>
          </div>
          <div>
            <h4>Contact Us</h4>
            <ul class="contact-list">
              <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><span>info@poghunting.co.za</span></li>
              <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.72 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.35 1.85.59 2.81.72A2 2 0 0 1 22 16.92z"/></svg><span>+27 12 345 6789</span></li>
              <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg><span>Eastern Cape, South Africa</span></li>
            </ul>
          </div>
          <div>
            <h4>Stay Connected</h4>
            <p style="font-size:.9rem;color:rgba(245,239,224,.7)">Subscribe for safari news and exclusive offers.</p>
            <form class="newsletter-form" onsubmit="event.preventDefault();this.reset();alert('Thanks for subscribing!')">
              <input type="email" placeholder="Your email" required>
              <button class="btn btn-gold" type="submit">Join</button>
            </form>
            <div class="socials">
              <a href="#" aria-label="Facebook"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 10-11.6 9.9v-7h-2v-3h2V9.5C10.4 7.5 11.6 6.4 13.4 6.4c.9 0 1.8.2 1.8.2V8.5h-1c-1 0-1.3.6-1.3 1.3V12h2.2l-.4 3h-1.9v7A10 10 0 0022 12z"/></svg></a>
              <a href="#" aria-label="Instagram"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.4A4 4 0 1112.6 8 4 4 0 0116 11.4z"/><line x1="17.5" y1="6.5" x2="17.5" y2="6.5"/></svg></a>
              <a href="#" aria-label="Twitter"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/></svg></a>
            </div>
          </div>
        </div>
        <div class="footer-bottom">
          <p>© ${new Date().getFullYear()} POG African Safaris. All rights reserved.</p>
          <div class="legal">${legalLinks}</div>
        </div>
      </div>
    </footer>`;
}

// Reveal on scroll
function initReveal(){
  const els = document.querySelectorAll('.reveal');
  if(!('IntersectionObserver' in window)){els.forEach(e=>e.classList.add('visible'));return;}
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');io.unobserve(e.target);}});
  },{threshold:.15});
  els.forEach(e=>io.observe(e));
}

// FAQ
function initFAQ(){
  document.querySelectorAll('.faq-item').forEach(item=>{
    const q = item.querySelector('.faq-q');
    if(q) q.addEventListener('click',()=>item.classList.toggle('open'));
  });
}

// Testimonials — expand full testimonial on click
function initTestimonials(){
  document.querySelectorAll('.quote').forEach(q=>{
    q.addEventListener('click',()=>{
      const isExpanded = q.getAttribute('data-expanded') === 'true';
      q.setAttribute('data-expanded', isExpanded ? 'false' : 'true');
    });
  });
}

// Gallery
function initGallery(){
  const grid = document.getElementById('gallery-grid');
  if(!grid) return;
  const imgs = grid.querySelectorAll('img');
  const lb = document.getElementById('lightbox');
  const lbImg = document.getElementById('lightbox-img');
  imgs.forEach(img=>img.addEventListener('click',()=>{lbImg.src=img.src;lb.classList.add('open');}));
  document.querySelectorAll('.filter-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
      document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      const cat = btn.dataset.cat;
      imgs.forEach(img=>img.style.display = (cat==='all'||img.dataset.cat===cat)?'block':'none');
    });
  });
}
function closeLightbox(){document.getElementById('lightbox').classList.remove('open');}

document.addEventListener('DOMContentLoaded',()=>{
  renderHeader();
  renderFooter();
  initReveal();
  initFAQ();
  initGallery();
  initTestimonials();
});
