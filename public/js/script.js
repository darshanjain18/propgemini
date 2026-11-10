const mobileToggle=document.getElementById('mobileToggle'),mobileNav=document.getElementById('mobileNav');
if(mobileToggle&&mobileNav){
mobileToggle.addEventListener('click',()=>{const open=mobileNav.classList.toggle('open');mobileToggle.setAttribute('aria-expanded',String(open));mobileToggle.textContent=open?'×':'☰'});
mobileNav.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{mobileNav.classList.remove('open');mobileToggle.setAttribute('aria-expanded','false');mobileToggle.textContent='☰'}));
}
document.documentElement.classList.add('js-ready');
document.documentElement.classList.remove('no-js');

const propertyRail=document.getElementById('propertyGrid');
if(propertyRail){
const previous=document.getElementById('propertyPrev');
const next=document.getElementById('propertyNext');
const pageBy = () => {
  const card = propertyRail.querySelector('.property');
  if (!card) return propertyRail.clientWidth;
  const styles = getComputedStyle(propertyRail);
  const gap = parseFloat(styles.columnGap || styles.gap) || 0;
  /* Scroll by one full row of visible cards (3 on desktop). */
  const cardWidth = card.getBoundingClientRect().width;
  const visible = Math.max(1, Math.round((propertyRail.clientWidth + gap) / (cardWidth + gap)));
  return visible * (cardWidth + gap);
};
if(previous) previous.addEventListener('click',()=>propertyRail.scrollBy({left:-pageBy(),behavior:'smooth'}));
if(next) next.addEventListener('click',()=>propertyRail.scrollBy({left:pageBy(),behavior:'smooth'}));
propertyRail.addEventListener('wheel', e => {
  if (Math.abs(e.deltaY) > Math.abs(e.deltaX) && propertyRail.scrollWidth > propertyRail.clientWidth) {
    e.preventDefault();
    propertyRail.scrollBy({left: e.deltaY, behavior: 'auto'});
  }
}, {passive:false});
}
document.querySelectorAll('.heart').forEach((b)=>b.addEventListener('click',(event)=>{
  event.preventDefault();
  event.stopPropagation();
  const active=b.classList.toggle('active');
  b.textContent=active?'♥':'♡';
  b.setAttribute('aria-pressed',String(active));
  showToast(active?'Saved to your shortlist':'Removed from your shortlist');
}));
function showToast(message){const t=document.getElementById('toast');if(!t)return;t.textContent=message;t.style.display='block';clearTimeout(window.demoToastTimer);window.demoToastTimer=setTimeout(()=>t.style.display='none',2200)}

(function initPremiumReveals(){
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const nodes = document.querySelectorAll('.reveal-item, .reveal-block, .reveal-media');
  if (!nodes.length) return;
  nodes.forEach((node) => {
    const delay = Number(node.getAttribute('data-reveal-delay') || 0);
    if (Number.isFinite(delay) && delay > 0) {
      node.style.setProperty('--reveal-delay', String(delay));
    }
  });
  if (reduce || !('IntersectionObserver' in window)) {
    nodes.forEach((node) => node.classList.add('is-visible'));
    return;
  }
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-visible');
      observer.unobserve(entry.target);
    });
  }, { threshold: 0.16, rootMargin: '0px 0px -8% 0px' });
  nodes.forEach((node) => {
    if (node.closest('.hero')) {
      requestAnimationFrame(() => node.classList.add('is-visible'));
      return;
    }
    observer.observe(node);
  });

  const markRevealed = (el) => {
    if (!el) return;
    const sectionObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        el.classList.add('is-revealed');
        sectionObserver.unobserve(el);
      });
    }, { threshold: 0.16 });
    sectionObserver.observe(el);
  };
  markRevealed(document.getElementById('properties'));
})();

