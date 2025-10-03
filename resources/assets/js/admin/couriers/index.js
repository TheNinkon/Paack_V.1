document.addEventListener('DOMContentLoaded', () => {
  const statusFilter = document.getElementById('couriers-filter-status');
  const vehicleFilter = document.getElementById('couriers-filter-vehicle');
  const zoneFilter = document.getElementById('couriers-filter-zone');
  const tableBody = document.querySelector('#couriers-table tbody');

  const applyFilters = () => {
    if (!tableBody) return;

    const statusValue = statusFilter ? statusFilter.value : '';
    const vehicleValue = vehicleFilter ? vehicleFilter.value : '';
    const zoneValue = zoneFilter ? zoneFilter.value : '';

    tableBody.querySelectorAll('tr').forEach((row) => {
      const rowStatus = row.getAttribute('data-status');
      const rowVehicle = row.getAttribute('data-vehicle');
      const rowZone = row.getAttribute('data-zone');

      const matchesStatus = !statusValue || statusValue === rowStatus;
      const matchesVehicle = !vehicleValue || vehicleValue === rowVehicle;
      const matchesZone = !zoneValue || zoneValue === rowZone;

      if (matchesStatus && matchesVehicle && matchesZone) {
        row.classList.remove('d-none');
      } else {
        row.classList.add('d-none');
      }
    });
  };

  if (statusFilter) {
    statusFilter.addEventListener('change', applyFilters);
  }

  if (vehicleFilter) {
    vehicleFilter.addEventListener('change', applyFilters);
  }

  if (zoneFilter) {
    zoneFilter.addEventListener('change', applyFilters);
  }

  applyFilters();

  const attachClientBindings = (form) => {
    if (!form) return;

    if (form.dataset.clientBindings === 'true') {
      return;
    }

    const clientSelect = form.querySelector('#client_id');
    const userSelect = form.querySelector('#user_id');
    const zoneSelect = form.querySelector('#zone_id');

    if (!userSelect && !zoneSelect) {
      return;
    }

    form.dataset.clientBindings = 'true';

    let usersMap = {};
    let zonesMap = {};

    try {
      usersMap = JSON.parse(userSelect?.dataset.usersMap || '{}');
    } catch (error) {
      usersMap = {};
    }

    try {
      zonesMap = JSON.parse(zoneSelect?.dataset.zonesMap || '{}');
    } catch (error) {
      zonesMap = {};
    }

    const renderUserOptions = (clientId) => {
      if (!userSelect) return;

      if (!clientId) {
        clientId = userSelect.dataset.defaultClientId || null;
      }

      const users = Array.isArray(usersMap[clientId]) ? usersMap[clientId] : [];
      const selectedValue = userSelect.value;

      userSelect.innerHTML = '';

      const placeholderOption = document.createElement('option');
      placeholderOption.value = '';
      placeholderOption.textContent = userSelect.dataset.placeholder || 'Selecciona un usuario';
      userSelect.appendChild(placeholderOption);

      users.forEach((user) => {
        const option = document.createElement('option');
        option.value = String(user.id);
        option.textContent = `${user.name} — ${user.email}`;
        userSelect.appendChild(option);
      });

      if (selectedValue && Array.from(userSelect.options).some((option) => option.value === selectedValue)) {
        userSelect.value = selectedValue;
      }
    };

    const renderZoneOptions = (clientId) => {
      if (!zoneSelect) return;

      if (!clientId) {
        clientId = zoneSelect.dataset.defaultClientId || null;
      }

      const zones = Array.isArray(zonesMap[clientId]) ? zonesMap[clientId] : [];
      const selectedValue = zoneSelect.value;

      zoneSelect.innerHTML = '';

      const placeholderOption = document.createElement('option');
      placeholderOption.value = '';
      placeholderOption.textContent = zoneSelect.dataset.placeholder || 'Selecciona una zona (opcional)';
      zoneSelect.appendChild(placeholderOption);

      zones.forEach((zone) => {
        const option = document.createElement('option');
        option.value = String(zone.id);
        option.textContent = zone.name;
        zoneSelect.appendChild(option);
      });

      if (selectedValue && Array.from(zoneSelect.options).some((option) => option.value === selectedValue)) {
        zoneSelect.value = selectedValue;
      }
    };

    if (userSelect) {
      userSelect.dataset.placeholder = userSelect.options[0]?.textContent || '';
    }

    if (zoneSelect) {
      zoneSelect.dataset.placeholder = zoneSelect.options[0]?.textContent || '';
    }

    const initialClientId = clientSelect ? clientSelect.value : (userSelect?.dataset.defaultClientId || zoneSelect?.dataset.defaultClientId);
    renderUserOptions(initialClientId);
    renderZoneOptions(initialClientId);

    if (clientSelect) {
      clientSelect.addEventListener('change', () => {
        renderUserOptions(clientSelect.value);
        renderZoneOptions(clientSelect.value);
      });
    }
  };

  const offcanvasElement = document.getElementById('offcanvasCourier');

  if (offcanvasElement) {
    const form = offcanvasElement.querySelector('form');
    attachClientBindings(form);

    offcanvasElement.addEventListener('show.bs.offcanvas', () => {
      window.setTimeout(() => {
        const firstInput = offcanvasElement.querySelector('input:not([type="hidden"]), select, textarea');
        if (firstInput instanceof HTMLElement) {
          firstInput.focus();
        }
      }, 200);
    });

    offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
      if (!form) return;

      form.reset();
      form.querySelectorAll('.is-invalid').forEach((element) => element.classList.remove('is-invalid'));
      form.dataset.clientBindings = 'false';
      attachClientBindings(form);
    });

    if (offcanvasElement.dataset.autoShow === 'true') {
      const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement);
      offcanvas.show();
    }
  }

  document.querySelectorAll('form').forEach((form) => {
    if (form.closest('#offcanvasCourier')) {
      return;
    }

    attachClientBindings(form);
  });
});
