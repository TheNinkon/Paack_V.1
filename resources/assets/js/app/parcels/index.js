import $ from 'jquery';
import 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import 'datatables.net-buttons-bs5';
import flatpickr from 'flatpickr';
import 'select2';
import { Offcanvas, Modal } from 'bootstrap';
import JsBarcode from 'jsbarcode';

let googleMapsLoaderPromise = null;
let googleMapsLoaderKey = null;

const loadGoogleMapsSdk = (apiKey) => {
  if (!apiKey) {
    console.warn('[maps] missing api key');
    return Promise.reject(new Error('missing api key'));
  }

  if (window.google?.maps?.places) {
    console.debug('[maps] SDK already available');
    return Promise.resolve();
  }

  if (googleMapsLoaderPromise && googleMapsLoaderKey === apiKey) {
    console.debug('[maps] reusing loader promise');
    return googleMapsLoaderPromise;
  }

  googleMapsLoaderKey = apiKey;
  googleMapsLoaderPromise = new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(apiKey) + '&libraries=places&loading=async';
    script.async = true;
    script.defer = true;
    script.onload = () => {
      console.debug('[maps] SDK loaded');
      resolve();
    };
    script.onerror = () => {
      console.error('[maps] SDK failed to load');
      reject(new Error('google maps failed to load'));
    };
    document.head.appendChild(script);
  });

  return googleMapsLoaderPromise;
};

