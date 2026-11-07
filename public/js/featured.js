(() => {
  if (!document.body.classList.contains('properties-page')) return;

  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const nodes = document.querySelectorAll('[data-featured-reveal]');

  if (!nodes.length) return;

  if (reduce || !('IntersectionObserver' in window)) {
    nodes.forEach((el) => el.classList.add('is-in'));
    return;
  }

  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-in');
        io.unobserve(entry.target);
      });
    },
    { threshold: 0.16, rootMargin: '0px 0px -6% 0px' }
  );

  nodes.forEach((el) => io.observe(el));
})();
