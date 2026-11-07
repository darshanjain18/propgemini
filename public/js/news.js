(() => {
  const revealNodes = document.querySelectorAll('[data-news-reveal]');
  if (revealNodes.length && 'IntersectionObserver' in window) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      revealNodes.forEach((n) => n.classList.add('is-in'));
    } else {
      const io = new IntersectionObserver(
        (entries, obs) => {
          entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-in');
            obs.unobserve(entry.target);
          });
        },
        { threshold: 0.08, rootMargin: '0px 0px -6% 0px' }
      );
      revealNodes.forEach((node) => {
        const rect = node.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
          node.classList.add('is-in');
        } else {
          io.observe(node);
        }
      });
    }
  } else {
    revealNodes.forEach((n) => n.classList.add('is-in'));
  }

  const filterBar = document.querySelector('[aria-label="News categories"]');
  const filters = filterBar
    ? filterBar.querySelectorAll('[data-news-filter]')
    : document.querySelectorAll('[data-news-filter]');
  const cards = document.querySelectorAll('[data-news-card]');
  const empty = document.querySelector('[data-news-empty-filter]');
  const grid = document.getElementById('pg-news-grid');

  const applyFilter = (slug) => {
    const activeSlug = slug || 'all-updates';
    let visible = 0;
    cards.forEach((card) => {
      const cat = (card.getAttribute('data-category') || '').trim();
      const show = activeSlug === 'all-updates' || cat === activeSlug;
      card.hidden = !show;
      card.setAttribute('aria-hidden', show ? 'false' : 'true');
      if (show) visible += 1;
    });
    if (empty) {
      empty.hidden = visible > 0 || cards.length === 0;
      empty.textContent = visible > 0
        ? 'No stories in this category yet.'
        : 'No stories in this category yet.';
    }
    filters.forEach((btn) => {
      const on = (btn.getAttribute('data-news-filter') || '') === activeSlug;
      btn.classList.toggle('is-active', on);
      btn.setAttribute('aria-selected', on ? 'true' : 'false');
    });
  };

  filters.forEach((btn) => {
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      applyFilter(btn.getAttribute('data-news-filter') || 'all-updates');
      if (grid && typeof grid.scrollIntoView === 'function') {
        const top = grid.getBoundingClientRect().top + window.scrollY - 90;
        window.scrollTo({ top, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
      }
    });
  });


  const form = document.querySelector('[data-news-newsletter]');
  if (form) {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const wrap = form.parentElement;
      const msg = (wrap && wrap.querySelector('[data-news-newsletter-msg]')) || document.querySelector('[data-news-newsletter-msg]');
      const data = new FormData(form);
      try {
        const res = await fetch(form.action, {
          method: 'POST',
          body: data,
          headers: { Accept: 'application/json' },
        });
        const json = await res.json();
        if (msg) {
          msg.textContent = json.message || (json.ok ? 'Subscribed.' : 'Could not subscribe.');
          msg.className = 'pg-news-newsletter-msg ' + (json.ok ? 'is-ok' : 'is-err');
        }
        if (json.ok) form.reset();
      } catch (err) {
        if (msg) {
          msg.textContent = 'Network error. Please try again.';
          msg.className = 'pg-news-newsletter-msg is-err';
        }
      }
    });
  }
})();