const setupParcelAddressAutocomplete = (form) => {
  if (!form) {
    console.warn('[maps] autocomplete: missing form');
    return;
  }

  if (form.dataset.googleMapsInitialized === '1') {
    console.debug('[maps] autocomplete already initialized');
    return;
  }

  const apiKey = form.dataset.googleMapsKey;
  if (!apiKey) {
    console.warn('[maps] autocomplete: no API key on form');
    return;
  }

  const addressInput = form.querySelector('#address_line');
  if (!addressInput) {
    console.warn('[maps] autocomplete: address field missing');
    return;
  }

  const latInput = form.querySelector('#parcel-latitude');
  const lngInput = form.querySelector('#parcel-longitude');
  const formattedInput = form.querySelector('#parcel-formatted-address');
  const latDisplay = form.querySelector('[data-latitude-display]');
  const lngDisplay = form.querySelector('[data-longitude-display]');
  const formattedDisplay = form.querySelector('[data-formatted-address-display]');

  const resetCoordinates = () => {
    if (latInput) latInput.value = '';
    if (lngInput) lngInput.value = '';
    if (formattedInput) formattedInput.value = '';
    if (latDisplay) latDisplay.value = '';
    if (lngDisplay) lngDisplay.value = '';
    if (formattedDisplay) formattedDisplay.value = '';
  };

  const logPredictionStatus = (label, payload) => {
    console.debug('[maps] ' + label, payload);
  };

  addressInput.addEventListener('input', () => {
    form.dataset.googleAddressDirty = '1';
    resetCoordinates();
  });

  loadGoogleMapsSdk(apiKey)
    .then(() => {
      if (form.dataset.googleMapsInitialized === '1') {
        return;
      }

      const autocompleteService = new window.google.maps.places.AutocompleteService();
      const placesService = new window.google.maps.places.PlacesService(document.createElement('div'));
      let sessionToken = new window.google.maps.places.AutocompleteSessionToken();
      console.debug('[maps] autocomplete service ready');

      const container = addressInput.closest('.col-12') || addressInput.parentElement;
      if (container && !container.style.position) {
        container.style.position = 'relative';
      }

      const dropdown = document.createElement('div');
      dropdown.className = 'parcel-places-suggestions';
      Object.assign(dropdown.style, {
        position: 'absolute',
        top: '100%',
        left: '0',
        right: '0',
        zIndex: '1055',
        background: '#fff',
        border: '1px solid rgba(15,23,42,0.12)',
        borderRadius: '0.75rem',
        marginTop: '0.35rem',
        boxShadow: '0 10px 30px rgba(15,23,42,0.15)',
        overflow: 'hidden',
        display: 'none',
        maxHeight: '240px',
        overflowY: 'auto'
      });

      const hideDropdown = () => {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
      };

      const showPredictions = (predictions = []) => {
        dropdown.innerHTML = '';
        logPredictionStatus('predictions', predictions);

        if (!predictions.length) {
          hideDropdown();
          return;
        }

        predictions.forEach((prediction) => {
          const option = document.createElement('button');
          option.type = 'button';
          option.className = 'btn w-100 text-start parcel-places-suggestion';
          option.style.padding = '0.65rem 1rem';
          option.style.background = 'transparent';
          option.style.border = 'none';
          option.style.borderBottom = '1px solid rgba(148,163,184,0.25)';
          option.style.fontSize = '0.95rem';
          option.style.color = '#0f172a';
          option.textContent = prediction.description;
          option.dataset.placeId = prediction.place_id;

          option.addEventListener('mousedown', (event) => {
            event.preventDefault();
            const placeId = option.dataset.placeId;
            if (!placeId) {
              return;
            }

            logPredictionStatus('fetching place details', { placeId });
            placesService.getDetails({
              placeId,
              fields: ['formatted_address', 'geometry']
            }, (placeResult, status) => {
              logPredictionStatus('place details status', { status, placeResult });
              if (status !== window.google.maps.places.PlacesServiceStatus.OK || !placeResult?.geometry?.location) {
                console.warn('[maps] place details missing geometry');
                return;
              }

              const lat = placeResult.geometry.location.lat();
              const lng = placeResult.geometry.location.lng();

              if (latInput) latInput.value = lat.toFixed(7);
              if (lngInput) lngInput.value = lng.toFixed(7);
              if (latDisplay) latDisplay.value = lat.toFixed(7);
              if (lngDisplay) lngDisplay.value = lng.toFixed(7);

              if (placeResult.formatted_address) {
                if (formattedInput) formattedInput.value = placeResult.formatted_address;
                if (formattedDisplay) formattedDisplay.value = placeResult.formatted_address;
                addressInput.value = placeResult.formatted_address;
              }

              form.dataset.googleAddressDirty = '0';
              sessionToken = new window.google.maps.places.AutocompleteSessionToken();
              hideDropdown();
            });
          });

          dropdown.appendChild(option);
        });

        dropdown.lastElementChild?.style.setProperty('border-bottom', 'none');
        dropdown.style.display = 'block';
      };

      let pendingRequest = 0;

      addressInput.addEventListener('input', () => {
        form.dataset.googleAddressDirty = '1';
        resetCoordinates();

        const value = addressInput.value.trim();
        if (value.length < 3) {
          hideDropdown();
          return;
        }

        const currentRequest = ++pendingRequest;
        console.debug('[maps] requesting predictions', value);
        autocompleteService.getPlacePredictions(
          {
            input: value,
            sessionToken,
            types: ['address'],
          },
          (predictions, status) => {
            if (currentRequest !== pendingRequest) {
              return;
            }

            logPredictionStatus('predictions status', status);

            if (status !== window.google.maps.places.PlacesServiceStatus.OK || !predictions?.length) {
              hideDropdown();
              return;
            }

            showPredictions(predictions);
          }
        );
      });

      document.addEventListener('click', (event) => {
        if (!dropdown.contains(event.target) && event.target !== addressInput) {
          hideDropdown();
        }
      });

      container?.appendChild(dropdown);
      form.dataset.googleMapsInitialized = '1';
    })
    .catch(() => {});
};

if (!window.setupParcelAddressAutocomplete) {
  window.setupParcelAddressAutocomplete = setupParcelAddressAutocomplete;
}

