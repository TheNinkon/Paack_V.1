document.addEventListener('DOMContentLoaded', () => {
  const offcanvasElement = document.getElementById('offcanvasClient');
  const filterStatus = document.getElementById('filter-status');
  const tableBody = document.querySelector('#clients-table tbody');

  const adminToggle = document.getElementById('create_admin');
  const adminFields = document.getElementById('admin-fields');

  const updateAdminFieldsVisibility = () => {
    if (!adminToggle || !adminFields) {
      return;
    }

    adminFields.classList.toggle('d-none', !adminToggle.checked);
  };

  if (adminToggle) {
    adminToggle.addEventListener('change', updateAdminFieldsVisibility);
    updateAdminFieldsVisibility();
  }

  if (offcanvasElement) {
    offcanvasElement.addEventListener('show.bs.offcanvas', () => {
      window.setTimeout(() => {
        const firstInput = offcanvasElement.querySelector('input:not([type="hidden"]), select, textarea');
        if (firstInput instanceof HTMLElement) {
          firstInput.focus();
        }
      }, 200);

      updateAdminFieldsVisibility();
    });

    offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
      const form = offcanvasElement.querySelector('form');
      if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid').forEach((element) => element.classList.remove('is-invalid'));
      }

      if (adminToggle) {
        adminToggle.checked = false;
        updateAdminFieldsVisibility();
      }
    });

    if (offcanvasElement.dataset.autoShow === 'true' && window.bootstrap) {
      window.bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement).show();
    }
  }

  if (filterStatus && tableBody) {
    filterStatus.addEventListener('change', () => {
      const value = filterStatus.value;
      tableBody.querySelectorAll('tr').forEach((row) => {
        const status = row.getAttribute('data-status');
        if (!status) {
          return;
        }

        if (!value) {
          row.classList.remove('d-none');
          return;
        }

        row.classList.toggle('d-none', status !== value);
      });
    });
  }
});
