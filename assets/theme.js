// Light and dark: follows the Mac's setting until the visitor picks one with a [data-theme-toggle] button.
// The choice stays in this browser (localStorage), is never sent anywhere, and the page always carries the
// look it's showing as <html data-theme="light|dark">, so pages can draw Bondi in either.
// Loaded in <head> without defer, so the page never flashes the wrong colours.
(() => {
  const key = 'bondi-theme';
  const system = matchMedia('(prefers-color-scheme: dark)');
  let memory = null; // used when the browser blocks storage: the choice lasts for this page only
  const saved = () => { try { return localStorage.getItem(key); } catch (e) { return memory; } };
  const current = () => saved() || (system.matches ? 'dark' : 'light');
  const label = theme => (theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');

  const apply = () => {
    const theme = current();
    document.documentElement.dataset.theme = theme;
    document.querySelectorAll('[data-theme-toggle]').forEach(button => {
      button.setAttribute('aria-label', label(theme));
      button.title = label(theme);
      button.setAttribute('aria-pressed', String(theme === 'dark'));
    });
    document.dispatchEvent(new CustomEvent('bondi-theme', { detail: theme }));
  };

  apply();
  system.addEventListener('change', () => { if (!saved()) apply(); });
  addEventListener('storage', event => { if (event.key === key) apply(); }); // another tab changed it
  document.addEventListener('DOMContentLoaded', apply);
  document.addEventListener('click', event => {
    if (!event.target.closest('[data-theme-toggle]')) return;
    const next = current() === 'dark' ? 'light' : 'dark';
    const follow = next === (system.matches ? 'dark' : 'light'); // picking the Mac's own look goes back to following it
    try {
      if (follow) localStorage.removeItem(key); else localStorage.setItem(key, next);
    } catch (e) {
      memory = follow ? null : next;
    }
    apply();
  });
})();
