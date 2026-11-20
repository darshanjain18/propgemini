(function initHomePath() {
  const root = document.querySelector('[data-pg-path]');
  if (!root) return;

  const steps = [...root.querySelectorAll('.pg-home-path-step')];
  if (!steps.length) return;

  const triggers = [...root.querySelectorAll('[data-path-step]')];
  const dots = [...root.querySelectorAll('[data-path-dot]')];
  const nextBtn = root.querySelector('[data-path-next]');
  const panelIcon = root.querySelector('[data-path-panel-icon]');
  const panelTitle = root.querySelector('[data-path-panel-title]');
  const panelCopy = root.querySelector('[data-path-panel-copy]');
  const mobileQuery = window.matchMedia('(max-width: 720px)');

  let index = Math.max(
    0,
    steps.findIndex((step) => step.classList.contains('is-active'))
  );

  const setStep = (nextIndex) => {
    const total = steps.length;
    index = ((nextIndex % total) + total) % total;

    steps.forEach((step, i) => {
      const active = i === index;
      step.classList.toggle('is-active', active);
      const trigger = step.querySelector('[data-path-step]');
      if (trigger) trigger.setAttribute('aria-selected', String(active));
    });

    dots.forEach((dot, i) => {
      const active = i === index;
      dot.classList.toggle('is-active', active);
      dot.setAttribute('aria-current', active ? 'true' : 'false');
    });

    const activeStep = steps[index];
    if (!activeStep) return;

    if (panelTitle) panelTitle.textContent = activeStep.dataset.title || '';
    if (panelCopy) panelCopy.textContent = activeStep.dataset.copy || '';

    if (panelIcon) {
      const sourceIcon = activeStep.querySelector('.pg-home-path-point .pg-home-path-icon');
      panelIcon.innerHTML = sourceIcon ? sourceIcon.innerHTML : '';
    }
  };

  triggers.forEach((trigger) => {
    trigger.addEventListener('click', () => {
      if (!mobileQuery.matches) return;
      const stepIndex = Number(trigger.dataset.pathStep);
      if (!Number.isFinite(stepIndex)) return;
      setStep(stepIndex);
    });
  });

  dots.forEach((dot) => {
    dot.addEventListener('click', () => {
      const stepIndex = Number(dot.dataset.pathDot);
      if (!Number.isFinite(stepIndex)) return;
      setStep(stepIndex);
    });
  });

  if (nextBtn) {
    nextBtn.addEventListener('click', () => setStep(index + 1));
  }

  setStep(index);
})();
