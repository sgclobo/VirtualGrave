/**
 * IN LOVING MEMORY — Main JavaScript
 * Handles: navigation, ambient music, scroll effects, modals, forms
 */

'use strict';

// ─── NAMESPACE ────────────────────────────────────────────
const Memorial = {

  // ─── INIT ──────────────────────────────────────────────
  init() {
    this.nav();
    this.music();
    this.scrollReveal();
    this.flashAutoHide();
    this.statsCounter();
    this.graveInteractions();
  },

  // ─── NAVIGATION ────────────────────────────────────────
  nav() {
    const mainNav  = document.getElementById('mainNav');
    const toggle   = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');

    // Scroll effect
    if (mainNav) {
      window.addEventListener('scroll', () => {
        mainNav.classList.toggle('scrolled', window.scrollY > 60);
      }, { passive: true });
    }

    // Hamburger
    if (toggle && navLinks) {
      toggle.addEventListener('click', () => {
        const open = navLinks.classList.toggle('open');
        toggle.setAttribute('aria-expanded', open);
        toggle.querySelectorAll('span').forEach((s, i) => {
          if (open) {
            if (i === 0) s.style.transform = 'rotate(45deg) translate(5px, 5px)';
            if (i === 1) s.style.opacity = '0';
            if (i === 2) s.style.transform = 'rotate(-45deg) translate(5px, -5px)';
          } else {
            s.style.transform = '';
            s.style.opacity = '';
          }
        });
      });

      // Close on outside click
      document.addEventListener('click', e => {
        if (!mainNav?.contains(e.target)) {
          navLinks.classList.remove('open');
          toggle.setAttribute('aria-expanded', 'false');
        }
      });
    }
  },

  // ─── AMBIENT MUSIC ─────────────────────────────────────
  music() {
    const audio  = document.getElementById('ambientAudio');
    const btn    = document.getElementById('musicToggle');
    if (!audio || !btn) return;

    let playing = false;

    btn.addEventListener('click', async () => {
      try {
        if (playing) {
          audio.pause();
          btn.classList.remove('playing');
          btn.title = 'Enable ambient music';
        } else {
          await audio.play();
          btn.classList.add('playing');
          btn.title = 'Disable ambient music';
        }
        playing = !playing;
      } catch (e) {
        // Autoplay policy; user must interact first — already handled
        console.warn('Audio play failed:', e.message);
      }
    });
  },

  // ─── SCROLL REVEAL ─────────────────────────────────────
  scrollReveal() {
    const els = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
    if (!els.length) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    els.forEach(el => observer.observe(el));
  },

  // ─── FLASH AUTO-HIDE ───────────────────────────────────
  flashAutoHide() {
    const flash = document.getElementById('flashMsg');
    if (flash) {
      setTimeout(() => {
        flash.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        flash.style.opacity = '0';
        flash.style.transform = 'translateX(20px)';
        setTimeout(() => flash.remove(), 500);
      }, 5000);
    }
  },

  // ─── STATS COUNTER ANIMATION ───────────────────────────
  statsCounter() {
    const counters = document.querySelectorAll('[data-count]');
    if (!counters.length) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el     = entry.target;
        const target = parseInt(el.dataset.count, 10);
        const dur    = 1500;
        const step   = target / (dur / 16);
        let current  = 0;

        const tick = () => {
          current = Math.min(current + step, target);
          el.textContent = Math.floor(current).toLocaleString();
          if (current < target) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
        observer.unobserve(el);
      });
    }, { threshold: 0.5 });

    counters.forEach(el => observer.observe(el));
  },

  // ─── GRAVE INTERACTIONS ────────────────────────────────
  graveInteractions() {
    // Flowers & candles are managed in memorial.php's inline JS
    // This handles the visual feedback bursts
  },
};

// ─── AJAX HELPER ──────────────────────────────────────────
async function memorialPost(url, data) {
  const csrf = document.querySelector('meta[name="csrf"]')?.content
             || document.querySelector('input[name="csrf_token"]')?.value
             || '';
  const body = new FormData();
  Object.entries(data).forEach(([k, v]) => body.append(k, v));
  body.append('csrf_token', csrf);

  const res = await fetch(url, { method: 'POST', body });
  return res.json();
}

