document.addEventListener('DOMContentLoaded', () => {
  const filter = document.getElementById('zones-filter-status');
  const tableBody = document.querySelector('#zones-table tbody');

  if (filter && tableBody) {
    filter.addEventListener('change', () => {
      const status = filter.value;

      tableBody.querySelectorAll('tr').forEach((row) => {
        const rowStatus = row.getAttribute('data-status');

        if (!status || status === rowStatus) {
          row.classList.remove('d-none');
        } else {
          row.classList.add('d-none');
        }
      });
    });
  }

  const offcanvasElement = document.getElementById('offcanvasZone');

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
        form.querySelectorAll('.is-invalid').forEach((element) => element.classList.remove('is-invalid'));
      }
    });

    if (offcanvasElement.dataset.autoShow === 'true') {
      const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement);
      offcanvas.show();
    }
  }
});