const emiForm = document.getElementById('emiCalculator');
if (emiForm && !document.body.classList.contains('emi-page')) {
    const pairs = [
        [document.getElementById('loanAmount'), document.getElementById('loanAmountInput')],
        [document.getElementById('interestRate'), document.getElementById('interestRateInput')],
        [document.getElementById('tenureYears'), document.getElementById('tenureYearsInput')],
    ];
    const money = (value) => '₹' + Math.round(value).toLocaleString('en-IN');
    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

    function render() {
        const principal = Number(pairs[0][0].value);
        const annualRate = Number(pairs[1][0].value);
        const tenureYears = Number(pairs[2][0].value);
        const months = tenureYears * 12;
        const monthlyRate = annualRate / 12 / 100;
        const growth = (1 + monthlyRate) ** months;
        const emi = monthlyRate === 0
            ? principal / months
            : principal * monthlyRate * growth / (growth - 1);
        const payable = emi * months;

        document.getElementById('monthlyEmi').textContent = money(emi);
        document.getElementById('totalInterest').textContent = money(payable - principal);
        document.getElementById('totalPayable').textContent = money(payable);
    }

    function setPair(range, input, value) {
        const next = clamp(value, Number(range.min), Number(range.max));
        range.value = String(next);
        input.value = String(next);
    }

    pairs.forEach(([range, input]) => {
        range.addEventListener('input', () => {
            input.value = range.value;
            render();
        });
        input.addEventListener('input', () => {
            const value = Number(input.value);
            if (!Number.isFinite(value)) return;
            if (value < Number(range.min) || value > Number(range.max)) return;
            range.value = String(value);
            render();
        });
        input.addEventListener('change', () => {
            setPair(range, input, Number(input.value));
            render();
        });
    });

    const params = new URLSearchParams(window.location.search);
    if (params.has('loan')) setPair(pairs[0][0], pairs[0][1], Number(params.get('loan')));
    if (params.has('rate')) setPair(pairs[1][0], pairs[1][1], Number(params.get('rate')));
    if (params.has('years')) setPair(pairs[2][0], pairs[2][1], Number(params.get('years')));

    emiForm.addEventListener('submit', (event) => event.preventDefault());
    render();
}

const previewCards = document.querySelectorAll('.property');
if (previewCards.length) {
    const hoverNone = () => matchMedia('(hover: none)').matches;
    let activePreview = null;
    function startPreview(card) {
        if (hoverNone() || activePreview === card) return;
        if (activePreview) stopPreview(activePreview);

        card.classList.add('is-preview');
        const video = card.querySelector('video');
        if (video) {
            video.currentTime = 0;
            video.play().catch(() => {});
        }
        activePreview = card;
        clearTimeout(card._previewTimer);
        card._previewTimer = setTimeout(() => stopPreview(card), 120000);
    }
    function stopPreview(card) {
        card.classList.remove('is-preview');
        const video = card.querySelector('video');
        if (video) {
            video.pause();
            video.currentTime = 0;
        }
        if (activePreview === card) activePreview = null;
        clearTimeout(card._previewTimer);
    }
    document.addEventListener('pointerover', (event) => {
        const card = event.target.closest && event.target.closest('.property');
        if (card && !card.contains(event.relatedTarget) && !hoverNone()) startPreview(card);
    });
    document.addEventListener('pointerout', (event) => {
        const card = event.target.closest && event.target.closest('.property');
        if (card && !card.contains(event.relatedTarget)) stopPreview(card);
    });
}

const areaMapNode = document.getElementById('areaMap');
const filterCity = document.getElementById('filterCity');
const areaSearch = document.getElementById('areaSearch');
const filterArea = document.getElementById('filterArea');
const areaDropdown = document.getElementById('areaDropdown');

