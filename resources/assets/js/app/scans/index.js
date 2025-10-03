document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('code');
  const form = document.getElementById('scan-form');
  const feedbackBanner = document.querySelector('[data-scan-feedback]');
  const beepSuccess = new Audio('/assets/audio/scan-success.wav');
  const beepError = new Audio('/assets/audio/scan-error.wav');

  const focusInput = () => {
    if (!input) return;
    window.setTimeout(() => {
      input.focus({ preventScroll: true });
      input.select();
    }, 50);
  };

  if (input) {
    focusInput();
    input.addEventListener('blur', focusInput);
  }

  let submitButton;
  let submitOriginalLabel;
  const feedbackContainer = document.getElementById('scan-feedback-container');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  if (form) {
    submitButton = form.querySelector('button[type="submit"]');
    submitOriginalLabel = submitButton?.innerHTML;

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const formData = new FormData(form);
      const codeValue = formData.get('code');

      if (!codeValue) {
        return;
      }

      if (submitButton) {
        submitButton.disabled = true;
        submitButton.classList.add('disabled');
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + (window.translations?.scanning ?? 'Escaneando...');
      }

      fetch(form.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken ?? '',
        },
        body: formData,
      })
        .then(async (response) => {
          if (!response.ok) {
            const errorText = await response.text();
            throw new Error(errorText || 'Scan request failed');
          }
          return response.json();
        })
        .then((data) => {
          playFeedback(data.feedback.status);
          renderFeedback(data.feedback, data.alerts ?? []);
          prependScanRow(data.scan, data.feedback);
          focusInput();
          form.reset();
        })
        .catch((error) => {
          renderError(error);
        })
        .finally(() => {
          if (submitButton && submitOriginalLabel) {
            submitButton.disabled = false;
            submitButton.classList.remove('disabled');
            submitButton.innerHTML = submitOriginalLabel;
          }
        });
    });
  }

  const playFeedback = (status) => {
    if (status === 'matched') {
      beepSuccess.currentTime = 0;
      void beepSuccess.play();
    } else if (status === 'unmatched') {
      beepError.currentTime = 0;
      void beepError.play();
    }
  };

  if (feedbackBanner) {
    const status = feedbackBanner.getAttribute('data-scan-feedback');
    playFeedback(status);
    highlightInput(status);
  }

  function highlightInput(status) {
    if (!input) return;
    input.classList.remove('is-valid', 'is-invalid');
    if (status === 'matched') {
      input.classList.add('is-valid');
    } else if (status === 'unmatched') {
      input.classList.add('is-invalid');
    }

    window.setTimeout(() => {
      input.classList.remove('is-valid', 'is-invalid');
    }, 2000);
  }

  function renderFeedback(feedback, alerts) {
    if (!feedbackContainer) return;

    const alertClass = feedback.status === 'matched' ? 'success' : 'warning';
    const icon = feedback.status === 'matched' ? 'ti tabler-checkbox' : 'ti tabler-alert-triangle';

    const detailLines = [];
    if (feedback.status === 'matched') {
      detailLines.push(`<span class="d-block">${window.translations?.scanProvider ?? 'Proveedor detectado'}: ${feedback.provider_name ?? '—'}</span>`);
      if (feedback.pattern_label) {
        detailLines.push(`<small class="text-muted">${window.translations?.scanPattern ?? 'Patrón'}: ${feedback.pattern_label}</small>`);
      }
    } else {
      detailLines.push(`<span class="d-block">${window.translations?.scanNoPattern ?? 'No se encontró un patrón asociado.'}</span>`);
    }

    const alertsHtml = (alerts ?? [])
      .map((alert) => `<div class="alert alert-${alert.type} mt-3 mb-0 py-2">${alert.message}</div>`)
      .join('');

    feedbackContainer.innerHTML = `
      <div class="alert alert-${alertClass} alert-dismissible" role="alert" data-scan-feedback="${feedback.status}">
        <div class="d-flex">
          <i class="${icon} me-2 mt-1"></i>
          <div>
            <strong>${feedback.code}</strong>
            ${detailLines.join('')}
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        ${alertsHtml}
      </div>
    `;

    highlightInput(feedback.status);
  }

  function renderError(error) {
    if (!feedbackContainer) return;

    feedbackContainer.innerHTML = `
      <div class="alert alert-danger alert-dismissible" role="alert">
        <div class="d-flex">
          <i class="ti tabler-alert-triangle me-2 mt-1"></i>
          <div>
            <strong>${window.translations?.scanError ?? 'Error al registrar el escaneo'}</strong>
            <span class="d-block small">${error?.message ?? error ?? 'Unknown error'}</span>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;

    playFeedback('unmatched');
    highlightInput('unmatched');
  }

  function prependScanRow(scan, feedback) {
    const table = document.querySelector('#parcels-table') ? null : document.querySelector('#scans-table');
    const recentTable = document.querySelector('#scans-table');

    if (!recentTable) {
      refreshPage();
      return;
    }

    const tbody = recentTable.querySelector('tbody');
    if (!tbody) {
      refreshPage();
      return;
    }

    const row = document.createElement('tr');
    row.className = 'table-active';

    row.innerHTML = `
      <td class="fw-medium">
        ${scan.code}
        <div class="text-muted small">
          ${scan.creator ? window.translations?.scanBy?.replace(':user', scan.creator.name) ?? `Por ${scan.creator.name}` : ''}
        </div>
        <div>
          <a class="btn btn-sm btn-label-primary mt-2" href="${routes.parcelHistory.replace('__CODE__', scan.code)}">
            <i class="ti tabler-clock me-1"></i>${window.translations?.history ?? 'Historial'}
          </a>
        </div>
      </td>
      <td>${scan.provider?.name ?? window.translations?.scanProviderUnknown ?? 'No detectado'}</td>
      <td>${scan.provider_barcode?.label ?? '—'}</td>
      <td>
        <span class="badge ${scan.is_valid ? 'bg-label-success' : 'bg-label-warning'}">
          ${scan.is_valid ? window.translations?.valid ?? 'Válido' : window.translations?.noMatch ?? 'Sin coincidencia'}
        </span>
      </td>
      <td><small class="text-muted">${window.translations?.justNow ?? 'Hace un momento'}</small></td>
    `;

    tbody.insertBefore(row, tbody.firstElementChild ?? null);

    while (tbody.children.length > 50) {
      tbody.removeChild(tbody.lastElementChild);
    }
  }

  function refreshPage() {
    window.location.reload();
  }
});