const setupProviderBarcodeFilter = (scope) => {
  if (!scope) return;
  const providerSelect = scope.querySelector('#provider_id');
  const barcodeSelect = scope.querySelector('#provider_barcode_id');

  if (!providerSelect || !barcodeSelect) return;

  const options = Array.from(barcodeSelect.options);

  const filterOptions = () => {
    const providerId = providerSelect.value || '';

    options.forEach((option) => {
      const optionProvider = option.dataset?.provider ?? '';
      const match = !optionProvider || !providerId || optionProvider === providerId;
      option.hidden = !match;
      option.disabled = !match;
    });

    if (barcodeSelect.value && barcodeSelect.selectedOptions[0]?.hidden) {
      barcodeSelect.value = '';
    }
  };

  filterOptions();
  providerSelect.addEventListener('change', filterOptions);
};

if (!window.setupProviderBarcodeFilter) {
  window.setupProviderBarcodeFilter = setupProviderBarcodeFilter;
}

const registerFilters = (dataTable) => {
  const filters = {
    provider: '',
    courier: '',
    status: '',
    from: null,
    to: null,
  };

  $.fn.dataTable.ext.search.push((settings, searchData, dataIndex) => {
    if (settings.nTable.getAttribute('id') !== 'parcels-table') {
      return true;
    }

    const row = dataTable.row(dataIndex).node();
    const providerId = row.getAttribute('data-provider-id');
    const courierId = row.getAttribute('data-courier-id');
    const status = row.getAttribute('data-status');
    const created = row.getAttribute('data-created');

    if (filters.provider) {
      if (filters.provider === '_none') {
        if (providerId !== '_none') return false;
      } else if (providerId !== filters.provider) {
        return false;
      }
    }

    if (filters.courier) {
      if (filters.courier === '_none') {
        if (courierId !== '_none') return false;
      } else if (courierId !== filters.courier) {
        return false;
      }
    }

    if (filters.status && status !== filters.status) {
      return false;
    }

    if (filters.from || filters.to) {
      if (!created) return false;
      const createdDate = new Date(`${created}T00:00:00`);

      if (filters.from && createdDate < filters.from) {
        return false;
      }

      if (filters.to && createdDate > filters.to) {
        return false;
      }
    }

    return true;
  });

  const providerSelect = $('#parcel-provider-filter');
  const courierSelect = $('#parcel-courier-filter');
  const statusSelect = $('#parcel-status-filter');
  const dateInput = document.getElementById('parcel-date-filter');
  const createOffcanvasEl = document.getElementById('parcel-create-offcanvas');
  const createCodeInput = document.getElementById('parcel-code-input');

  if (providerSelect.length) {
    providerSelect.select2({
      allowClear: true,
      width: '100%',
    });

    providerSelect.on('change', () => {
      const value = providerSelect.val();
      filters.provider = value === null ? '' : value;
      dataTable.draw();
    });
  }

  if (courierSelect.length) {
    courierSelect.select2({
      allowClear: true,
      width: '100%',
    });

    courierSelect.on('change', () => {
      const value = courierSelect.val();
      filters.courier = value === null ? '' : value;
      dataTable.draw();
    });
  }

  if (statusSelect.length) {
    statusSelect.select2({
      allowClear: true,
      width: '100%',
    });

    statusSelect.on('change', () => {
      const value = statusSelect.val();
      filters.status = value === null ? '' : value;
      dataTable.draw();
    });
  }

  if (dateInput) {
    flatpickr(dateInput, {
      mode: 'range',
      dateFormat: 'Y-m-d',
      onChange: (selectedDates) => {
        if (selectedDates.length === 2) {
          const [from, to] = selectedDates;
          const fromDate = new Date(from);
          fromDate.setHours(0, 0, 0, 0);
          const toDate = new Date(to);
          toDate.setHours(23, 59, 59, 999);
          filters.from = fromDate;
          filters.to = toDate;
        } else if (selectedDates.length === 0) {
          filters.from = null;
          filters.to = null;
        }
        dataTable.draw();
      },
      onClose: (selectedDates, dateStr, instance) => {
        if (!dateStr) {
          filters.from = null;
          filters.to = null;
          dataTable.draw();
        }
      },
      allowInput: true,
    });
  }

  if (createOffcanvasEl && createCodeInput) {
    createOffcanvasEl.addEventListener('shown.bs.offcanvas', () => {
      window.setTimeout(() => {
        createCodeInput.focus();
        createCodeInput.select();
      }, 150);
    });
  }
};