if (areaMapNode && filterCity && areaSearch && filterArea && areaDropdown) {
    const areaMap = JSON.parse(areaMapNode.textContent);
    let availableAreas = [];

    function renderAreas(query = '') {
        const filtered = availableAreas.filter(area =>
            area.toLowerCase().includes(query.toLowerCase())
        );

        areaDropdown.innerHTML = '';

        if (!filtered.length) {
            areaDropdown.innerHTML =
                '<div class="area-empty">No areas found</div>';
        } else {
            areaDropdown.innerHTML = `
                <div class="area-grid">
                    ${filtered.map(area => `
                        <button type="button" class="area-option"
                            data-area="${area.replace(/"/g, '&quot;')}">
                            ${area}
                        </button>
                    `).join('')}
                </div>
            `;
        }

        areaDropdown.hidden = false;
    }

    function updateAreas() {
        const city = filterCity.value;

        availableAreas = city ? (areaMap[city] || []) : [];

        areaSearch.disabled = !city;
        areaSearch.value = '';
        filterArea.value = '';
        areaDropdown.hidden = true;
    }

    areaSearch.addEventListener('focus', () => {
        if (!areaSearch.disabled) {
            renderAreas(areaSearch.value);
        }
    });

    areaSearch.addEventListener('input', () => {
        filterArea.value = '';
        renderAreas(areaSearch.value);
    });

    areaDropdown.addEventListener('click', event => {
        const option = event.target.closest('.area-option');
        if (!option) return;

        const area = option.dataset.area;
        areaSearch.value = area;
        filterArea.value = area;
        areaDropdown.hidden = true;
    });

    filterCity.addEventListener('change', updateAreas);

    document.addEventListener('click', event => {
        if (!event.target.closest('.area-picker')) {
            areaDropdown.hidden = true;
        }
    });

    updateAreas();

    // Restore the selected area when the page reloads with filters.
    if (filterArea.value) {
        areaSearch.value = filterArea.value;
    }
}
const propertyIndexNode = document.getElementById('propertyIndex');
const propertyIndex = propertyIndexNode ? JSON.parse(propertyIndexNode.textContent) : [];
function initials(value) {
    return value.split(/\s+/).map((word) => word[0] || '').join('').toLowerCase();
}
function propertyMatches(item, query) {
    const haystack = `${item.name} ${item.city} ${item.state} ${item.area}`.toLowerCase();
    return haystack.includes(query) || initials(item.name).startsWith(query) || initials(`${item.city} ${item.name}`).includes(query);
}

document.querySelectorAll('.property-pick').forEach((pick) => {
    const form = pick.closest('form');
    const citySelect = form?.querySelector('[name="preferred_city"]');
    const search = pick.querySelector('.property-search');
    const hidden = pick.querySelector('.property-id');
    const list = pick.querySelector('.property-results');
    const openPlaceholder = search.dataset.openPlaceholder || 'Search by name or area';
    const lockedPlaceholder = search.dataset.lockedPlaceholder || 'Select a city first';
    const selectedCity = () => (citySelect?.value || '').trim();
    const cityReady = () => !citySelect || selectedCity() !== '';

    function syncCityGate() {
        if (!citySelect) return;
        const ready = cityReady();
        search.disabled = !ready;
        search.placeholder = ready ? openPlaceholder : lockedPlaceholder;
        if (!ready) {
            hidden.value = '';
            search.value = '';
            list.hidden = true;
        }
    }

    function show(query) {
        if (!cityReady()) {
            list.hidden = true;
            return;
        }
        const city = selectedCity();
        const pool = city
            ? propertyIndex.filter((item) => String(item.city || '').toLowerCase() === city.toLowerCase())
            : propertyIndex;
        const matches = pool.filter((item) => query === '' || propertyMatches(item, query)).slice(0, 8);
        list.innerHTML = matches.map((item) => `<li><button type="button" data-id="${item.id}">${item.name} · ${item.area}, ${item.city}</button></li>`).join('');
        list.hidden = matches.length === 0;
    }

    const chosen = propertyIndex.find((item) => item.id === hidden.value);
    if (chosen) {
        if (!citySelect || !selectedCity() || chosen.city.toLowerCase() === selectedCity().toLowerCase()) {
            search.value = `${chosen.name} · ${chosen.city}`;
        }
    }

    search.addEventListener('focus', () => {
        if (!cityReady()) {
            list.hidden = true;
            citySelect?.focus();
            return;
        }
        if (!pick.dataset.locked) show(search.value.trim().toLowerCase());
    });
    search.addEventListener('input', () => {
        if (pick.dataset.locked || !cityReady()) return;
        hidden.value = '';
        show(search.value.trim().toLowerCase());
    });
    list.addEventListener('click', (event) => {
        const button = event.target.closest('button');
        if (!button || button.disabled) return;
        hidden.value = button.dataset.id;
        search.value = button.textContent;
        list.hidden = true;
    });
    citySelect?.addEventListener('change', () => {
        hidden.value = '';
        search.value = '';
        list.hidden = true;
        syncCityGate();
        if (cityReady() && !pick.dataset.locked) {
            search.focus();
            show('');
        }
    });
    syncCityGate();
});

