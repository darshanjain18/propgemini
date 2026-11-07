(() => {
  if (!document.body.classList.contains('emi-page')) return;

  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const money = (value) => '₹ ' + Math.round(value).toLocaleString('en-IN');
  const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

  /* Tabs */
  const tabs = [...document.querySelectorAll('[data-emi-tab]')];
  const panelCalc = document.getElementById('pgEmiPanelCalc');
  const panelEnquire = document.getElementById('pgEmiPanelEnquire');
  const tabCalc = document.getElementById('pgEmiTabCalc');
  const tabEnquire = document.getElementById('pgEmiTabEnquire');

  function setTab(name) {
    const enquire = name === 'enquire';
    tabCalc?.classList.toggle('is-active', !enquire);
    tabEnquire?.classList.toggle('is-active', enquire);
    tabCalc?.setAttribute('aria-selected', String(!enquire));
    tabEnquire?.setAttribute('aria-selected', String(enquire));
    panelCalc?.classList.toggle('is-active', !enquire);
    panelEnquire?.classList.toggle('is-active', enquire);
    if (panelCalc) panelCalc.hidden = enquire;
    if (panelEnquire) panelEnquire.hidden = !enquire;
    if (enquire) {
      panelEnquire?.querySelector('input,select,textarea')?.focus?.({ preventScroll: true });
    }
  }

  tabs.forEach((el) => {
    el.addEventListener('click', () => setTab(el.getAttribute('data-emi-tab') || 'emi'));
  });

  /* Calculator */
  const form = document.getElementById('emiCalculator');
  if (form) {
    const loanRange = document.getElementById('loanAmount');
    const loanInput = document.getElementById('loanAmountInput');
    const rateRange = document.getElementById('interestRate');
    const rateInput = document.getElementById('interestRateInput');
    const tenureRange = document.getElementById('tenureYears');
    const tenureInput = document.getElementById('tenureYearsInput');
    const emiOut = document.getElementById('monthlyEmi');
    const interestOut = document.getElementById('totalInterest');
    const payableOut = document.getElementById('totalPayable');

    function paintRange(range) {
      const min = Number(range.min);
      const max = Number(range.max);
      const value = Number(range.value);
      const percent = ((value - min) / (max - min)) * 100;
      range.style.background =
        `linear-gradient(90deg, #c6a36a 0%, #c6a36a ${percent}%, #e8dfd0 ${percent}%, #e8dfd0 100%)`;
    }

    function formatLoan(value) {
      return '₹ ' + Math.round(value).toLocaleString('en-IN');
    }

    function parseLoan(raw) {
      const digits = String(raw).replace(/[^\d]/g, '');
      return digits === '' ? NaN : Number(digits);
    }

    function parseRate(raw) {
      const cleaned = String(raw).replace(/[^\d.]/g, '');
      return cleaned === '' ? NaN : Number(cleaned);
    }

    function parseYears(raw) {
      const digits = String(raw).replace(/[^\d]/g, '');
      return digits === '' ? NaN : Number(digits);
    }

    function calculate() {
      const principal = Number(loanRange.value);
      const annualRate = Number(rateRange.value);
      const years = Number(tenureRange.value);
      const months = Math.max(1, years * 12);
      const monthlyRate = annualRate / 12 / 100;

      let emi;
      if (monthlyRate === 0) {
        emi = principal / months;
      } else {
        const growth = Math.pow(1 + monthlyRate, months);
        emi = (principal * monthlyRate * growth) / (growth - 1);
      }

      const payable = emi * months;
      const interest = payable - principal;

      if (!reduce) emiOut.classList.add('is-updating');
      emiOut.textContent = money(emi);
      interestOut.textContent = money(interest);
      payableOut.textContent = money(payable);
      if (!reduce) {
        window.requestAnimationFrame(() => {
          emiOut.classList.remove('is-updating');
        });
      }

      paintRange(loanRange);
      paintRange(rateRange);
      paintRange(tenureRange);
    }

    function syncLoanFromRange() {
      const value = Number(loanRange.value);
      loanInput.value = formatLoan(value);
      loanInput.dataset.raw = String(value);
      calculate();
    }

    function syncRateFromRange() {
      const value = Number(rateRange.value);
      const nice = Number.isInteger(value) ? String(value) : value.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
      rateInput.value = `${nice} %`;
      rateInput.dataset.raw = nice;
      calculate();
    }

    function syncTenureFromRange() {
      const value = Number(tenureRange.value);
      tenureInput.value = `${value} Years`;
      tenureInput.dataset.raw = String(value);
      calculate();
    }

    loanRange.addEventListener('input', syncLoanFromRange);
    rateRange.addEventListener('input', syncRateFromRange);
    tenureRange.addEventListener('input', syncTenureFromRange);

    loanInput.addEventListener('focus', () => {
      loanInput.value = loanInput.dataset.raw || loanRange.value;
    });
    loanInput.addEventListener('input', () => {
      const value = parseLoan(loanInput.value);
      if (!Number.isFinite(value)) return;
      if (value < Number(loanRange.min) || value > Number(loanRange.max)) return;
      loanRange.value = String(value);
      loanInput.dataset.raw = String(value);
      calculate();
    });
    loanInput.addEventListener('change', () => {
      const value = clamp(parseLoan(loanInput.value) || Number(loanRange.min), Number(loanRange.min), Number(loanRange.max));
      loanRange.value = String(value);
      syncLoanFromRange();
    });
    loanInput.addEventListener('blur', syncLoanFromRange);

    rateInput.addEventListener('focus', () => {
      rateInput.value = rateInput.dataset.raw || rateRange.value;
    });
    rateInput.addEventListener('input', () => {
      const value = parseRate(rateInput.value);
      if (!Number.isFinite(value)) return;
      if (value < Number(rateRange.min) || value > Number(rateRange.max)) return;
      rateRange.value = String(value);
      rateInput.dataset.raw = String(value);
      calculate();
    });
    rateInput.addEventListener('change', () => {
      const value = clamp(parseRate(rateInput.value) || Number(rateRange.min), Number(rateRange.min), Number(rateRange.max));
      rateRange.value = String(value);
      syncRateFromRange();
    });
    rateInput.addEventListener('blur', syncRateFromRange);

    tenureInput.addEventListener('focus', () => {
      tenureInput.value = tenureInput.dataset.raw || tenureRange.value;
    });
    tenureInput.addEventListener('input', () => {
      const value = parseYears(tenureInput.value);
      if (!Number.isFinite(value)) return;
      if (value < Number(tenureRange.min) || value > Number(tenureRange.max)) return;
      tenureRange.value = String(value);
      tenureInput.dataset.raw = String(value);
      calculate();
    });
    tenureInput.addEventListener('change', () => {
      const value = clamp(parseYears(tenureInput.value) || Number(tenureRange.min), Number(tenureRange.min), Number(tenureRange.max));
      tenureRange.value = String(value);
      syncTenureFromRange();
    });
    tenureInput.addEventListener('blur', syncTenureFromRange);

    const params = new URLSearchParams(window.location.search);
    if (params.has('loan')) {
      loanRange.value = String(clamp(Number(params.get('loan')), Number(loanRange.min), Number(loanRange.max)));
    }
    if (params.has('rate')) {
      rateRange.value = String(clamp(Number(params.get('rate')), Number(rateRange.min), Number(rateRange.max)));
    }
    if (params.has('years')) {
      tenureRange.value = String(clamp(Number(params.get('years')), Number(tenureRange.min), Number(tenureRange.max)));
    }

    form.addEventListener('submit', (event) => event.preventDefault());
    syncLoanFromRange();
    syncRateFromRange();
    syncTenureFromRange();
  }

  /* Reveal */
  const nodes = document.querySelectorAll('[data-emi-reveal]');
  if (reduce || !('IntersectionObserver' in window)) {
    nodes.forEach((el) => el.classList.add('is-in'));
  } else {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('is-in');
          io.unobserve(entry.target);
        });
      },
      { threshold: 0.15, rootMargin: '0px 0px -6% 0px' }
    );
    nodes.forEach((el) => io.observe(el));
  }
})();
