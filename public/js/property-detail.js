(() => {
  const root = document.querySelector('.pg-pd');
  if (!root) return;

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Hero carousel
  const slides = [...root.querySelectorAll('[data-pd-slide]')];
  const countEl = root.querySelector('[data-pd-count]');
  let index = 0;

  const paintHero = () => {
    slides.forEach((slide, i) => slide.classList.toggle('is-active', i === index));
    if (countEl) {
      const total = String(slides.length).padStart(2, '0');
      const current = String(index + 1).padStart(2, '0');
      countEl.textContent = `${current} / ${total}`;
    }
  };

  root.querySelector('[data-pd-prev]')?.addEventListener('click', () => {
    if (!slides.length) return;
    index = (index - 1 + slides.length) % slides.length;
    paintHero();
  });
  root.querySelector('[data-pd-next]')?.addEventListener('click', () => {
    if (!slides.length) return;
    index = (index + 1) % slides.length;
    paintHero();
  });
  paintHero();

  // Video modal
  const videoModal = root.querySelector('[data-pd-video-modal]');
  const videoEl = root.querySelector('[data-pd-video-el]');
  const closeVideo = () => {
    if (!videoModal || !videoEl) return;
    videoEl.pause();
    videoEl.removeAttribute('src');
    videoEl.load();
    videoModal.hidden = true;
    document.body.classList.remove('modal-open');
  };
  root.querySelector('[data-pd-play-video]')?.addEventListener('click', (event) => {
    const btn = event.currentTarget;
    const src = btn.getAttribute('data-video') || '';
    if (!src || !videoModal || !videoEl) return;
    videoEl.src = src;
    videoModal.hidden = false;
    document.body.classList.add('modal-open');
    videoEl.play?.().catch(() => {});
  });
  root.querySelector('[data-pd-video-close]')?.addEventListener('click', closeVideo);
  videoModal?.addEventListener('click', (event) => {
    if (event.target === videoModal) closeVideo();
  });

  // Sticky tabs + scroll spy
  const tabsBar = root.querySelector('[data-pd-tabs]');
  const tabs = [...root.querySelectorAll('[data-pd-tab]')];
  const sections = tabs
    .map((tab) => document.getElementById(`section-${tab.dataset.pdTab}`))
    .filter(Boolean);

  const setActiveTab = (key) => {
    tabs.forEach((tab) => tab.classList.toggle('is-on', tab.dataset.pdTab === key));
  };

  tabs.forEach((tab) => {
    tab.addEventListener('click', (event) => {
      const target = document.getElementById(`section-${tab.dataset.pdTab}`);
      if (!target) return;
      event.preventDefault();
      const offset = (tabsBar?.offsetHeight || 0) + 12;
      const top = target.getBoundingClientRect().top + window.scrollY - offset;
      window.scrollTo({ top, behavior: reduceMotion ? 'auto' : 'smooth' });
      setActiveTab(tab.dataset.pdTab);
      history.replaceState(null, '', `#section-${tab.dataset.pdTab}`);
    });
  });

  if (sections.length && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        const visible = entries
          .filter((entry) => entry.isIntersecting)
          .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
        if (!visible) return;
        const key = visible.target.getAttribute('data-pd-section');
        if (key) setActiveTab(key);
      },
      {
        rootMargin: '-30% 0px -55% 0px',
        threshold: [0.1, 0.25, 0.5],
      }
    );
    sections.forEach((section) => observer.observe(section));
  }

  // Lightbox
  const lightbox = root.querySelector('[data-pd-lightbox-modal]');
  const lightboxImg = root.querySelector('[data-pd-lightbox-img]');
  const galleryBtns = [...root.querySelectorAll('[data-pd-lightbox]')];
  let lightboxIndex = 0;

  const openLightbox = (i) => {
    if (!lightbox || !lightboxImg || !galleryBtns.length) return;
    lightboxIndex = (i + galleryBtns.length) % galleryBtns.length;
    const src = galleryBtns[lightboxIndex].getAttribute('data-pd-lightbox') || '';
    lightboxImg.src = src;
    lightboxImg.alt = galleryBtns[lightboxIndex].querySelector('img')?.alt || 'Property image';
    lightbox.hidden = false;
    document.body.classList.add('modal-open');
  };
  const closeLightbox = () => {
    if (!lightbox || !lightboxImg) return;
    lightbox.hidden = true;
    lightboxImg.removeAttribute('src');
    document.body.classList.remove('modal-open');
  };

  galleryBtns.forEach((btn, i) => {
    btn.addEventListener('click', () => openLightbox(i));
  });
  root.querySelector('[data-pd-lightbox-close]')?.addEventListener('click', closeLightbox);
  root.querySelector('[data-pd-lightbox-prev]')?.addEventListener('click', () => openLightbox(lightboxIndex - 1));
  root.querySelector('[data-pd-lightbox-next]')?.addEventListener('click', () => openLightbox(lightboxIndex + 1));
  lightbox?.addEventListener('click', (event) => {
    if (event.target === lightbox) closeLightbox();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeLightbox();
      closeVideo();
    }
  });

  // Soft reveal
  if (!reduceMotion && 'IntersectionObserver' in window) {
    const reveal = [...root.querySelectorAll('.pg-pd-section, .pg-pd-spec-card, .pg-pd-enquire')];
    reveal.forEach((el) => el.classList.add('pg-pd-reveal'));
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-in');
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12 }
    );
    reveal.forEach((el) => io.observe(el));
  }
})();