// ─── SUCCESS BURST ANIMATION ───────────────────────────────
function showBurst(emoji, x, y) {
  const el = document.createElement('div');
  el.className = 'success-burst';
  el.textContent = emoji;
  el.style.left = (x - 20) + 'px';
  el.style.top  = (y - 20) + 'px';
  document.body.appendChild(el);
  el.addEventListener('animationend', () => el.remove());
}

// ─── CANDLE / FLOWER SELECTION ────────────────────────────
function initSelector(containerClass, hiddenId) {
  document.querySelectorAll('.' + containerClass).forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.' + containerClass).forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      const input = document.getElementById(hiddenId);
      if (input) input.value = card.dataset.id;
    });
  });
}

// ─── FLOWER MODAL FORM ─────────────────────────────────────
function initFlowerForm() {
  initSelector('flower-option', 'selectedFlowerId');

  const form = document.getElementById('flowerForm');
  if (!form) return;

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = form.querySelector('[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Depositing…';

    const data = {
      flower_id: document.getElementById('selectedFlowerId').value,
      message:   document.getElementById('flowerMessage').value,
    };

    if (!data.flower_id) {
      showAlert('Please select a flower.', 'warning');
      btn.disabled = false;
      btn.textContent = 'Deposit Flowers';
      return;
    }

    try {
      const res = await memorialPost(SITE_URL + '/api/deposit_flower.php', data);
      if (res.success) {
        showAlert('🌹 Your flowers have been placed with love.', 'success');
        bootstrap.Modal.getInstance(document.getElementById('flowerModal'))?.hide();
        // Add flower visually
        addGraveFlower(res.flower_emoji, res.flower_name, res.user_name);
        updateCounter('flowersCount', 1);
        form.reset();
        document.querySelectorAll('.flower-option').forEach(c => c.classList.remove('selected'));
      } else {
        showAlert(res.message, 'error');
      }
    } catch (err) {
      showAlert('Something went wrong. Please try again.', 'error');
    }
    btn.disabled = false;
    btn.textContent = 'Deposit Flowers';
  });
}

// ─── CANDLE MODAL FORM ─────────────────────────────────────
function initCandleForm() {
  initSelector('candle-option', 'selectedCandleId');

  const form = document.getElementById('candleForm');
  if (!form) return;

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = form.querySelector('[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Lighting…';

    const data = {
      candle_id:   document.getElementById('selectedCandleId').value,
      dedication:  document.getElementById('candleDedication').value,
    };

    if (!data.candle_id) {
      showAlert('Please select a candle.', 'warning');
      btn.disabled = false;
      btn.textContent = 'Light Candle';
      return;
    }

    try {
      const res = await memorialPost(SITE_URL + '/api/light_candle.php', data);
      if (res.success) {
        showAlert('🕯 Your candle glows in loving memory.', 'success');
        bootstrap.Modal.getInstance(document.getElementById('candleModal'))?.hide();
        addGraveCandle(res.candle_name, res.glow_color);
        updateCounter('candlesCount', 1);
        form.reset();
        document.querySelectorAll('.candle-option').forEach(c => c.classList.remove('selected'));
      } else {
        showAlert(res.message, 'error');
      }
    } catch (err) {
      showAlert('Something went wrong. Please try again.', 'error');
    }
    btn.disabled = false;
    btn.textContent = 'Light Candle';
  });
}

// ─── PRAYER FORM ───────────────────────────────────────────
function initPrayerForm() {
  const form = document.getElementById('prayerForm');
  if (!form) return;

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = form.querySelector('[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Sending…';

    const data = {
      title:       document.getElementById('prayerTitle').value,
      prayer_text: document.getElementById('prayerText').value,
      category:    document.getElementById('prayerCategory').value,
      visibility:  document.getElementById('prayerVisibility').value,
    };

    try {
      const res = await memorialPost(SITE_URL + '/api/submit_prayer.php', data);
      if (res.success) {
        showAlert('🙏 Your prayer has been received.', 'success');
        bootstrap.Modal.getInstance(document.getElementById('prayerModal'))?.hide();
        updateCounter('prayersCount', 1);
        form.reset();
      } else {
        showAlert(res.message, 'error');
      }
    } catch (err) {
      showAlert('Something went wrong. Please try again.', 'error');
    }
    btn.disabled = false;
    btn.textContent = 'Send Prayer';
  });
}