document.querySelectorAll('.country-pick').forEach((pick) => {
    const search = pick.querySelector('.country-search');
    const hidden = pick.querySelector('.country-code');
    const current = pick.querySelector('.country-current');
    const dropdown = pick.querySelector('.country-dropdown');
    const list = pick.querySelector('.country-list');
    const open = () => {
        dropdown.hidden = false;
        current.setAttribute('aria-expanded', 'true');
        search.focus();
    };
    current.addEventListener('click', open);
    search.addEventListener('focus', open);
    search.addEventListener('input', () => {
        const query = search.value.trim().toLowerCase();
        list.querySelectorAll('li').forEach((item) => {
            item.hidden = query !== '' && !item.textContent.toLowerCase().includes(query);
        });
    });
    list.addEventListener('click', (event) => {
        const button = event.target.closest('button');
        if (!button) return;
        hidden.value = button.dataset.code;
        current.firstChild.textContent = button.textContent.trim() + ' ';
        search.value = '';
        dropdown.hidden = true;
        current.setAttribute('aria-expanded', 'false');
    });
});
document.addEventListener('click', (event) => {
    if (!event.target.closest('.country-pick')) {
        document.querySelectorAll('.country-dropdown').forEach((dropdown) => { dropdown.hidden = true; });
        document.querySelectorAll('.country-current').forEach((current) => { current.setAttribute('aria-expanded', 'false'); });
    }
    if (!event.target.closest('.property-pick')) {
        document.querySelectorAll('.property-results').forEach((list) => { list.hidden = true; });
    }
});

document.querySelectorAll('.visit-form').forEach((form) => {
    const dateInput = form.querySelector('[name="visit_date"]');
    const timeInput = form.querySelector('[name="visit_time"]');
    if (!dateInput || !timeInput) return;

    const localDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    const syncVisitTime = () => {
        const now = new Date();
        const today = localDate(now);
        dateInput.min = today;
        if (dateInput.value === today) {
            const minTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
            timeInput.min = minTime;
            if (timeInput.value && timeInput.value < minTime) timeInput.value = '';
        } else {
            timeInput.removeAttribute('min');
        }
    };
    dateInput.addEventListener('change', syncVisitTime);
    syncVisitTime();
});

const visitModal = document.getElementById('visitModal');
const brochureAccessModal = document.getElementById('brochureAccessModal');
document.querySelectorAll('.visit-open').forEach((button) => {
    button.addEventListener('click', () => {
        const form = visitModal.querySelector('form');
        const propertyId = button.dataset.property || '';
        const intent = button.dataset.intent || 'visit';
        form.querySelector('[name="intent"]').value = intent;
        form.querySelector('.property-id').value = propertyId;
        const match = propertyIndex.find((item) => item.id === propertyId);
        const propertyPick = form.querySelector('.property-pick');
        const propertySearch = form.querySelector('.property-search');
        propertySearch.value = match ? `${match.name} · ${match.area}, ${match.city}` : '';
        propertyPick.dataset.locked = intent === 'brochure' ? 'true' : '';
        propertySearch.readOnly = intent === 'brochure';
        form.querySelector('.property-results').hidden = true;
        const schedule = form.querySelector('.visit-schedule');
        if (schedule) schedule.hidden = intent !== 'visit';
        form.querySelectorAll('[name="visit_date"], [name="visit_time"]').forEach((input) => {
            input.required = intent === 'visit';
        });
        visitModal.querySelector('h2').textContent = intent === 'brochure' ? 'Fill the form to open the brochure' : 'Request a site visit';
        visitModal.hidden = false;
        document.body.classList.add('modal-open');
    });
});
if (visitModal) {
    visitModal.querySelector('.visit-close').addEventListener('click', closeVisit);
    visitModal.addEventListener('click', (event) => { if (event.target === visitModal) closeVisit(); });
}
function closeVisit() {
    if (!visitModal) return;
    visitModal.hidden = true;
    document.body.classList.remove('modal-open');
}
function openBrochureAccess(propertyId, brochureUrl) {
    if (!brochureAccessModal || !propertyId) return;
    const link = brochureAccessModal.querySelector('.brochure-download-link');
    const base = link?.dataset.brochureBase || '/actions/brochure.php';
    link.href = brochureUrl || `${base}?id=${encodeURIComponent(propertyId)}&mode=download`;
    brochureAccessModal.hidden = false;
    document.body.classList.add('modal-open');
}
function closeBrochureAccess() {
    if (!brochureAccessModal) return;
    brochureAccessModal.hidden = true;
    document.body.classList.remove('modal-open');
}
if (brochureAccessModal) {
    brochureAccessModal.querySelector('.brochure-access-close').addEventListener('click', closeBrochureAccess);
    brochureAccessModal.addEventListener('click', (event) => { if (event.target === brochureAccessModal) closeBrochureAccess(); });
}

