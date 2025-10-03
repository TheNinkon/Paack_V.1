document.addEventListener('DOMContentLoaded', () => {
  const filter = document.getElementById('users-filter-status');
  const table = document.querySelector('#users-table tbody');

  if (filter && table) {
    filter.addEventListener('change', () => {
      const status = filter.value;

      table.querySelectorAll('tr').forEach((row) => {
        const rowStatus = row.getAttribute('data-status');

        if (!status || status === rowStatus) {
          row.classList.remove('d-none');
        } else {
          row.classList.add('d-none');
        }
      });
    });
  }

  const offcanvasElement = document.getElementById('offcanvasUser');

  if (offcanvasElement) {
    offcanvasElement.addEventListener('show.bs.offcanvas', () => {
      window.setTimeout(() => {
        const firstInput = offcanvasElement.querySelector('input:not([type="hidden"]), select, textarea');
        if (firstInput instanceof HTMLElement) {
          firstInput.focus();
        }
      }, 200);
    });

    offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
      const form = offcanvasElement.querySelector('form');
      if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
      }
    });

    if (offcanvasElement.dataset.autoShow === 'true') {
      const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement);
      offcanvas.show();
    }
  }
});
