/**
 * IN LOVING MEMORY — Floating Petal Effect
 * Gentle, non-intrusive ambient animation
 */

(function() {
  'use strict';

  const petals = ['🌸', '🌺', '🌹', '🌷', '✿', '❀'];
  const container = document.getElementById('petalContainer');
  if (!container) return;

  // Respect reduced motion
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  let active = 0;
  const MAX  = 8;

  function spawnPetal() {
    if (active >= MAX || document.hidden) return;

    const el = document.createElement('span');
    el.className = 'petal';
    el.textContent = petals[Math.floor(Math.random() * petals.length)];

    const size      = 0.8 + Math.random() * 0.8;
    const left      = Math.random() * 100;
    const duration  = 8 + Math.random() * 10;
    const delay     = Math.random() * 2;
    const drift     = (Math.random() - 0.5) * 120;
    const spin      = (Math.random() > 0.5 ? 1 : -1) * (180 + Math.random() * 360);
    const ease      = 'ease-in-out';

    el.style.cssText = `
      --size: ${size}rem;
      --duration: ${duration}s;
      --delay: ${delay}s;
      --ease: ${ease};
      --left: ${left}%;
      --drift: ${drift}px;
      --spin: ${spin}deg;
      left: ${left}%;
    `;

    container.appendChild(el);
    active++;

    el.addEventListener('animationend', () => {
      el.remove();
      active--;
    });
  }

  // Spawn petals at intervals
  const interval = setInterval(spawnPetal, 2500);

  // Spawn a few immediately on load
  setTimeout(spawnPetal, 500);
  setTimeout(spawnPetal, 1500);
  setTimeout(spawnPetal, 2500);

  // Pause when tab is hidden
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      clearInterval(interval);
    }
  });

})();
