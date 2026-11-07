(() => {
  const toc = document.querySelector('[data-legal-toc]');
  const links = [...document.querySelectorAll('[data-legal-toc-link]')];
  const sections = links
    .map((link) => {
      const id = (link.getAttribute('href') || '').replace(/^#/, '');
      const el = id ? document.getElementById(id) : null;
      return el ? { link, el } : null;
    })
    .filter(Boolean);

  const toggle = document.querySelector('[data-legal-toc-toggle]');
  if (toggle && toc) {
    toggle.addEventListener('click', () => {
      const open = toc.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    links.forEach((link) => {
      link.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 980px)').matches) {
          toc.classList.remove('is-open');
          toggle.setAttribute('aria-expanded', 'false');
        }
      });
    });
  }

  const setActive = (activeLink) => {
    links.forEach((link) => {
      link.classList.toggle('is-active', link === activeLink);
    });
  };

  if (sections.length > 0 && 'IntersectionObserver' in window) {
    const ratios = new Map();
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          ratios.set(entry.target.id, entry.intersectionRatio);
        });
        let bestId = null;
        let bestRatio = 0;
        ratios.forEach((ratio, id) => {
          if (ratio > bestRatio) {
            bestRatio = ratio;
            bestId = id;
          }
        });
        if (!bestId) return;
        const match = sections.find((item) => item.el.id === bestId);
        if (match) setActive(match.link);
      },
      {
        rootMargin: '-20% 0px -55% 0px',
        threshold: [0, 0.15, 0.35, 0.55, 0.75, 1],
      }
    );
    sections.forEach(({ el }) => observer.observe(el));
    setActive(sections[0].link);
  } else if (links[0]) {
    setActive(links[0]);
  }

  const revealNodes = document.querySelectorAll('[data-legal-reveal]');
  if (revealNodes.length === 0) return;

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    revealNodes.forEach((node) => node.classList.add('is-in'));
    return;
  }

  if (!('IntersectionObserver' in window)) {
    revealNodes.forEach((node) => node.classList.add('is-in'));
    return;
  }

  const revealObserver = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-in');
        obs.unobserve(entry.target);
      });
    },
    { threshold: 0.05, rootMargin: '0px 0px -4% 0px' }
  );

  revealNodes.forEach((node) => {
    const rect = node.getBoundingClientRect();
    if (rect.top < window.innerHeight && rect.bottom > 0) {
      node.classList.add('is-in');
      return;
    }
    revealObserver.observe(node);
  });
})();