// ─── GRAVE VISUAL HELPERS ──────────────────────────────────
function addGraveFlower(emoji, name, userName) {
  const area = document.getElementById('graveFlowersArea');
  if (!area) return;
  const el = document.createElement('span');
  el.className = 'grave-flower-item';
  el.innerHTML = `${emoji}<span class="flower-tooltip">${userName} left ${name}</span>`;
  area.appendChild(el);
}

function addGraveCandle(name, glowColor) {
  const area = document.getElementById('graveCandlesArea');
  if (!area) return;
  const el = document.createElement('div');
  el.className = 'grave-candle';
  el.style.setProperty('--glow', glowColor);
  el.innerHTML = `
    <div class="grave-candle-flame"></div>
    <div class="grave-candle-body" style="box-shadow: 0 0 12px ${glowColor}80;"></div>
    <span class="grave-candle-name">${name}</span>`;
  area.appendChild(el);
}

function updateCounter(id, delta) {
  const el = document.getElementById(id);
  if (!el) return;
  const current = parseInt(el.textContent.replace(/,/g,''), 10) || 0;
  el.textContent = (current + delta).toLocaleString();
  el.style.animation = 'countBounce 0.5s ease';
  el.addEventListener('animationend', () => el.style.animation = '', { once: true });
}

// ─── ALERT HELPER ──────────────────────────────────────────
function showAlert(msg, type = 'info') {
  const icons = { success: '✓', error: '✕', info: 'ℹ', warning: '⚠' };
  const container = document.querySelector('.flash-container');
  if (!container) return;
  const div = document.createElement('div');
  div.className = `flash-message flash-${type}`;
  div.innerHTML = `
    <span class="flash-icon">${icons[type] || 'ℹ'}</span>
    <span>${msg}</span>
    <button onclick="this.parentElement.remove()" class="flash-close">×</button>`;
  container.appendChild(div);
  setTimeout(() => {
    div.style.opacity = '0';
    div.style.transform = 'translateX(20px)';
    div.style.transition = 'all 0.4s ease';
    setTimeout(() => div.remove(), 400);
  }, 5000);
}

// ─── GALLERY LIGHTBOX ──────────────────────────────────────
function initLightbox() {
  const overlay = document.getElementById('lightboxOverlay');
  const img     = document.getElementById('lightboxImg');
  const cap     = document.getElementById('lightboxCaption');
  if (!overlay) return;

  document.querySelectorAll('[data-lightbox]').forEach(el => {
    el.addEventListener('click', () => {
      img.src = el.dataset.src || el.querySelector('img')?.src || '';
      img.alt = el.dataset.title || '';
      if (cap) cap.textContent = el.dataset.title || '';
      overlay.classList.add('open');
      document.body.style.overflow = 'hidden';
    });
  });

  overlay.addEventListener('click', e => {
    if (e.target === overlay || e.target.classList.contains('lightbox-close')) {
      overlay.classList.remove('open');
      document.body.style.overflow = '';
    }
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      overlay.classList.remove('open');
      document.body.style.overflow = '';
    }
  });
}

// ─── GALLERY FILTER ────────────────────────────────────────
function initGalleryFilter() {
  const btns  = document.querySelectorAll('.gallery-filter-btn');
  const items = document.querySelectorAll('.gallery-item');

  btns.forEach(btn => {
    btn.addEventListener('click', () => {
      btns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const cat = btn.dataset.filter;

      items.forEach(item => {
        if (cat === 'all' || item.dataset.category === cat) {
          item.style.display = '';
          item.style.animation = 'fadeIn 0.4s ease';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
}

// ─── TIMELINE ANIMATION ────────────────────────────────────
function initTimeline() {
  const items = document.querySelectorAll('.timeline-item');
  if (!items.length) return;

  const observer = new IntersectionObserver(entries => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => entry.target.classList.add('revealed'), i * 100);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.2 });

  items.forEach(item => {
    item.classList.add('reveal');
    observer.observe(item);
  });
}

// ─── REGISTRATION — Conditional Field ─────────────────────
function initRegForm() {
  const relField  = document.getElementById('relationship');
  const famGroup  = document.getElementById('familyDetailGroup');
  if (!relField || !famGroup) return;

  relField.addEventListener('change', () => {
    famGroup.style.display = relField.value === 'family' ? 'block' : 'none';
  });
}

// ─── DOM READY ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  Memorial.init();
  initFlowerForm();
  initCandleForm();
  initPrayerForm();
  initLightbox();
  initGalleryFilter();
  initTimeline();
  initRegForm();
});

// SITE_URL is set inline from PHP
