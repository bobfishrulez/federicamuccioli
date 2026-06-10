/* =============================================
   FEDERICA MUCCIOLI — JavaScript principale

   CONTENUTO:
   1. Navbar — ombra allo scroll
   2. Navbar — menu hamburger mobile
   3. Animazioni fade-in allo scroll
   ============================================= */


/* =============================================
   1. NAVBAR — aggiunge ombra dopo 40px di scroll
   ============================================= */
const navbar = document.getElementById('navbar');

window.addEventListener('scroll', () => {
  // Aggiunge la classe 'scrolled' che nel CSS
  // attiva box-shadow sulla navbar
  navbar.classList.toggle('scrolled', window.scrollY > 40);
});


/* =============================================
   2. NAVBAR — menu hamburger per mobile
   Apre/chiude il menu mobile espanso
   ============================================= */
const hamburger = document.getElementById('hamburger');
const mobileMenu = document.getElementById('nav-mobile-menu');

hamburger.addEventListener('click', () => {
  mobileMenu.classList.toggle('aperto');
});

// Chiude il menu mobile cliccando un link
document.querySelectorAll('.nav-mobile-link').forEach(link => {
  link.addEventListener('click', () => {
    mobileMenu.classList.remove('aperto');
  });
});


/* =============================================
   SWITCHER AREE — sincronizza toggle desktop,
   toggle mobile, link navbar e aree contenuto
   ============================================= */
function cambiaArea(area) {
  const areaChiSono  = document.getElementById('area-chi-sono');
  const areaCosaFare = document.getElementById('area-cosa-posso-fare');
  const navToggle    = document.getElementById('nav-toggle');
  const navLinks     = document.getElementById('nav-links');

  // Mostra toggle e link navbar dopo la prima selezione
  navToggle.style.display = 'flex';
  // Link solo su desktop
  if (window.innerWidth > 768) {
    navLinks.style.display = 'flex';
  }

  // Toggle desktop
  document.querySelectorAll('.nav-toggle-btn').forEach(btn => {
    btn.classList.toggle('attivo', btn.dataset.area === area);
  });

  // Tasti macchina — desktop e mobile
  document.querySelectorAll('.tasto-macchina').forEach(t => {
    t.classList.toggle('attivo', t.dataset.area === area);
  });

  if (area === 'chi-sono') {
    areaChiSono.style.display  = 'block';
    areaCosaFare.style.display = 'none';
    document.querySelectorAll('.nav-item-chi-sono').forEach(el => el.style.display = '');
    document.querySelectorAll('.nav-item-cosa-posso-fare').forEach(el => el.style.display = 'none');
  } else {
    areaChiSono.style.display  = 'none';
    areaCosaFare.style.display = 'block';
    document.querySelectorAll('.nav-item-chi-sono').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.nav-item-cosa-posso-fare').forEach(el => el.style.display = '');
  }

  // Chiudi menu mobile se aperto
  mobileMenu.classList.remove('aperto');

  // Scrolla alla prima sezione dell'area selezionata
  const primaSezione = area === 'chi-sono'
    ? document.getElementById('chi-sono')
    : document.getElementById('servizi');
  if (primaSezione) {
    setTimeout(() => {
      primaSezione.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 50);
  }

  // Riattiva animazioni fade-in nella nuova area
  setTimeout(() => {
    document.querySelectorAll('.area-contenuto:not([style*="display: none"]) .fade-in').forEach(el => {
      const rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight) el.classList.add('visibile');
      osservatore.observe(el);
    });
  }, 100);
}

// Tasti macchina da scrivere — desktop e mobile
document.querySelectorAll('.tasto-macchina').forEach(t => {
  t.addEventListener('click', () => cambiaArea(t.dataset.area));
});

// Toggle desktop
document.querySelectorAll('.nav-toggle-btn').forEach(btn => {
  btn.addEventListener('click', () => cambiaArea(btn.dataset.area));
});

// Link del menu mobile — se clicco link di area non attiva, fa switch
document.querySelectorAll('.nav-mobile-link[data-area-link]').forEach(link => {
  link.addEventListener('click', () => {
    const areaLink = link.dataset.areaLink;
    const areaChiSono = document.getElementById('area-chi-sono');
    const areaAttiva = areaChiSono.style.display !== 'none' ? 'chi-sono' : 'cosa-posso-fare';
    if (areaLink !== areaAttiva) {
      // Fa lo switch prima di scrollare alla sezione
      cambiaArea(areaLink);
      // Aspetta la transizione poi scrolla alla sezione giusta
      const href = link.getAttribute('href');
      setTimeout(() => {
        const target = document.querySelector(href);
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }, 350);
    }
  });
});


/* =============================================
   3. ANIMAZIONI FADE-IN ALLO SCROLL
   Usa IntersectionObserver per rilevare
   quando gli elementi entrano nel viewport
   e aggiunge la classe 'visibile'
   ============================================= */
const osservatore = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      // L'elemento è entrato nel viewport: mostralo
      entry.target.classList.add('visibile');
      // Smette di osservarlo per non riattivarsi
      osservatore.unobserve(entry.target);
    }
  });
}, {
  // Scatta quando almeno il 12% dell'elemento è visibile
  threshold: 0.12
});

// Osserva tutti gli elementi con classe fade-in
document.querySelectorAll('.fade-in').forEach(el => {
  osservatore.observe(el);
});

// Attiva subito gli elementi già visibili al caricamento
// (es. il contenuto hero che è già nella viewport)
document.querySelectorAll('.fade-in').forEach(el => {
  const rect = el.getBoundingClientRect();
  if (rect.top < window.innerHeight) {
    el.classList.add('visibile');
  }
});


/* =============================================
   POPUP — apertura e chiusura
   ============================================= */

// Apre un popup per id
function apriPopup(id) {
  document.getElementById(id).classList.add('aperto');
  document.body.style.overflow = 'hidden'; // blocca scroll pagina
}

// Chiude un popup per id
function chiudiPopup(id) {
  document.getElementById(id).classList.remove('aperto');
  document.body.style.overflow = ''; // riabilita scroll
}

// Chiude il popup cliccando sull'overlay (fuori dal riquadro)
document.querySelectorAll('.popup-overlay').forEach(overlay => {
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) {
      overlay.classList.remove('aperto');
      document.body.style.overflow = '';
    }
  });
});

// Chiude il popup con tasto Escape
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.popup-overlay.aperto').forEach(p => {
      p.classList.remove('aperto');
    });
    document.body.style.overflow = '';
  }
});


/* =============================================
   FORM POPUP — gestione invio (senza PHP per ora)
   Mostra il messaggio di successo dopo il submit
   Quando si aggiungerà il PHP, modificare questa
   funzione per fare la chiamata al server
   ============================================= */
function inviaForm(event, tipo) {
  event.preventDefault(); // blocca il submit normale

  const form = event.target;
  const successoId = 'successo-' + tipo;

  // Nasconde il form e mostra il messaggio di successo
  form.style.display = 'none';
  document.getElementById(successoId).style.display = 'block';

  // Dopo 4 secondi chiude il popup e resetta
  setTimeout(() => {
    const popupId = tipo === 'misura' ? 'popup-misura' : 'popup-sorpresa';
    chiudiPopup(popupId);
    // Resetta il form per la prossima apertura
    setTimeout(() => {
      form.reset();
      form.style.display = 'flex';
      document.getElementById(successoId).style.display = 'none';
    }, 500);
  }, 4000);
}


