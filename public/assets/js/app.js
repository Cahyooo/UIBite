window.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) window.lucide.createIcons();

  document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('click', (event) => {
      if (!window.confirm(element.dataset.confirm || 'Lanjutkan tindakan ini?')) {
        event.preventDefault();
      }
    });
  });

  document.querySelectorAll('[data-remove-cart-item]').forEach((button) => {
    button.addEventListener('click', () => {
      const row = button.closest('[data-cart-row]');
      const quantity = row?.querySelector('input[type="number"]');
      const form = row?.closest('form');
      if (quantity && form) {
        quantity.value = '0';
        form.requestSubmit();
      }
    });
  });

  document.querySelectorAll('[data-auto-submit]').forEach((element) => {
    element.addEventListener('change', () => element.form?.submit());
  });
});

function toggleSellerSidebar(open) {
  const sidebar = document.getElementById('seller-sidebar');
  const overlay = document.getElementById('seller-sidebar-overlay');
  if (!sidebar || !overlay) return;
  sidebar.classList.toggle('-translate-x-full', !open);
  overlay.classList.toggle('hidden', !open);
}
