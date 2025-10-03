document.addEventListener('DOMContentLoaded', () => {
  const filterSelect = document.getElementById('providers-filter-status');
  const tableBody = document.querySelector('#providers-table tbody');

  if (!filterSelect || !tableBody) {
    return;
  }

  filterSelect.addEventListener('change', () => {
    const status = filterSelect.value;

    tableBody.querySelectorAll('tr').forEach((row) => {
      const rowStatus = row.getAttribute('data-status');

      if (!status || status === rowStatus) {
        row.classList.remove('d-none');
      } else {
        row.classList.add('d-none');
      }
    });
  });
});
