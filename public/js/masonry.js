(function () {
  'use strict';

  const ROOT = document.querySelector('[data-masonry]');
  if (!ROOT) return;

  const EASE = 'cubic-bezier(0.2, 0.9, 0.2, 1)';
  const DIRECTIONS = ['bottom', 'top', 'left', 'right'];
  const reduced = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const settings = {
    columns: ROOT.dataset.columns || 'auto',
    columnWidth: Number(ROOT.dataset.columnWidth || 260),
    gap: Number(ROOT.dataset.gap || 14),
    radius: Number(ROOT.dataset.radius || 16),
    animateFrom: ROOT.dataset.animateFrom || 'bottom',
    stagger: Number(ROOT.dataset.stagger || 0.05),
    duration: Number(ROOT.dataset.duration || 0.6),
    bounce: Number(ROOT.dataset.bounce || 0.15),
    blurToFocus: ROOT.dataset.blurToFocus !== 'false',
    revealOnScroll: ROOT.dataset.revealOnScroll !== 'false',
    hoverEffect: ROOT.dataset.hoverEffect || 'zoom',
    captions: ROOT.dataset.captions || 'hover',
    parallax: Number(ROOT.dataset.parallax || 0.12),
    lightbox: ROOT.dataset.lightbox !== 'false',
  };

  ROOT.style.setProperty('--masonry-radius', `${Math.max(0, settings.radius)}px`);
  ROOT.setAttribute('data-hover', settings.hoverEffect);

  const nodes = new Map();
  const springs = new Map();
  const loaded = new Set();
  const waiting = new Set();
  const pending = new Set();
  const measured = {};
  const engine = { raf: 0, last: 0, flush: 0 };
  let width = 0;
  let openId = null;
  let closing = false;
  let boxEl = null;
  let token = 0;

  const items = [...ROOT.querySelectorAll('.masonry__item')].map((node, index) => {
    const id = String(node.dataset.id || index + 1);
    node.dataset.id = id;
    nodes.set(id, node);
    return {
      id,
      node,
      type: node.dataset.type === 'video' ? 'video' : 'image',
      src: node.dataset.src || '',
      title: node.dataset.title || '',
      description: node.dataset.description || '',
      alt: node.dataset.alt || node.dataset.title || '',
      width: Number(node.dataset.width || 0),
      height: Number(node.dataset.height || 0),
      span: Number(node.dataset.span || 1),
    };
  });

  const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

  const aspectOf = (item) => {
    if (item.width > 0 && item.height > 0) return item.height / item.width;
    if (measured[item.id]) return measured[item.id];
    if (item.type === 'video') return 9 / 16;
    return 1.15;
  };

  const gridOf = (w, columns, columnWidth, gap) => {
    const fixed = Number(columns);
    const count =
      columns !== 'auto' && fixed > 0
        ? Math.max(1, Math.round(fixed))
        : Math.max(1, Math.floor((w + gap) / (Math.max(40, columnWidth) + gap)));
    return { count, size: Math.max(1, (w - gap * (count - 1)) / count) };
  };

  const place = (heights, item, grid, gap, commit) => {
    const { count, size } = grid;
    const span = Math.min(count, Math.max(1, Math.round(item.span || 1)));
    let column = 0;
    let top = Infinity;
    for (let start = 0; start <= count - span; start++) {
      let y = 0;
      for (let k = start; k < start + span; k++) y = Math.max(y, heights[k]);
      if (y < top - 0.5) {
        top = y;
        column = start;
      }
    }
    const tileW = size * span + gap * (span - 1);
    const tileH = Math.max(1, tileW * aspectOf(item));
    if (commit) {
      for (let k = column; k < column + span; k++) heights[k] = top + tileH + gap;
    }
    return { x: Math.round(column * (size + gap)), y: Math.round(top), w: tileW, h: tileH };
  };

  const layoutOf = (list, w) => {
    const grid = gridOf(w, settings.columns, settings.columnWidth, settings.gap);
    const heights = new Array(grid.count).fill(0);
    const tiles = list.map((item) => ({
      item,
      id: item.id,
      ...place(heights, item, grid, settings.gap, true),
    }));
    return { tiles, height: Math.max(0, Math.max(...heights, 0) - settings.gap) };
  };

  const paint = (id, spring) => {
    const node = nodes.get(id);
    if (!node) return;
    node.style.transform = `translate3d(${spring.x}px, ${spring.y}px, 0) scale(${spring.s})`;
    if (spring.reveal < 1) {
      const eased = 1 - Math.pow(1 - Math.max(0, spring.reveal), 3);
      node.style.opacity = String(eased);
      node.style.filter = spring.blur ? `blur(${((1 - eased) * 12).toFixed(2)}px)` : '';
    } else if (node.style.opacity !== '') {
      node.style.opacity = '';
      node.style.filter = '';
    }
  };

  const frame = (now) => {
    engine.raf = 0;
    const elapsed = clamp((now - engine.last) / 1000, 0, 1 / 20);
    engine.last = now;
    const omega = (Math.PI * 2) / settings.duration;
    const zeta = clamp(1 - settings.bounce, 0.1, 1);
    const steps = Math.max(1, Math.ceil(elapsed / (1 / 240)));
    const dt = elapsed / steps;
    let active = false;
    springs.forEach((spring, id) => {
      if (!spring.shown) return;
      if (now < spring.start) {
        active = true;
        return;
      }
      for (let i = 0; i < steps; i++) {
        spring.vx += (-omega * omega * (spring.x - spring.tx) - 2 * zeta * omega * spring.vx) * dt;
        spring.vy += (-omega * omega * (spring.y - spring.ty) - 2 * zeta * omega * spring.vy) * dt;
        spring.vs += (-omega * omega * (spring.s - 1) - 2 * zeta * omega * spring.vs) * dt;
        spring.x += spring.vx * dt;
        spring.y += spring.vy * dt;
        spring.s += spring.vs * dt;
      }
      const resting =
        Math.abs(spring.x - spring.tx) < 0.05 &&
        Math.abs(spring.y - spring.ty) < 0.05 &&
        Math.abs(spring.s - 1) < 0.0005 &&
        Math.abs(spring.vx) + Math.abs(spring.vy) < 0.05 &&
        Math.abs(spring.vs) < 0.001;
      if (resting) {
        spring.x = spring.tx;
        spring.y = spring.ty;
        spring.s = 1;
        spring.vx = spring.vy = spring.vs = 0;
      } else active = true;
      if (spring.reveal < 1) {
        spring.reveal = clamp((now - spring.start) / (settings.duration * 1000), 0, 1);
        if (spring.reveal < 1) active = true;
      }
      paint(id, spring);
    });
    if (active) engine.raf = requestAnimationFrame(frame);
  };

  const kick = () => {
    if (engine.raf) return;
    engine.last = performance.now();
    engine.raf = requestAnimationFrame(frame);
  };

  const visibleCenter = () => {
    const rect = ROOT.getBoundingClientRect();
    const top = Math.max(0, -rect.top);
    const bottom = Math.min(rect.height, window.innerHeight - rect.top);
    return bottom > top ? (top + bottom) / 2 : rect.height / 2;
  };

  const flushReveals = () => {
    engine.flush = 0;
    const batch = [];
    pending.forEach((id) => {
      const spring = springs.get(id);
      if (spring && !spring.shown) batch.push({ id, spring });
    });
    pending.clear();
    if (!batch.length) return;
    batch.sort((a, b) => a.spring.ty - b.spring.ty || a.spring.tx - b.spring.tx);
    const now = performance.now();
    const still = reduced() || settings.animateFrom === 'none';
    batch.forEach(({ id, spring }, rank) => {
      spring.shown = true;
      spring.blur = settings.blurToFocus && !still;
      if (still) {
        spring.x = spring.tx;
        spring.y = spring.ty;
        spring.s = 1;
        spring.reveal = 1;
        spring.start = now;
        paint(id, spring);
        return;
      }
      const direction =
        settings.animateFrom === 'random'
          ? DIRECTIONS[Math.floor(Math.random() * DIRECTIONS.length)]
          : settings.animateFrom;
      const distance = 60 + Math.min(spring.h, 280) * 0.35;
      spring.x = spring.tx;
      spring.y = spring.ty;
      spring.s = 0.96;
      if (direction === 'center') {
        spring.x = (width || ROOT.clientWidth) / 2 - spring.w / 2;
        spring.y = visibleCenter() - spring.h / 2;
        spring.s = 0.55;
      } else if (direction === 'bottom') spring.y += distance;
      else if (direction === 'top') spring.y -= distance;
      else if (direction === 'left') spring.x -= distance;
      else if (direction === 'right') spring.x += distance;
      spring.vx = spring.vy = spring.vs = 0;
      spring.reveal = 0;
      spring.start = now + rank * settings.stagger * 1000;
      paint(id, spring);
    });
    kick();
  };

  const requestReveal = (id) => {
    const spring = springs.get(id);
    if (!spring || spring.shown) return;
    if (!loaded.has(id)) {
      waiting.add(id);
      return;
    }
    waiting.delete(id);
    pending.add(id);
    if (!engine.flush) engine.flush = requestAnimationFrame(flushReveals);
  };

  const markLoaded = (id) => {
    loaded.add(id);
    const node = nodes.get(id);
    node?.querySelector('img, video')?.setAttribute('data-loaded', '');
    if (waiting.has(id)) requestReveal(id);
  };

  const applyLayout = () => {
    if (!width) return;
    const layout = layoutOf(items, width);
    const instant = reduced();
    layout.tiles.forEach((tile) => {
      const node = nodes.get(tile.id);
      let spring = springs.get(tile.id);
      if (!spring) {
        spring = {
          x: tile.x,
          y: tile.y,
          s: 1,
          vx: 0,
          vy: 0,
          vs: 0,
          tx: tile.x,
          ty: tile.y,
          w: tile.w,
          h: tile.h,
          shown: false,
          blur: false,
          reveal: 0,
          start: 0,
        };
        springs.set(tile.id, spring);
        if (node) {
          node.style.opacity = '0';
          node.style.transform = `translate3d(${tile.x}px, ${tile.y}px, 0)`;
        }
      } else if (spring.shown && Math.abs(spring.w - tile.w) > 0.5 && !instant) {
        spring.s *= spring.w / tile.w;
      }
      spring.tx = tile.x;
      spring.ty = tile.y;
      spring.w = tile.w;
      spring.h = tile.h;
      if (node) {
        node.style.width = `${tile.w}px`;
        node.style.height = `${tile.h}px`;
      }
      if (!spring.shown) {
        spring.x = tile.x;
        spring.y = tile.y;
        if (node) node.style.transform = `translate3d(${tile.x}px, ${tile.y}px, 0)`;
      } else if (instant) {
        spring.x = tile.x;
        spring.y = tile.y;
        spring.s = 1;
        paint(tile.id, spring);
      }
    });
    ROOT.style.height = `${layout.height}px`;
    kick();

    if (!settings.revealOnScroll || typeof IntersectionObserver === 'undefined') {
      layout.tiles.forEach((tile) => requestReveal(tile.id));
      return;
    }
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          const id = entry.target.getAttribute('data-id');
          if (id) requestReveal(id);
          observer.unobserve(entry.target);
        });
      },
      { rootMargin: '0px 0px -6% 0px' }
    );
    layout.tiles.forEach((tile) => {
      const node = nodes.get(tile.id);
      if (node && !springs.get(tile.id)?.shown) observer.observe(node);
    });
  };

  const measureImages = () => {
    items.forEach((item) => {
      if (item.width > 0 && item.height > 0) {
        markLoaded(item.id);
        return;
      }
      if (item.type === 'video') {
        measured[item.id] = 9 / 16;
        const videoNode = item.node.querySelector('video');
        if (videoNode) {
          const done = () => markLoaded(item.id);
          if (videoNode.readyState >= 1) done();
          else {
            videoNode.addEventListener('loadedmetadata', done, { once: true });
            videoNode.addEventListener('error', done, { once: true });
          }
        } else {
          markLoaded(item.id);
        }
        return;
      }
      const image = new Image();
      image.onload = () => {
        if (image.naturalWidth) {
          measured[item.id] = image.naturalHeight / image.naturalWidth;
          applyLayout();
        }
        markLoaded(item.id);
      };
      image.onerror = () => markLoaded(item.id);
      image.src = item.src;
      const imgNode = item.node.querySelector('img');
      if (imgNode?.complete && imgNode.naturalWidth) {
        measured[item.id] = imgNode.naturalHeight / imgNode.naturalWidth;
        markLoaded(item.id);
      }
    });
  };

  const mediaRect = (id) =>
    nodes.get(id)?.querySelector('[data-media]')?.getBoundingClientRect() ?? null;

  const frameFor = (item) => {
    const aspect = aspectOf(item);
    const pad = window.innerWidth < 640 ? 16 : 72;
    const room = item.title || item.description ? 64 : 0;
    const maxW = Math.max(40, window.innerWidth - pad * 2);
    const maxH = Math.max(40, window.innerHeight - pad * 2 - room);
    let w = maxW;
    let h = w * aspect;
    if (h > maxH) {
      h = maxH;
      w = h / aspect;
    }
    return {
      left: (window.innerWidth - w) / 2,
      top: (window.innerHeight - room - h) / 2,
      width: w,
      height: h,
      room,
    };
  };

  const ensureBox = () => {
    if (boxEl) return boxEl;
    boxEl = document.createElement('div');
    boxEl.className = 'masonry-lightbox';
    boxEl.setAttribute('role', 'dialog');
    boxEl.setAttribute('aria-modal', 'true');
    boxEl.innerHTML = `
      <div class="masonry-lightbox__backdrop" data-box-backdrop></div>
      <div data-box-media></div>
      <div class="masonry-lightbox__caption" data-box-caption data-box-fade></div>
      <button type="button" class="masonry-lightbox__button masonry-lightbox__close" data-box-close data-box-fade aria-label="Close">×</button>
      <button type="button" class="masonry-lightbox__button masonry-lightbox__prev" data-box-prev data-box-fade aria-label="Previous">←</button>
      <button type="button" class="masonry-lightbox__button masonry-lightbox__next" data-box-next data-box-fade aria-label="Next">→</button>
    `;
    document.body.appendChild(boxEl);
    boxEl.querySelector('[data-box-backdrop]').addEventListener('click', closeBox);
    boxEl.querySelector('[data-box-close]').addEventListener('click', closeBox);
    boxEl.querySelector('[data-box-prev]').addEventListener('click', () => stepBox(-1));
    boxEl.querySelector('[data-box-next]').addEventListener('click', () => stepBox(1));
    return boxEl;
  };

  const openIndex = () => items.findIndex((item) => item.id === openId);
  const openItem = () => items[openIndex()] || null;

  const renderOpenMedia = (item, box) => {
    const holder = boxEl.querySelector('[data-box-media]');
    holder.innerHTML = '';
    let media;
    if (item.type === 'video') {
      media = document.createElement('video');
      media.className = 'masonry-lightbox__video';
      media.controls = true;
      media.playsInline = true;
      media.src = item.src;
      media.autoplay = true;
    } else {
      media = document.createElement('img');
      media.className = 'masonry-lightbox__image';
      media.src = item.src;
      media.alt = item.alt;
      media.addEventListener('click', closeBox);
    }
    media.setAttribute('data-box-image', '');
    media.draggable = false;
    media.style.width = `${box.width}px`;
    media.style.height = `${box.height}px`;
    media.style.left = `${box.left}px`;
    media.style.top = `${box.top}px`;
    media.style.borderRadius = '6px';
    holder.appendChild(media);

    const caption = boxEl.querySelector('[data-box-caption]');
    caption.style.top = `${box.top + box.height + 18}px`;
    caption.innerHTML = '';
    if (item.title) {
      const title = document.createElement('span');
      title.textContent = item.title;
      caption.appendChild(title);
    }
    if (item.description) {
      const desc = document.createElement('span');
      desc.className = 'masonry-lightbox__muted';
      desc.textContent = item.description;
      caption.appendChild(desc);
    }
    caption.hidden = !(item.title || item.description);

    const multi = items.length > 1;
    boxEl.querySelector('[data-box-prev]').hidden = !multi;
    boxEl.querySelector('[data-box-next]').hidden = !multi;
    return media;
  };

  const openBox = (id) => {
    if (!settings.lightbox) return;
    const item = items.find((entry) => entry.id === id);
    if (!item) return;
    token += 1;
    closing = false;
    openId = id;
    const box = ensureBox();
    const frame = frameFor(item);
    const media = renderOpenMedia(item, frame);
    box.setAttribute('aria-label', item.title || item.alt || 'Gallery media');
    document.documentElement.style.overflow = 'hidden';
    nodes.get(id)?.setAttribute('data-hidden', '');

    const rect = mediaRect(id);
    if (rect && !reduced() && item.type === 'image') {
      const target = media.getBoundingClientRect();
      const scale = rect.width / target.width;
      media.animate(
        [
          {
            transform: `translate(${rect.left - target.left}px, ${rect.top - target.top}px) scale(${scale})`,
            borderRadius: `${settings.radius / Math.max(scale, 0.01)}px`,
          },
          { transform: 'none', borderRadius: '6px' },
        ],
        { duration: 520, easing: EASE }
      );
      box
        .querySelector('[data-box-backdrop]')
        ?.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 360, easing: 'ease' });
      box.querySelectorAll('[data-box-fade]').forEach((fade) =>
        fade.animate([{ opacity: 0 }, { opacity: 0, offset: 0.5 }, { opacity: 1 }], {
          duration: 560,
          easing: 'ease',
        })
      );
    }
    box.querySelector('[data-box-close]')?.focus({ preventScroll: true });
  };

  const closeBox = () => {
    if (!openId || closing) return;
    token += 1;
    const currentToken = token;
    const item = openItem();
    const media = boxEl?.querySelector('[data-box-image]');
    const backdrop = boxEl?.querySelector('[data-box-backdrop]');
    const rect = mediaRect(openId);
    closing = true;
    const finish = () => {
      if (currentToken !== token) return;
      nodes.get(openId)?.removeAttribute('data-hidden');
      openId = null;
      closing = false;
      if (boxEl) {
        boxEl.remove();
        boxEl = null;
      }
      document.documentElement.style.overflow = '';
    };
    if (!media || !rect || reduced() || item?.type === 'video') {
      finish();
      return;
    }
    const current = media.getBoundingClientRect();
    const scale = rect.width / current.width;
    media.getAnimations().forEach((animation) => animation.cancel());
    const animation = media.animate(
      [
        { transform: 'none', borderRadius: '6px' },
        {
          transform: `translate(${rect.left - current.left}px, ${rect.top - current.top}px) scale(${scale})`,
          borderRadius: `${settings.radius / Math.max(scale, 0.01)}px`,
        },
      ],
      { duration: 380, easing: EASE, fill: 'forwards' }
    );
    backdrop?.animate([{ opacity: 1 }, { opacity: 0 }], {
      duration: 300,
      easing: 'ease',
      fill: 'forwards',
    });
    boxEl
      ?.querySelectorAll('[data-box-fade]')
      .forEach((node) =>
        node.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 160, fill: 'forwards' })
      );
    animation.finished.then(finish).catch(finish);
  };

  const stepBox = (direction) => {
    if (!openId || closing || items.length < 2) return;
    const next = items[(openIndex() + direction + items.length) % items.length];
    nodes.get(openId)?.removeAttribute('data-hidden');
    openId = next.id;
    const frame = frameFor(next);
    const media = renderOpenMedia(next, frame);
    nodes.get(openId)?.setAttribute('data-hidden', '');
    if (!reduced()) {
      media.animate(
        [
          { opacity: 0, transform: 'scale(0.97)' },
          { opacity: 1, transform: 'none' },
        ],
        { duration: 320, easing: EASE }
      );
    }
  };

  items.forEach((item) => {
    const card = item.node.querySelector('[data-card]');
    card?.addEventListener('click', (event) => {
      event.preventDefault();
      openBox(item.id);
    });
  });

  window.addEventListener('keydown', (event) => {
    if (!openId) return;
    if (event.key === 'Escape') closeBox();
    else if (event.key === 'ArrowRight') stepBox(1);
    else if (event.key === 'ArrowLeft') stepBox(-1);
  });

  if (settings.parallax > 0) {
    let raf = 0;
    const updateParallax = () => {
      raf = 0;
      const strength = settings.parallax;
      const center = window.innerHeight / 2;
      nodes.forEach((node) => {
        const layer = node.querySelector('[data-parallax]');
        if (!layer) return;
        const rect = node.getBoundingClientRect();
        if (rect.bottom < -200 || rect.top > window.innerHeight + 200) return;
        const reach = strength * 0.2 * rect.height;
        const shift = clamp(-(rect.top + rect.height / 2 - center) * strength * 0.25, -reach, reach);
        layer.style.transform = `translate3d(0, ${shift.toFixed(2)}px, 0) scale(${(
          1 +
          strength * 0.4 +
          0.002
        ).toFixed(3)})`;
      });
    };
    const schedule = () => {
      if (!raf) raf = requestAnimationFrame(updateParallax);
    };
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule, { passive: true });
  }

  const resizeObserver = new ResizeObserver(([entry]) => {
    width = Math.round(entry.contentRect.width);
    applyLayout();
  });
  resizeObserver.observe(ROOT);
  width = Math.round(ROOT.getBoundingClientRect().width);
  measureImages();
  applyLayout();

  setTimeout(() => {
    waiting.forEach((id) => {
      loaded.add(id);
      requestReveal(id);
    });
  }, 2500);
})();