document.addEventListener('DOMContentLoaded', () => {
  const table = $('#parcels-table');
  const offcanvasElement = document.getElementById('parcel-offcanvas');
  const offcanvasBody = offcanvasElement?.querySelector('[data-parcel-offcanvas-body]');
  const offcanvasLink = offcanvasElement?.querySelector('[data-parcel-offcanvas-open-full]');
  const offcanvasInstance = offcanvasElement ? new Offcanvas(offcanvasElement) : null;
  const tagModalElement = document.getElementById('parcel-tag-modal');
  const tagModal = tagModalElement ? new Modal(tagModalElement) : null;
  const tagBarcode = document.getElementById('parcel-tag-barcode');
  const tagCodeLabel = tagModalElement?.querySelector('[data-parcel-tag-code]');
  const tagStatus = tagModalElement?.querySelector('[data-parcel-tag-status]');
  const tagFeedback = tagModalElement?.querySelector('[data-parcel-tag-feedback]');
  const tagKillButton = tagModalElement?.querySelector('[data-parcel-kill]');
  const editModalElement = document.getElementById('parcel-edit-modal');
  const editModal = editModalElement ? new Modal(editModalElement) : null;
  const editModalBody = editModalElement?.querySelector('[data-edit-body]');
  const editModalTitle = editModalElement?.querySelector('#parcel-edit-modal-label');
  let activeTag = { code: null, rowSelector: null, killUrl: null };

  if (!table.length) {
    return;
  }

  const dataTable = table.DataTable({
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100],
    order: [[6, 'desc']],
    responsive: true,
    language: {
      url: table.data('lang-url') || undefined,
    },
    columnDefs: [
      { targets: -1, orderable: false, searchable: false },
    ],
  });

  registerFilters(dataTable);

  const loadParcelSummary = (summaryUrl, fullUrl) => {
    if (!offcanvasBody) return;

    offcanvasBody.innerHTML = `
      <div class="text-center w-100 my-6">
        <span class="spinner-border text-primary mb-3" role="status"></span>
        <p class="text-muted mb-0">${window.translations?.loadingParcel ?? 'Cargando información del bulto…'}</p>
      </div>
    `;

    if (offcanvasLink) {
      offcanvasLink.href = fullUrl ?? '#';
      offcanvasLink.classList.toggle('d-none', !fullUrl);
    }

    fetch(summaryUrl, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
      .then(async (response) => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        const data = await response.json();
        offcanvasBody.innerHTML = data.html;
      })
      .catch(() => {
        offcanvasBody.innerHTML = `
          <div class="text-center text-danger my-6">
            <i class="ti tabler-alert-triangle icon-32px mb-3"></i>
            <p class="mb-0">${window.translations?.parcelLoadError ?? 'No se pudo cargar la información del bulto.'}</p>
          </div>
        `;
      });
  };

  const handleEditForm = (form) => {
    if (!form) return;

    const errorsAlert = form.querySelector('[data-form-errors]');
    const successAlert = form.querySelector('[data-form-success]');
    const cancelButton = form.querySelector('[data-modal-dismiss]');
    const submitButton = form.querySelector('[data-submit]');
    const submitLabel = submitButton?.querySelector('[data-submit-label]');
    const originalLabel = submitLabel ? submitLabel.innerHTML : submitButton?.innerHTML;

    if (cancelButton) {
      cancelButton.addEventListener('click', () => {
        editModal?.hide();
      });
    }

    setupProviderBarcodeFilter(form);
    if (window.setupParcelAddressAutocomplete) {
      window.setupParcelAddressAutocomplete(form);
    }

    const showErrors = (messages) => {
      if (!errorsAlert) return;
      errorsAlert.classList.remove('d-none');
      errorsAlert.innerHTML = Array.isArray(messages)
        ? `<ul class="mb-0">${messages.map((message) => `<li>${message}</li>`).join('')}</ul>`
        : messages;
    };

    const showSuccess = (message) => {
      if (!successAlert) return;
      successAlert.classList.remove('d-none');
      successAlert.textContent = message;
    };

    form.addEventListener('submit', (event) => {
      event.preventDefault();

      errorsAlert?.classList.add('d-none');
      if (successAlert) {
        successAlert.classList.add('d-none');
        successAlert.textContent = '';
      }

      if (submitButton) {
        submitButton.disabled = true;
        if (submitLabel) {
          submitLabel.innerHTML = `${window.translations?.saving ?? 'Guardando…'} <span class="spinner-border spinner-border-sm ms-2"></span>`;
        } else {
          submitButton.innerHTML = `${window.translations?.saving ?? 'Guardando…'} <span class="spinner-border spinner-border-sm ms-2"></span>`;
        }
      }

      const formData = new FormData(form);

      fetch(form.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken ?? '',
        },
        body: formData,
      })
        .then(async (response) => {
          if (response.ok) {
            return response.json();
          }

          if (response.status === 422) {
            const data = await response.json();
            const messages = Object.values(data.errors ?? {}).flat();
            showErrors(messages);
            throw new Error('validation');
          }

          throw new Error('request');
        })
        .then((data) => {
          showSuccess(data?.message ?? window.translations?.parcelUpdated ?? 'Datos actualizados correctamente.');
          window.setTimeout(() => {
            window.location.reload();
          }, 600);
        })
        .catch((error) => {
          if (error.message !== 'validation') {
            showErrors(window.translations?.parcelUpdateError ?? 'No se pudo actualizar el bulto.');
          }
        })
        .finally(() => {
          if (submitButton) {
            submitButton.disabled = false;
            if (submitLabel && originalLabel) {
              submitLabel.innerHTML = originalLabel;
            } else if (originalLabel) {
              submitButton.innerHTML = originalLabel;
            }
          }
        });
    });
  };

  const loadEditForm = (url) => {
    if (!editModalBody) return;

    editModalBody.innerHTML = `
      <div class="text-center my-4">
        <span class="spinner-border text-primary mb-3" role="status"></span>
        <p class="text-muted mb-0">${window.translations?.loadingParcel ?? 'Cargando datos del bulto…'}</p>
      </div>
    `;

    fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
    })
      .then(async (response) => {
        if (!response.ok) {
          throw new Error('request');
        }
        return response.json();
      })
      .then((data) => {
        if (editModalTitle && data?.title) {
          editModalTitle.textContent = data.title;
        }
        editModalBody.innerHTML = data?.html ?? '';
        const form = editModalBody.querySelector('[data-edit-form]');
        handleEditForm(form);
      })
      .catch(() => {
        editModalBody.innerHTML = `
          <div class="text-center text-danger my-4">
            <i class="ti tabler-alert-triangle icon-32px mb-3"></i>
            <p class="mb-0">${window.translations?.parcelLoadError ?? 'No se pudo cargar la información del bulto.'}</p>
          </div>
        `;
      });
  };

  const bindDetailButtons = () => {
    table.find('.parcel-detail-trigger').off('click').on('click', function handleClick(event) {
      event.preventDefault();
      const summaryUrl = this.getAttribute('data-summary-url');
      const fullUrl = this.getAttribute('data-full-url');

      if (!summaryUrl || !offcanvasInstance) {
        if (fullUrl) {
          window.location.href = fullUrl;
        }
        return;
      }

      loadParcelSummary(summaryUrl, fullUrl);
      offcanvasInstance.show();
    });

    table.find('.parcel-edit-trigger').off('click').on('click', function handleEditClick(event) {
      event.preventDefault();
      const editUrl = this.getAttribute('data-edit-url');
      if (!editUrl || !editModal) {
        const fallbackHref = this.getAttribute('href');
        if (fallbackHref) {
          window.location.href = fallbackHref;
        }
        return;
      }

      loadEditForm(editUrl);
      editModal.show();
    });

    table.find('.parcel-tag-trigger').off('click').on('click', function handleTagClick(event) {
      event.preventDefault();
      if (!tagModal) return;

      const code = this.getAttribute('data-code');
      const killUrl = this.getAttribute('data-kill-url');
      const rowSelector = this.getAttribute('data-row-selector');
      const currentRow = rowSelector ? document.querySelector(rowSelector) : null;
      const badge = currentRow?.querySelector('[data-status-badge]');

      activeTag = { code, rowSelector, killUrl };

      if (tagBarcode && code) {
        JsBarcode(tagBarcode, code, {
          format: 'CODE128',
          displayValue: false,
          lineColor: '#000',
          width: 2,
          height: 80,
          margin: 10,
        });
      }

      if (tagCodeLabel && code) {
        tagCodeLabel.textContent = code;
      }

      if (tagStatus) {
        const statusText = badge ? badge.textContent.trim() : '';
        tagStatus.textContent = statusText ? `${window.translations?.currentStatus ?? 'Estado actual'}: ${statusText}` : '';
      }

      if (tagFeedback) {
        tagFeedback.textContent = '';
      }

      if (tagKillButton) {
        tagKillButton.disabled = !killUrl;
      }

      tagModal.show();
    });
  };

  bindDetailButtons();
  table.on('draw.dt', bindDetailButtons);

  // Bulk parcel registration helpers
  const codeInput = document.getElementById('parcel-code-input');
  const addButton = document.getElementById('parcel-code-add');
  const clearButton = document.getElementById('parcel-codes-clear');
  const codesList = document.getElementById('parcel-codes-list');
  const hiddenTextarea = document.getElementById('parcel-codes-hidden');
  const counts = {
    pending: document.querySelector('[data-count-pending]'),
    duplicate: document.querySelector('[data-count-duplicate]'),
    total: document.querySelector('[data-count-total]'),
  };

  const codesState = new Map(); // code => {status, element}

  const statusText = {
    pending: window.translations?.listPending ?? 'Pendiente de guardar',
    duplicate: window.translations?.listDuplicate ?? 'Ya existe en el sistema',
    local: window.translations?.listLocalDuplicate ?? 'Ya se añadió en la lista',
  };

  const statusClasses = {
    pending: ['border-start', 'border-3', 'border-success', 'list-group-item'],
    duplicate: ['border-start', 'border-3', 'border-warning', 'list-group-item'],
  };

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  function updateHiddenField() {
    const pending = Array.from(codesState.entries())
      .filter(([, entry]) => entry.status === 'pending')
      .map(([code]) => code);

    if (hiddenTextarea) {
      hiddenTextarea.value = pending.join('\n');
    }

    updateCounts();
  }

  function updateCounts() {
    const pendingCount = Array.from(codesState.values()).filter((entry) => entry.status === 'pending').length;
    const duplicateCount = Array.from(codesState.values()).filter((entry) => entry.status === 'duplicate').length;
    const totalCount = codesState.size;

    if (counts.pending) counts.pending.textContent = pendingCount;
    if (counts.duplicate) counts.duplicate.textContent = duplicateCount;
    if (counts.total) counts.total.textContent = totalCount;
  }

  function flashExisting(code) {
    const entry = codesState.get(code);
    if (!entry) return;
    entry.element.classList.add('position-relative');
    entry.element.classList.add('border', 'border-info');
    entry.element.classList.add('shadow-sm');
    window.setTimeout(() => {
      entry.element.classList.remove('border', 'border-info', 'shadow-sm');
    }, 1200);
  }

  function createListItem(code, status) {
    const li = document.createElement('li');
    li.dataset.code = code;
    li.dataset.status = status;
    li.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-start', 'gap-3');
    li.classList.add(...(statusClasses[status] ?? ['border-start', 'border-3', 'border-secondary', 'list-group-item']));

    li.innerHTML = `
      <div>
        <span class="fw-semibold d-block">${code}</span>
        <small class="text-muted">${statusText[status] ?? status}</small>
      </div>
      <button type="button" class="btn btn-sm btn-icon btn-label-danger" data-remove-code="${code}">
        <i class="ti tabler-x"></i>
      </button>
    `;

    const removeButton = li.querySelector('[data-remove-code]');
    removeButton.addEventListener('click', () => {
      codesState.delete(code);
      li.remove();
      updateHiddenField();
    });

    codesList?.appendChild(li);

    return li;
  }

  function addCodeToList(code, status) {
    const element = createListItem(code, status);
    codesState.set(code, { status, element });
    updateHiddenField();
  }

  function handleCode(code) {
    const trimmed = code.trim();
    if (!trimmed) {
      return;
    }

    if (codesState.has(trimmed)) {
      flashExisting(trimmed);
      return;
    }

    fetch(window.routes?.parcelsCheck ?? '', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken ?? '',
      },
      body: JSON.stringify({ code: trimmed }),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then((data) => {
        addCodeToList(trimmed, data.exists ? 'duplicate' : 'pending');
      })
      .catch(() => {
        addCodeToList(trimmed, 'duplicate');
      });
  }

  function queueCodes(rawInput) {
    if (!rawInput) return;
    const parts = rawInput
      .split(/[\n\r,;\t]+/)
      .map((part) => part.trim())
      .filter((part) => part.length > 0);

    parts.forEach(handleCode);
  }

  if (codeInput) {
    codeInput.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        queueCodes(codeInput.value);
        codeInput.value = '';
      }
    });

    codeInput.addEventListener('paste', (event) => {
      const text = event.clipboardData?.getData('text');
      if (text) {
        event.preventDefault();
        queueCodes(text);
        codeInput.value = '';
      }
    });
  }

  if (addButton && codeInput) {
    addButton.addEventListener('click', () => {
      queueCodes(codeInput.value);
      codeInput.value = '';
      codeInput.focus();
    });
  }

  if (clearButton) {
    clearButton.addEventListener('click', () => {
      codesState.clear();
      codesList.innerHTML = '';
      updateHiddenField();
      codeInput?.focus();
    });
  }

  // Prefill hidden textarea (old values) if validation failed
  if (hiddenTextarea?.value) {
    queueCodes(hiddenTextarea.value);
    hiddenTextarea.value = '';
  }

  if (tagKillButton) {
    tagKillButton.addEventListener('click', () => {
      if (!activeTag.killUrl) {
        return;
      }

      tagKillButton.disabled = true;
      tagKillButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + (window.translations?.marking ?? 'Actualizando…');

      fetch(activeTag.killUrl, {
        method: 'PATCH',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken ?? '',
        },
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error('Request failed');
          }
          return response.json();
        })
        .then((data) => {
          tagFeedback.textContent = window.translations?.markedReturned ?? 'Bulto marcado como retirado.';
          const row = activeTag.rowSelector ? document.querySelector(activeTag.rowSelector) : null;
          const badge = row?.querySelector('[data-status-badge]');
          if (badge && data?.parcel?.status) {
            badge.textContent = window.translations?.returnedLabel ?? 'Returned';
            badge.className = 'badge bg-label-danger status-badge';
          }
          if (row) {
            row.setAttribute('data-status', 'returned');
          }
        })
        .catch(() => {
          tagFeedback.textContent = window.translations?.markingError ?? 'No se pudo actualizar el estado.';
        })
        .finally(() => {
          tagKillButton.disabled = false;
          tagKillButton.innerHTML = window.translations?.markReturnedButton ?? 'Marcar como retirado';
        });
    });
  }
});
