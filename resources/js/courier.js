if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch((error) => console.error('SW registration failed', error));
  });
}

const docReady = (fn) => {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fn, { once: true });
  } else {
    fn();
  }
};

docReady(() => {
  const state = window.courierInitialState ?? {
    filters: { status: ['assigned', 'out_for_delivery'], search: '' },
    meta: {},
    parcels: { active: [], completed: [] },
    routes: {},
  };

  const loadGoogleMapsSdk = (apiKey) => {
    if (!apiKey) {
      console.warn('[courier-map] missing Google Maps key');
      return Promise.reject(new Error('missing maps key'));
    }

    if (window.google?.maps) {
      return Promise.resolve();
    }

    if (window.__courierMapsLoader && window.__courierMapsLoader.key === apiKey) {
      return window.__courierMapsLoader.promise;
    }

    const promise = new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(apiKey) + '&libraries=places&v=weekly';
      script.async = true;
      script.defer = true;
      script.onload = () => resolve();
      script.onerror = () => reject(new Error('google maps failed to load'));
      document.head.appendChild(script);
    });

    window.__courierMapsLoader = { key: apiKey, promise };

    return promise;
  };

  const courierId = state.meta?.courier_id ?? null;
  const courierActive = state.meta?.courier_active ?? true;
  const canOperate = Boolean(courierId) && courierActive;
  const supportsBarcodeDetector = 'BarcodeDetector' in window && (state.meta?.canUseBarcodeDetector ?? true);
  const openScannerBtn = document.getElementById('courier-open-scanner');
  const manualInputBtn = document.getElementById('courier-manual-input');
  const navScannerBtn = document.getElementById('courier-nav-scanner');
  const closeScannerBtn = document.getElementById('courier-close-scanner');
  const refreshBtn = document.getElementById('courier-refresh');
  const scannerPanel = document.getElementById('courier-scanner-panel');
  const blockedMessage = !courierId
    ? 'Aún no tienes courier asignado. Pide a coordinación que te vincule.'
    : 'Tu perfil de courier está inactivo. Contacta a coordinación.';
  const previewContainer = document.getElementById('courier-scanner-preview');
  const lastCodeEl = document.getElementById('courier-last-code');
  const activeList = document.getElementById('courier-active-list');

  let detector;
  let videoEl;
  let stream;
  let detectionActive = false;
  let lastDetectionValue = '';
  const successSound = new Audio('/assets/audio/scan-success.wav');
  const errorSound = new Audio('/assets/audio/scan-error.wav');

  const vibration = (pattern) => {
    if (typeof navigator !== 'undefined' && navigator.vibrate) {
      navigator.vibrate(pattern);
    }
  };

  const updateFeedback = (value) => {
    if (lastCodeEl) {
      lastCodeEl.textContent = value || '—';
    }
  };

  const stopScanner = async () => {
    detectionActive = false;
    if (stream) {
      stream.getTracks().forEach((track) => track.stop());
      stream = undefined;
    }
    if (videoEl) {
      videoEl.pause();
      videoEl.srcObject = null;
      videoEl.remove();
      videoEl = undefined;
    }
    previewContainer?.classList.remove('detected');
    scannerPanel?.classList.add('hidden');
  };

  const detectionLoop = async () => {
    if (!detector || !videoEl || !detectionActive) {
      return;
    }

    try {
      const codes = await detector.detect(videoEl);
      if (codes.length) {
        const candidate = codes[0]?.rawValue?.trim();
        if (candidate && candidate !== lastDetectionValue) {
          lastDetectionValue = candidate;
          updateFeedback(candidate);
          successSound.currentTime = 0;
          void successSound.play().catch(() => {});
          vibration([45, 30, 45]);
          previewContainer?.classList.add('detected');
          window.setTimeout(() => previewContainer?.classList.remove('detected'), 250);
          const event = new CustomEvent('courier:code-detected', {
            detail: { value: candidate },
          });
          window.dispatchEvent(event);
        }
      }
    } catch (error) {
      console.error('Barcode detection failed', error);
    }

    if (detectionActive) {
      window.requestAnimationFrame(detectionLoop);
    }
  };

  const startScanner = async () => {
    if (!supportsBarcodeDetector) {
      return;
    }

    if (!navigator.mediaDevices?.getUserMedia) {
      errorSound.currentTime = 0;
      void errorSound.play().catch(() => {});
      alert('No se pudo acceder a la cámara en este dispositivo. Usa el registro manual.');
      return;
    }

    try {
      detector = detector ?? new window.BarcodeDetector({
        formats: ['code_128', 'code_39', 'ean_13', 'ean_8', 'qr_code', 'upc_e', 'upc_a'],
      });
    } catch (error) {
      console.warn('Barcode detector unavailable', error);
      return;
    }

    if (!videoEl) {
      videoEl = document.createElement('video');
      videoEl.setAttribute('playsinline', '');
      videoEl.className = 'h-full w-full object-cover';
      previewContainer?.appendChild(videoEl);
    }

    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: {
          facingMode: 'environment',
          width: { ideal: 1280 },
          height: { ideal: 720 },
        },
        audio: false,
      });
      videoEl.srcObject = stream;
      await videoEl.play();
      lastDetectionValue = '';
      detectionActive = true;
      scannerPanel?.classList.remove('hidden');
      window.requestAnimationFrame(detectionLoop);
    } catch (error) {
      console.error('Unable to start scanner', error);
      errorSound.currentTime = 0;
      void errorSound.play().catch(() => {});
      alert('No se pudo iniciar la cámara. Comprueba permisos o usa el registro manual.');
    }
  };

  if ((!supportsBarcodeDetector || !canOperate) && openScannerBtn) {
    openScannerBtn.setAttribute('disabled', 'true');
    openScannerBtn.classList.add('cursor-not-allowed', 'opacity-40');
  }

  const openScanner = () => {
    if (!canOperate) {
      alert(blockedMessage);
      return;
    }

    if (!supportsBarcodeDetector) {
      alert('El escáner no está disponible en este dispositivo. Usa el registro manual.');
      return;
    }

    void startScanner();
  };

  if (!canOperate) {
    navScannerBtn?.setAttribute('disabled', 'true');
    manualInputBtn?.setAttribute('disabled', 'true');
  }

  openScannerBtn?.addEventListener('click', openScanner);
  navScannerBtn?.addEventListener('click', openScanner);
  closeScannerBtn?.addEventListener('click', () => void stopScanner());

  manualInputBtn?.addEventListener('click', () => {
    if (!canOperate) {
      alert(blockedMessage);
      return;
    }

    const manual = window.prompt('Introduce el código de barras o tracking:');
    if (!manual) {
      return;
    }

    updateFeedback(manual.trim());
    vibration([30, 30, 30]);
    window.dispatchEvent(
      new CustomEvent('courier:code-manual', {
        detail: { value: manual.trim() },
      })
    );
  });

  refreshBtn?.addEventListener('click', () => {
    window.location.assign(state.routes?.dashboard ?? window.location.href);
  });

  window.addEventListener('courier:code-detected', (event) => {
    const code = event.detail?.value;
    if (!code || !activeList) {
      return;
    }

    const match = activeList.querySelector(`[data-code="${CSS.escape(code)}"]`);
    if (match) {
      const card = match.closest('article') ?? match;
      card.classList.add('ring-2', 'ring-indigo-400');
      window.setTimeout(() => card.classList.remove('ring-2', 'ring-indigo-400'), 1500);
    }
  });

  const setupCourierMap = () => {
    const mapContainer = document.getElementById('courier-map');
    if (!mapContainer) {
      return;
    }

    const apiKey = state.meta?.mapsApiKey ?? mapContainer.dataset.googleMapsKey ?? '';
    if (!apiKey) {
      console.warn('[courier-map] no API key provided');
      return;
    }

    const parcels = Array.isArray(state.parcels?.active) ? state.parcels.active : [];
    const parsedParcels = parcels
      .map((parcel, index) => ({
        ...parcel,
        latitude: parcel.latitude !== null ? parseFloat(parcel.latitude) : null,
        longitude: parcel.longitude !== null ? parseFloat(parcel.longitude) : null,
        index,
      }))
      .filter((parcel) => Number.isFinite(parcel.latitude) && Number.isFinite(parcel.longitude));

    loadGoogleMapsSdk(apiKey)
      .then(() => {
        const google = window.google;

        if (!google?.maps || typeof google.maps.Map !== 'function') {
          throw new Error('Google Maps unavailable after load');
        }

        const detailCard = document.querySelector('[data-selected-card]');
        const detailCode = detailCard?.querySelector('[data-selected-code]');
        const detailAddress = detailCard?.querySelector('[data-selected-address]');
        const detailProvider = detailCard?.querySelector('[data-selected-provider]');
        const detailStatus = detailCard?.querySelector('[data-selected-status]');
        const detailActions = detailCard ? Array.from(detailCard.querySelectorAll('[data-selected-action]')) : [];

        const parcelMap = new Map(parsedParcels.map((parcel) => [parcel.code, parcel]));

        const showParcelDetails = (parcel) => {
          if (!detailCard || !parcel) return;

          detailCard.classList.remove('hidden');
          detailCode.textContent = parcel.code;
          detailAddress.textContent = parcel.formatted_address || parcel.address_line || '—';
          detailProvider.textContent = parcel.provider ? parcel.provider : '';
          detailStatus.textContent = parcel.status ? parcel.status.replace(/_/g, ' ') : '—';

          detailActions.forEach((button) => {
            const action = button.dataset.selectedAction;
            button.dataset.code = parcel.code;
            button.dataset.lat = parcel.latitude ?? '' ;
            button.dataset.lng = parcel.longitude ?? '' ;
            button.disabled = !parcel.code || (action === 'open-navigation' && (!parcel.latitude || !parcel.longitude));
          });

          if (sheet?.classList.contains('collapsed')) {
            sheet.classList.remove('collapsed');
            updateSheetState();
          }
        };

        const initialCenter = (() => {
          if (parsedParcels.length) {
            return {
              lat: parsedParcels[0].latitude,
              lng: parsedParcels[0].longitude,
            };
          }

          return { lat: 40.4168, lng: -3.7038 };
        })();

        const map = new google.maps.Map(mapContainer, {
          center: initialCenter,
          zoom: parsedParcels.length ? 12 : 5,
          disableDefaultUI: true,
        });

        const listItems = Array.from(document.querySelectorAll('#courier-active-list [data-code]'));

        const highlightItem = (code) => {
          const item = listItems.find((el) => el.dataset.code === code);
          if (!item) return;
          item.classList.add('ring-2', 'ring-indigo-400');
          item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
          window.setTimeout(() => item.classList.remove('ring-2', 'ring-indigo-400'), 1500);
        };

        const markers = parsedParcels.map((parcel, idx) => {
          const marker = new google.maps.Marker({
            map,
            position: { lat: parcel.latitude, lng: parcel.longitude },
            label: {
              text: String(idx + 1),
              color: '#ffffff',
              fontWeight: 'bold',
            },
          });

          marker.addListener('click', () => {
            const position = marker.getPosition();
            if (position) {
              map.panTo(position);
              map.setZoom(Math.max(map.getZoom(), 14));
            }
            highlightItem(parcel.code);
            showParcelDetails(parcel);
          });

          return { code: parcel.code, marker };
        });

        const riderMarker = new google.maps.Marker({
          map,
          position: initialCenter,
          icon: {
            path: 'M 0 -1 L 1 1 L 0 0 L -1 1 Z',
            scale: 12,
            fillColor: '#22d3ee',
            fillOpacity: 0.95,
            strokeColor: '#0f172a',
            strokeWeight: 2,
          },
        });

        const recenterButton = document.getElementById('courier-map-recenter');
        let riderPosition = initialCenter;

        const recenter = () => {
          map.panTo(riderPosition);
          map.setZoom(Math.max(map.getZoom(), 14));
        };

        recenterButton?.addEventListener('click', () => {
          recenter();
        });

        if (navigator.geolocation) {
          navigator.geolocation.watchPosition(
            (position) => {
              riderPosition = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
              };
              riderMarker.setPosition(riderPosition);
            },
            () => {},
            { enableHighAccuracy: true, maximumAge: 15000 }
          );
        }

        const openNavigation = (lat, lng) => {
          if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            alert('No hay coordenadas disponibles para esta parada.');
            return;
          }
          const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
          window.open(url, '_blank');
        };

        document.addEventListener('click', (event) => {
          const navBtn = event.target.closest('[data-action="open-navigation"]');
          if (!navBtn) return;
          const lat = parseFloat(navBtn.dataset.lat ?? '');
          const lng = parseFloat(navBtn.dataset.lng ?? '');
          openNavigation(lat, lng);
        });

        window.addEventListener('courier:code-detected', (event) => {
          const code = event.detail?.value;
          const markerEntry = markers.find((entry) => entry.code === code);
          if (markerEntry?.marker) {
            const position = markerEntry.marker.getPosition();
            if (position) {
              map.panTo(position);
            }
            highlightItem(code);
            const parcel = parcelMap.get(code);
            if (parcel) {
              showParcelDetails(parcel);
            }
          }
        });

        if (parsedParcels.length) {
          showParcelDetails(parsedParcels[0]);
        }
      })
      .catch((error) => {
        console.error('[courier-map] unable to load maps', error);
      });
  };

  const handleAction = (event) => {
    const target = event.target.closest('[data-action]');
    if (!target) {
      return;
    }

    const code = target.getAttribute('data-code');
    const action = target.getAttribute('data-action');
    if (!code || !action) {
      return;
    }

    if (action === 'mark-delivered') {
      window.dispatchEvent(new CustomEvent('courier:parcel-status', {
        detail: { code, status: 'delivered' },
      }));
      alert(`Marca ${code} como entregado desde la app o llama a soporte.`);
    }

    if (action === 'report-issue') {
      const comment = window.prompt('Describe la incidencia para el paquete ' + code + ':');
      window.dispatchEvent(new CustomEvent('courier:parcel-incident', {
        detail: { code, comment },
      }));
      if (comment) {
        alert('Incidencia registrada localmente. Sincroniza más tarde.');
      }
    }
  };

  document.addEventListener('click', handleAction);

  document.addEventListener('click', (event) => {
    const listItem = event.target.closest('#courier-active-list [data-code]');
    if (!listItem) return;
    const code = listItem.getAttribute('data-code');
    const parcel = code ? parcelMap.get(code) : null;
    if (parcel) {
      showParcelDetails(parcel);
    }
  });

  window.addEventListener('beforeunload', () => {
    if (detectionActive) {
      stopScanner().catch(() => {});
    }
  });

  const sheet = document.querySelector('[data-sheet]');
  const sheetToggle = document.querySelector('[data-sheet-toggle]');
  const sheetBody = document.querySelector('[data-sheet-body]');

  const updateSheetState = () => {
    if (!sheet || !sheetToggle) return;
    const expanded = !sheet.classList.contains('collapsed');
    sheetToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
  };

  if (sheet) {
    sheet.classList.add('collapsed');
  }

  sheetToggle?.addEventListener('click', () => {
    if (!sheet) return;
    sheet.classList.toggle('collapsed');
    updateSheetState();
  });

  updateSheetState();

  setupCourierMap();
});
