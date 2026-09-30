document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('[data-menu-toggle]');
  const sidebar = document.querySelector('#appSidebar');
  const backdrop = document.querySelector('[data-menu-close]');
  const setMenu = (open) => {
    document.body.classList.toggle('menu-open', open);
    toggle?.setAttribute('aria-expanded', String(open));
  };
  toggle?.addEventListener('click', () => setMenu(!document.body.classList.contains('menu-open')));
  backdrop?.addEventListener('click', () => setMenu(false));
  sidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setMenu(false)));
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape') setMenu(false); });
  document.querySelectorAll('[data-scroll-to]').forEach((button) => button.addEventListener('click', () => document.querySelector(`#${button.dataset.scrollTo}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' })));
  document.querySelectorAll('[data-confirm]').forEach((form) => form.addEventListener('submit', (event) => { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); }));
});
