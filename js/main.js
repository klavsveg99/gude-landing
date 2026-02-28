let lastScroll = 0;
const header = document.querySelector('header');

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 100) {
        if (currentScroll > lastScroll) {
            header.classList.add('hidden');
            header.classList.remove('sticky');
        } else {
            header.classList.remove('hidden');
            header.classList.add('sticky');
        }
    } else {
        header.classList.remove('sticky', 'hidden');
    }
    
    lastScroll = currentScroll;
});

const contactPopup = document.getElementById('contactPopup');
const contactOverlay = document.getElementById('contactOverlay');
const popupClose = document.getElementById('popupClose');
const contactForm = document.getElementById('contactForm');
const formMessage = document.getElementById('formMessage');
const ctaButtons = document.querySelectorAll('.contact-cta-btn');

function openPopup() {
    contactPopup.classList.add('active');
    contactOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closePopup() {
    contactPopup.classList.remove('active');
    contactOverlay.classList.remove('active');
    document.body.style.overflow = '';
}

ctaButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        openPopup();
    });
});

popupClose.addEventListener('click', closePopup);
contactOverlay.addEventListener('click', closePopup);

const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const mainNav = document.getElementById('mainNav');
const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

function openMobileMenu() {
    mainNav.classList.add('active');
    mobileMenuToggle.classList.add('active');
    mobileMenuOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeMobileMenu() {
    mainNav.classList.remove('active');
    mobileMenuToggle.classList.remove('active');
    mobileMenuOverlay.classList.remove('active');
    document.body.style.overflow = '';
}

if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', () => {
        if (mainNav.classList.contains('active')) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    });
}

if (mobileMenuOverlay) {
    mobileMenuOverlay.addEventListener('click', closeMobileMenu);
}

document.querySelectorAll('#mainNav a').forEach(link => {
    link.addEventListener('click', closeMobileMenu);
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (contactPopup && contactPopup.classList.contains('active')) {
            closePopup();
        }
        if (mainNav && mainNav.classList.contains('active')) {
            closeMobileMenu();
        }
    }
});

if (contactForm) {
    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(contactForm);
        
        try {
            const response = await fetch('contact.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            formMessage.textContent = result.message;
            formMessage.className = 'form-message ' + (result.success ? 'success' : 'error');
            
            if (result.success) {
                contactForm.reset();
                setTimeout(closePopup, 2000);
            }
        } catch (error) {
            formMessage.textContent = 'Kļūda nosūtot ziņojumu. Lūdzu, mēģiniet vēlreiz.';
            formMessage.className = 'form-message error';
        }
    });
}

const animatedElements = document.querySelectorAll('.section, footer, .cta-section, .features-grid, .quality-grid, .products-grid, .logistics-grid, .contacts-grid, .stat-item, .feature-card, .quality-item, .product-card, .logistics-card, .contact-card, .hours-card, .tech-specs, .spec-card, .cta-box, .map-placeholder, .map-locations');

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animationPlayState = 'running';
            entry.target.style.opacity = '1';
        }
    });
}, { threshold: 0.1 });

animatedElements.forEach(el => {
    el.style.animationPlayState = 'paused';
    observer.observe(el);
});
