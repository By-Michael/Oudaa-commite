/**
 * Keeps a section of the page live without a manual reload, by
 * re-fetching the current URL on an interval and swapping in just the
 * HTML inside one container.
 *
 * Safety rules baked in so this can't fight the person while they work:
 *  - paused while the tab is hidden (no point burning requests)
 *  - paused while a protected form has anything typed into it, so an
 *    in-progress draft (a new expense, a new fund, etc.) can never get
 *    wiped out from under someone by a background refresh
 *  - skips the DOM swap entirely if the fetched HTML is identical, so
 *    open dropdowns / hover states inside the container aren't reset
 *    on every tick for no reason
 */
function initLivePoll(containerId, opts = {}) {
  const interval = opts.interval || 5000;
  const protectForm = opts.protectForm ? document.querySelector(opts.protectForm) : null;
  const container = document.getElementById(containerId);
  if (!container) return;

  let inFlight = false;

  function formIsDirty() {
    if (!protectForm) return false;
    return Array.from(protectForm.elements).some(el => {
      if (!('value' in el) || el.type === 'hidden' || el.type === 'submit' || el.type === 'button') return false;
      return el.value && el.value.trim() !== '';
    });
  }

  async function tick() {
    if (inFlight || document.hidden || formIsDirty()) return;
    inFlight = true;
    try {
      const res = await fetch(window.location.pathname + window.location.search, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (!res.ok) return;
      const html = await res.text();
      const fresh = new DOMParser().parseFromString(html, 'text/html').getElementById(containerId);
      if (fresh && fresh.innerHTML !== container.innerHTML) {
        container.innerHTML = fresh.innerHTML;
      }
    } catch (e) {
      // Network hiccup — just try again next tick.
    } finally {
      inFlight = false;
    }
  }

  tick();
  setInterval(tick, interval);

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') tick();
  });
}
