(() => {
  const qs = (sel, root = document) => root.querySelector(sel);
  const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  // Searchable selects (city / area)
  const setupSearchSelect = (wrap) => {
    const input = qs('.search-input', wrap);
    const hidden = qs('input[type="hidden"]', wrap);
    const list = qs('.list', wrap);
    if (!input || !hidden || !list) return;

    const buttons = () => qsa('button[data-value]', list);

    const filter = () => {
      const q = input.value.trim().toLowerCase();
      buttons().forEach((btn) => {
        const label = (btn.dataset.label || btn.textContent || '').toLowerCase();
        btn.hidden = q !== '' && !label.includes(q);
      });
    };

    const close = () => wrap.classList.remove('open');
    const open = () => wrap.classList.add('open');

    input.addEventListener('focus', open);
    input.addEventListener('input', () => {
      open();
      filter();
      if (input.value.trim() === '') hidden.value = '';
    });

    list.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-value]');
      if (!btn) return;
      hidden.value = btn.dataset.value || '';
      input.value = btn.dataset.label || btn.textContent || '';
      hidden.dispatchEvent(new Event('change', { bubbles: true }));
      close();
    });

    document.addEventListener('click', (e) => {
      if (!wrap.contains(e.target)) close();
    });
  };

  qsa('[data-search-select]').forEach(setupSearchSelect);

  // City -> area dynamic load into searchable area select
  const cityHidden = document.getElementById('city_id');
  const areaHidden = document.getElementById('area_id');
  const areaInput = document.getElementById('areaSearchInput');
  const areaList = document.getElementById('areaList');
  const areasUrl = areaHidden?.dataset.areasUrl;

  const loadAreas = async () => {
    if (!cityHidden || !areaHidden || !areaInput || !areaList || !areasUrl) return;
    const cityId = cityHidden.value;
    areaList.innerHTML = '';
    areaInput.value = '';
    areaHidden.value = '';
    areaInput.disabled = !cityId;
    if (!cityId) return;

    try {
      const res = await fetch(`${areasUrl}?city_id=${encodeURIComponent(cityId)}`);
      const data = await res.json();
      (data.areas || []).forEach((item) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.dataset.value = String(item.id);
        btn.dataset.label = item.name;
        btn.textContent = item.name;
        areaList.appendChild(btn);
      });

      const selected = areaHidden.dataset.selected || '';
      if (selected) {
        const match = (data.areas || []).find((a) => String(a.id) === String(selected));
        if (match) {
          areaHidden.value = String(match.id);
          areaInput.value = match.name;
        }
      }
    } catch (err) {
      console.error(err);
    }
  };

  cityHidden?.addEventListener('change', () => {
    if (areaHidden) areaHidden.dataset.selected = '';
    loadAreas();
  });

  // Combo: searchable + create for property type
  qsa('[data-combo]').forEach((wrap) => {
    const input = qs('.combo-input', wrap);
    const hidden = qs('input[type="hidden"]', wrap);
    const list = qs('.list', wrap);
    const quickCreate = qs('[data-quick-create]', wrap);
    const createPanel = qs('[data-create-panel]', wrap);
    const createInput = qs('.combo-create-input', wrap);
    const confirmCreate = qs('[data-confirm-create]', wrap);
    const feedback = qs('[data-create-feedback]', wrap);
    if (!input || !hidden || !list) return;

    const buttons = () => qsa('button[data-value]', list);

    const filter = () => {
      const q = input.value.trim().toLowerCase();
      buttons().forEach((btn) => {
        btn.hidden = q !== '' && !(btn.dataset.value || '').toLowerCase().includes(q);
      });
    };

    const selectType = (val) => {
      hidden.value = val;
      input.value = val;
      wrap.classList.remove('open');
      if (createPanel) createPanel.classList.remove('is-open');
      if (createInput) createInput.value = '';
    };

    const createType = (raw) => {
      const val = String(raw || '').trim();
      if (!val) {
        createInput?.focus();
        return;
      }
      const exists = buttons().some((b) => (b.dataset.value || '').toLowerCase() === val.toLowerCase());
      if (!exists) {
        const option = document.createElement('button');
        option.type = 'button';
        option.dataset.value = val;
        option.textContent = val;
        list.prepend(option);
      }
      selectType(val);
      if (feedback) {
        feedback.textContent = `"${val}" added and selected.`;
        feedback.classList.add('is-on');
        setTimeout(() => feedback.classList.remove('is-on'), 2200);
      }
    };

    input.addEventListener('focus', () => wrap.classList.add('open'));
    input.addEventListener('input', () => {
      wrap.classList.add('open');
      hidden.value = input.value.trim();
      filter();
    });

    quickCreate?.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      wrap.classList.remove('open');
      createPanel?.classList.add('is-open');
      createInput?.focus();
    });

    confirmCreate?.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      createType(createInput?.value || '');
    });

    createInput?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        createType(createInput.value);
      }
    });

    list.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-value]');
      if (!btn) return;
      selectType(btn.dataset.value || '');
    });

    document.addEventListener('click', (e) => {
      if (!wrap.contains(e.target)) {
        wrap.classList.remove('open');
        createPanel?.classList.remove('is-open');
      }
    });
  });

  // Show/hide password toggles
  qsa('[data-toggle-password]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-toggle-password');
      const input = id ? document.getElementById(id) : null;
      if (!input) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.textContent = show ? 'Hide' : 'Show';
    });
  });

  // Drag & drop upload zones
  qsa('[data-dropzone]').forEach((zone) => {
    const input = qs('input[type="file"]', zone);
    const chips = qs('[data-file-chips]', zone);
    if (!input) return;

    const renderChips = () => {
      if (!chips) return;
      chips.innerHTML = '';
      const files = Array.from(input.files || []);
      files.forEach((file) => {
        const chip = document.createElement('span');
        chip.className = 'file-chip';
        chip.textContent = file.name;
        chips.appendChild(chip);
      });
    };

    zone.addEventListener('click', (e) => {
      if (e.target === input) return;
      input.click();
    });

    ['dragenter', 'dragover'].forEach((evt) => {
      zone.addEventListener(evt, (e) => {
        e.preventDefault();
        e.stopPropagation();
        zone.classList.add('is-dragover');
      });
    });

    ['dragleave', 'drop'].forEach((evt) => {
      zone.addEventListener(evt, (e) => {
        e.preventDefault();
        e.stopPropagation();
        zone.classList.remove('is-dragover');
      });
    });

    zone.addEventListener('drop', (e) => {
      const files = e.dataTransfer?.files;
      if (!files || files.length === 0) return;
      const dt = new DataTransfer();
      if (input.multiple) {
        Array.from(files).forEach((f) => dt.items.add(f));
      } else {
        dt.items.add(files[0]);
      }
      input.files = dt.files;
      renderChips();
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    input.addEventListener('change', renderChips);
  });

  // Settings phone fields: force +91 / 10 digits
  const bindPhone = (digitsInputId) => {
    const input = document.getElementById(digitsInputId);
    if (!input) return;
    input.addEventListener('input', () => {
      input.value = input.value.replace(/\D/g, '').slice(0, 10);
    });
  };
  bindPhone('whatsapp_digits');
  bindPhone('contact_phone_digits');

  // Properties list drag-and-drop ordering
  const propertiesList = qs('[data-sortable-properties]');
  if (propertiesList) {
    const status = document.getElementById('propertyOrderStatus');
    let dragEl = null;
    let saveTimer = null;

    const saveOrder = async () => {
      const order = qsa('tr[data-property-id]', propertiesList).map((row) => row.dataset.propertyId);
      if (status) status.textContent = 'Saving order…';
      try {
        const res = await fetch(propertiesList.dataset.reorderUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            csrf_token: propertiesList.dataset.csrf,
            order,
          }),
        });
        const data = await res.json();
        if (status) {
          status.textContent = data.ok
            ? 'Order saved · this is how listings appear on the website'
            : (data.message || 'Could not save order');
        }
      } catch (err) {
        if (status) status.textContent = 'Could not save order';
      }
    };

    const queueSave = () => {
      clearTimeout(saveTimer);
      saveTimer = setTimeout(saveOrder, 250);
    };

    qsa('tr[data-property-id]', propertiesList).forEach((row) => {
      row.addEventListener('dragstart', (e) => {
        dragEl = row;
        row.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', row.dataset.propertyId || '');
      });
      row.addEventListener('dragend', () => {
        row.classList.remove('is-dragging');
        qsa('tr[data-property-id]', propertiesList).forEach((el) => el.classList.remove('is-drop-target'));
        dragEl = null;
        queueSave();
      });
      row.addEventListener('dragover', (e) => {
        e.preventDefault();
        if (!dragEl || dragEl === row) return;
        row.classList.add('is-drop-target');
        const rect = row.getBoundingClientRect();
        const before = e.clientY < rect.top + rect.height / 2;
        if (before) {
          propertiesList.insertBefore(dragEl, row);
        } else {
          propertiesList.insertBefore(dragEl, row.nextSibling);
        }
      });
      row.addEventListener('dragleave', () => row.classList.remove('is-drop-target'));
      row.addEventListener('drop', (e) => {
        e.preventDefault();
        row.classList.remove('is-drop-target');
      });
    });
  }

  // Gallery drag-and-drop ordering
  const gallery = qs('[data-sortable-gallery]');
  if (gallery) {
    const status = document.getElementById('galleryOrderStatus');
    let dragEl = null;
    let saveTimer = null;

    const refreshLabels = () => {
      qsa('.media-item', gallery).forEach((item, index) => {
        const label = qs('[data-order-label]', item);
        if (label) label.textContent = `Order ${index + 1}`;
        const meta = qs('.meta', item);
        if (!meta) return;
        let primary = qs('em', meta);
        if (index === 0) {
          if (!primary) {
            primary = document.createElement('em');
            primary.textContent = 'Primary';
            meta.appendChild(primary);
          }
        } else if (primary) {
          primary.remove();
        }
      });
    };

    const saveOrder = async () => {
      const order = qsa('.media-item', gallery).map((item) => item.dataset.mediaId);
      if (status) status.textContent = 'Saving order…';
      try {
        const res = await fetch(gallery.dataset.reorderUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            csrf_token: gallery.dataset.csrf,
            property_id: Number(gallery.dataset.propertyId || 0),
            order,
          }),
        });
        const data = await res.json();
        if (status) {
          status.textContent = data.ok
            ? 'Order saved · first image is primary'
            : (data.message || 'Could not save order');
        }
      } catch (err) {
        if (status) status.textContent = 'Could not save order';
      }
    };

    const queueSave = () => {
      clearTimeout(saveTimer);
      saveTimer = setTimeout(saveOrder, 250);
    };

    qsa('.media-item', gallery).forEach((item) => {
      item.addEventListener('dragstart', (e) => {
        dragEl = item;
        item.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', item.dataset.mediaId || '');
      });
      item.addEventListener('dragend', () => {
        item.classList.remove('is-dragging');
        qsa('.media-item', gallery).forEach((el) => el.classList.remove('is-drop-target'));
        dragEl = null;
        refreshLabels();
        queueSave();
      });
      item.addEventListener('dragover', (e) => {
        e.preventDefault();
        if (!dragEl || dragEl === item) return;
        item.classList.add('is-drop-target');
        const rect = item.getBoundingClientRect();
        const before = e.clientY < rect.top + rect.height / 2;
        if (before) {
          gallery.insertBefore(dragEl, item);
        } else {
          gallery.insertBefore(dragEl, item.nextSibling);
        }
      });
      item.addEventListener('dragleave', () => item.classList.remove('is-drop-target'));
      item.addEventListener('drop', (e) => {
        e.preventDefault();
        item.classList.remove('is-drop-target');
      });
    });
  }
})();