document.querySelectorAll('.visit-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const status = form.querySelector('.form-status');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        const dateInput = form.querySelector('[name="visit_date"]');
        const timeInput = form.querySelector('[name="visit_time"]');
        if (dateInput && timeInput && dateInput.required) {
            const selected = new Date(`${dateInput.value}T${timeInput.value}`);
            if (Number.isNaN(selected.getTime()) || selected <= new Date()) {
                status.textContent = 'Please choose a future date and time for your site visit.';
                status.classList.add('error');
                return;
            }
        }
        const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
        const data = await response.json();
        status.textContent = data.message;
        status.classList.toggle('error', !data.ok);
        if (!data.ok) return;
        const intent = form.querySelector('[name="intent"]').value;
        const propertyId = form.querySelector('[name="property_id"]').value;
        const lockedProperty = form.dataset.lockedProperty || '';
        const token = form.querySelector('[name="csrf_token"]').value;
        const countryCode = form.querySelector('[name="country_code"]')?.value || '+91';
        const countryCurrent = form.querySelector('.country-current');
        const countryLabel = countryCurrent ? countryCurrent.textContent.trim() : 'India +91';
        form.reset();
        form.querySelector('[name="csrf_token"]').value = token;
        form.querySelector('[name="intent"]').value = intent;
        const countryHidden = form.querySelector('[name="country_code"]');
        if (countryHidden) countryHidden.value = countryCode;
        if (countryCurrent && countryCurrent.firstChild) {
            countryCurrent.firstChild.textContent = countryLabel + ' ';
        }
        if (lockedProperty) {
            const lockedInput = form.querySelector('[name="property_id"]');
            if (lockedInput) lockedInput.value = lockedProperty;
        }
        if (form.closest('#visitModal') && intent === 'brochure') {
            closeVisit();
            setTimeout(() => openBrochureAccess(data.property_id || propertyId, data.brochure_url || ''), 200);
            return;
        }
        if (form.closest('#visitModal')) setTimeout(closeVisit, 900);
    });
});

document.querySelectorAll('input[name="phone"]').forEach((input) => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0, 10);
    });
});

document.querySelectorAll('.detail-tab').forEach((tab) => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.detail-tab').forEach((item) => item.classList.remove('is-on'));
        document.querySelectorAll('.detail-panel').forEach((panel) => { panel.hidden = true; panel.classList.remove('is-on'); });
        tab.classList.add('is-on');
        const panel = document.getElementById('panel-' + tab.dataset.panel);
        panel.hidden = false;
        panel.classList.add('is-on');
    });
});


// Clear all property filters
const clearFiltersButton = document.getElementById('clearFilters');

if (clearFiltersButton) {
    clearFiltersButton.addEventListener('click', () => {
        const form = clearFiltersButton.closest('form');

        if (form) {
            window.location.href = form.action;
        }
    });
}
