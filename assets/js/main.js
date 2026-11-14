/* Prime Care Health Solutions — interactions */
(() => {
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Sticky header + back-to-top */
  const header = $('[data-header]'), totop = $('[data-totop]');
  const onScroll = () => {
    const y = scrollY;
    header && header.classList.toggle('is-scrolled', y > 10);
    totop && totop.classList.toggle('is-visible', y > 700);
  };
  addEventListener('scroll', onScroll, { passive: true }); onScroll();
  totop && totop.addEventListener('click', () => scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' }));

  /* Mobile drawer */
  const drawer = $('[data-drawer]'), burger = $('[data-burger]');
  let lastFocus;
  const openDrawer = () => {
    lastFocus = document.activeElement;
    drawer.hidden = false;
    requestAnimationFrame(() => drawer.classList.add('is-open'));
    burger.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
    setTimeout(() => $('[data-drawer-close]', drawer).focus(), 50);
  };
  const closeDrawer = () => {
    drawer.classList.remove('is-open');
    burger.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    setTimeout(() => { drawer.hidden = true; lastFocus && lastFocus.focus(); }, 300);
  };
  if (drawer && burger) {
    burger.addEventListener('click', openDrawer);
    $('[data-drawer-close]', drawer).addEventListener('click', closeDrawer);
    drawer.addEventListener('click', e => { if (e.target === drawer) closeDrawer(); });
    document.addEventListener('keydown', e => {
      if (drawer.hidden) return;
      if (e.key === 'Escape') closeDrawer();
      if (e.key === 'Tab') { // focus trap
        const f = $$('a,button', drawer); const first = f[0], last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });
  }

  /* Location phone picker (remembers choice) */
  const picker = $('[data-loc-picker]');
  if (picker) {
    const btn = $('.loc-picker__btn', picker), mobileCall = $('[data-mobile-call]');
    const apply = b => {
      $('[data-loc-label]', picker).textContent = b.dataset.label;
      $('[data-loc-phone]', picker).textContent = b.dataset.phone;
      if (mobileCall) mobileCall.href = 'tel:' + b.dataset.tel;
    };
    const set = open => { picker.classList.toggle('is-open', open); btn.setAttribute('aria-expanded', open); };
    btn.addEventListener('click', e => { e.stopPropagation(); set(!picker.classList.contains('is-open')); });
    $$('[data-loc]', picker).forEach(b => b.addEventListener('click', () => {
      apply(b); set(false); btn.focus();
      try { localStorage.setItem('pc_loc', b.dataset.loc); } catch (_) {}
    }));
    document.addEventListener('click', () => set(false));
    document.addEventListener('keydown', e => e.key === 'Escape' && set(false));
    try { const s = localStorage.getItem('pc_loc'); const b = s && $(`[data-loc="${s}"]`, picker); b && apply(b); } catch (_) {}
  }

  /* Reveal on scroll */
  const reveals = $$('.reveal');
  if ('IntersectionObserver' in window && !reduce) {
    const io = new IntersectionObserver(es => es.forEach(en => { if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); } }), { rootMargin: '0px 0px -8% 0px', threshold: .08 });
    reveals.forEach(el => io.observe(el));
  } else reveals.forEach(el => el.classList.add('is-in'));

  /* Count-up stats */
  const counters = $$('[data-count]');
  if (counters.length && 'IntersectionObserver' in window && !reduce) {
    const co = new IntersectionObserver(es => es.forEach(en => {
      if (!en.isIntersecting) return;
      const el = en.target, end = +el.dataset.count, t0 = performance.now(), dur = 1100;
      const tick = t => { const p = Math.min((t - t0) / dur, 1); el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))); p < 1 && requestAnimationFrame(tick); };
      el.textContent = '0'; requestAnimationFrame(tick); co.unobserve(el);
    }), { threshold: .6 });
    counters.forEach(c => co.observe(c));
  }

  /* Lazy maps */
  const loadMap = box => {
    if (!box || box.dataset.loaded) return;
    const f = document.createElement('iframe');
    f.src = box.dataset.mapSrc; f.title = box.dataset.mapTitle || 'Map'; f.loading = 'lazy'; f.referrerPolicy = 'no-referrer-when-downgrade';
    f.addEventListener('load', () => { const ph = $('.loc-map__ph', box); ph && ph.remove(); });
    box.appendChild(f); box.dataset.loaded = '1';
  };
  const maps = $$('[data-map-src]');
  if ('IntersectionObserver' in window) {
    const mo = new IntersectionObserver(es => es.forEach(en => { if (en.isIntersecting && en.target.offsetParent !== null) { loadMap(en.target); mo.unobserve(en.target); } }), { rootMargin: '200px' });
    maps.forEach(m => mo.observe(m));
  } else maps.forEach(loadMap);

  /* Accessible tabs */
  $$('[data-tabs]').forEach(wrap => {
    const tabs = $$('[role="tab"]', wrap);
    const select = t => {
      tabs.forEach(x => {
        const on = x === t;
        x.setAttribute('aria-selected', on); x.tabIndex = on ? 0 : -1;
        const p = document.getElementById(x.getAttribute('aria-controls')); p.hidden = !on;
        if (on) loadMap($('[data-map-src]', p));
      });
    };
    tabs.forEach((t, i) => {
      t.addEventListener('click', () => select(t));
      t.addEventListener('keydown', e => {
        let n = null;
        if (e.key === 'ArrowRight') n = tabs[(i + 1) % tabs.length];
        if (e.key === 'ArrowLeft') n = tabs[(i - 1 + tabs.length) % tabs.length];
        if (e.key === 'Home') n = tabs[0];
        if (e.key === 'End') n = tabs[tabs.length - 1];
        if (n) { e.preventDefault(); n.focus(); select(n); }
      });
    });
  });

  /* Services scroll-spy */
  const spyLinks = $$('[data-spy]');
  if (spyLinks.length && 'IntersectionObserver' in window) {
    const so = new IntersectionObserver(es => es.forEach(en => {
      if (en.isIntersecting) spyLinks.forEach(a => a.classList.toggle('is-active', a.dataset.spy === en.target.id));
    }), { rootMargin: '-40% 0px -55% 0px' });
    spyLinks.forEach(a => { const s = document.getElementById(a.dataset.spy); s && so.observe(s); });
  }

  /* Char counter */
  $$('[data-counter]').forEach(t => {
    const out = t.closest('.field').querySelector('[data-count-out]'), max = t.maxLength;
    const upd = () => out && (out.textContent = `${t.value.length} / ${max}`);
    t.addEventListener('input', upd); upd();
  });

  /* Newsletter AJAX */
  $$('[data-ajax-form]').forEach(f => f.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = $('.form-msg', f), btn = $('button', f);
    btn.disabled = true; msg.className = 'form-msg'; msg.textContent = 'Subscribing…';
    try {
      const r = await fetch(f.action, { method: 'POST', body: new FormData(f), headers: { 'X-Requested-With': 'fetch' } });
      const j = await r.json();
      msg.textContent = j.message; msg.classList.add(j.ok ? 'is-ok' : 'is-err');
      if (j.ok) f.reset();
    } catch (_) { msg.textContent = 'Something went wrong. Please try again.'; msg.classList.add('is-err'); }
    btn.disabled = false;
  }));

  /* Appointment wizard */
  const wiz = $('[data-wizard]');
  if (wiz) {
    const steps = $$('[data-step]', wiz), inds = $$('[data-step-ind]', wiz);
    let cur = window.__wizardStart || 1;
    const show = (n, focus = true) => {
      cur = n;
      steps.forEach(s => s.classList.toggle('is-active', +s.dataset.step === n));
      inds.forEach(i => { const k = +i.dataset.stepInd; i.classList.toggle('is-active', k === n); i.classList.toggle('is-done', k < n); });
      const top = wiz.getBoundingClientRect().top + scrollY - 100;
      if (focus && scrollY > top) scrollTo({ top, behavior: reduce ? 'auto' : 'smooth' });
      const first = $('input:not([type=hidden]):not(.hp),select,textarea', steps[n - 1]);
      focus && first && setTimeout(() => first.focus({ preventScroll: true }), 60);
    };
    const showErr = (field, text) => {
      let p = $('.field__err', field);
      if (!p) { p = document.createElement('p'); p.className = 'field__err'; field.appendChild(p); }
      p.textContent = text; field.classList.add('has-error');
    };
    const clearErr = field => { const p = $('.field__err', field); p && p.remove(); field.classList.remove('has-error'); const i = $('[aria-invalid]', field); i && i.removeAttribute('aria-invalid'); };
    const validate = n => {
      let ok = true;
      $$('.field', steps[n - 1]).forEach(field => {
        clearErr(field);
        const radios = $$('input[type=radio][required]', field);
        if (radios.length && !radios.some(r => { const g = $$(`input[name="${r.name}"]`, wiz); return g.some(x => x.checked); })) {
          ok = false; showErr(field, 'Please make a selection.'); return;
        }
        const inp = $('input:not([type=radio]):not([type=checkbox]),textarea', field);
        if (inp && !inp.checkValidity()) {
          ok = false; inp.setAttribute('aria-invalid', 'true');
          showErr(field, inp.type === 'email' ? 'Please enter a valid email address.' : inp.type === 'date' ? 'Please choose a valid date.' : 'This field is required.');
        }
        if (inp && inp.type === 'date' && inp.value && new Date(inp.value + 'T12:00').getDay() === 0) {
          ok = false; inp.setAttribute('aria-invalid', 'true'); showErr(field, 'Our clinics are closed on Sundays. Please choose another day.');
        }
        if (inp && inp.type === 'tel' && inp.value.replace(/\D/g, '').length < 10) {
          ok = false; inp.setAttribute('aria-invalid', 'true'); showErr(field, 'Please enter a valid phone number.');
        }
      });
      if (n === 3) {
        const c = $('input[name=consent]', wiz), lbl = c.closest('.check');
        let p = lbl.nextElementSibling && lbl.nextElementSibling.classList.contains('field__err') ? lbl.nextElementSibling : null;
        if (!c.checked) { ok = false; if (!p) { p = document.createElement('p'); p.className = 'field__err'; lbl.after(p); } p.textContent = 'Please confirm so we can contact you.'; }
        else p && p.remove();
      }
      if (!ok) { const e = $('.has-error,[aria-invalid="true"]', steps[n - 1]); e && e.scrollIntoView({ block: 'center', behavior: reduce ? 'auto' : 'smooth' }); }
      return ok;
    };
    $$('[data-next]', wiz).forEach(b => b.addEventListener('click', () => validate(cur) && show(cur + 1)));
    $$('[data-prev]', wiz).forEach(b => b.addEventListener('click', () => show(cur - 1)));
    $$('input', wiz).forEach(i => i.addEventListener('change', () => { const f = i.closest('.field'); f && f.classList.contains('has-error') && clearErr(f); }));
    wiz.addEventListener('keydown', e => { if (e.key === 'Enter' && e.target.tagName === 'INPUT' && cur < 3) { e.preventDefault(); validate(cur) && show(cur + 1); } });
    wiz.addEventListener('submit', e => {
      if (!validate(3)) { e.preventDefault(); return; }
      const b = $('button[type=submit]', wiz); b.disabled = true; b.textContent = 'Sending…';
    });
    show(cur, false);
  }

  /* Hero Slider Interaction */
  const slider = $('[data-hero-slider]');
  if (slider) {
    const track = $('[data-hero-slides]', slider);
    const slides = $$('[data-hero-slide]', track);
    const dots = $$('[data-hero-dot]', slider);
    const prevBtn = $('[data-hero-prev]', slider);
    const nextBtn = $('[data-hero-next]', slider);
    let current = 0;
    let timer = null;

    const update = index => {
      current = (index + slides.length) % slides.length;
      track.style.transform = `translateX(-${current * 100}%)`;
      slides.forEach((s, i) => s.classList.toggle('is-active', i === current));
      dots.forEach((d, i) => d.classList.toggle('is-active', i === current));
    };

    const next = () => update(current + 1);
    const prev = () => update(current - 1);

    const startTimer = () => {
      stopTimer();
      timer = setInterval(next, 6000);
    };
    const stopTimer = () => {
      if (timer) { clearInterval(timer); timer = null; }
    };

    nextBtn && nextBtn.addEventListener('click', () => { next(); startTimer(); });
    prevBtn && prevBtn.addEventListener('click', () => { prev(); startTimer(); });

    dots.forEach((d, idx) => d.addEventListener('click', () => {
      update(idx);
      startTimer();
    }));

    slider.addEventListener('mouseenter', stopTimer);
    slider.addEventListener('mouseleave', startTimer);
    slider.addEventListener('touchstart', stopTimer, { passive: true });
    slider.addEventListener('touchend', startTimer, { passive: true });

    update(0);
    startTimer();
  }

})();
