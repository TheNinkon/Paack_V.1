# Rider API (Beta)

This document summarizes the REST endpoints exposed under `/api/rider` for the forthcoming courier mobile app.

> **Authentication**
>
> - Login issues a bearer token that must be sent in the `Authorization: Bearer <token>` header (or `X-Courier-Token` fallback).
> - Tokens are stored hashed in the new `courier_tokens` table and expire 30 days after creation.
> - Tokens can be revoked via the logout endpoint or automatically if marked inactive/expired.

## Endpoints

### `POST /api/rider/login`

Request body:

```json
{
  "email": "courier@example.com",
  "password": "secret",
  "device_name": "iPhone 15" // optional
}
```

Successful response:

```json
{
  "token": "<plain-token>",
  "token_type": "Bearer",
  "expires_at": "2025-10-26T15:21:00+00:00",
  "user": {
    "id": 42,
    "name": "Juan Pérez",
    "email": "courier@example.com",
    "phone": "+34 600 000 000",
    "client_id": 5,
    "courier": {
      "id": 12,
      "vehicle_type": "moto",
      "external_code": "CTT-123",
      "active": true
    }
  }
}
```

Common errors:

- `422` – invalid credentials
- `403` – user is not active or lacks the `courier` role

### `POST /api/rider/logout`

Headers: `Authorization: Bearer <token>`

Returns `200` with `{ "message": "Sesión cerrada correctamente." }` and revokes the token.

### `GET /api/rider/me`

Headers: `Authorization: Bearer <token>`

Returns the authenticated profile payload (same structure as the `user` node in the login response).

### `GET /api/rider/parcels`

Query params:

- `status` – comma-separated list (pending, assigned, out_for_delivery, delivered, incident, returned)
- `search` – matches code, stop, or address (case-insensitive)
- `today_only` – `true/false`
- `per_page` – defaults to 50, maximum 100

Response (paginated):

```json
{
  "data": [
    {
      "id": 1024,
      "code": "0082800082809760457567001",
      "status": "out_for_delivery",
      "provider": {
        "id": 3,
        "name": "CTT"
      },
      "stop_code": "STOP-9811",
      "address_line": "Calle Migdia 23, Local 1",
      "city": "Girona",
      "state": "Catalunya",
      "postal_code": "17001",
      "liquidation_code": "LQ-001",
      "liquidation_reference": "REF-001",
      "latest_scan_at": "2025-09-25T18:40:00+00:00",
      "latest_scan_by": {
        "id": 7,
        "name": "Operario Almacén"
      },
      "updated_at": "2025-09-25T18:42:00+00:00",
      "created_at": "2025-09-25T08:15:00+00:00"
    }
  ],
  "links": {
    "first": "…",
    "last": "…",
    "prev": null,
    "next": "…"
  },
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 120,
    "filters": {
      "status": ["out_for_delivery"],
      "search": null,
      "today_only": false
    }
  }
}
```

### `POST /api/rider/parcels/{id}/events`

Body:

```json
{
  "status": "delivered",
  "comment": "Entregado al portero."
}
```

Rules:

- `status` must be one of: `pending`, `assigned`, `out_for_delivery`, `delivered`, `incident`, `returned`.
- `comment` is optional (max 500 chars).

Side effects:

- Updates the parcel status (if it changed).
- Registers a `parcel_status_updated` (or `parcel_status_confirmed`) event with payload referencing the courier.
- Returns the refreshed parcel resource.

Response example:

```json
{
  "message": "Estado actualizado correctamente.",
  "parcel": {
    "id": 1024,
    "code": "0082800082809760457567001",
    "status": "delivered",
    "provider": { "id": 3, "name": "CTT" },
    "latest_scan_at": null,
    "latest_scan_by": null,
    "updated_at": "2025-09-25T19:01:00+00:00",
    "created_at": "2025-09-25T08:15:00+00:00"
  }
}
```

## Token storage

Tokens are saved in the `courier_tokens` table with hashed values (SHA-256). Use the `Authorization` header on every request. A helper fallback header `X-Courier-Token` is also supported for environments that cannot set `Authorization`.

Tokens can be revoked manually by deleting the row in the database or via the logout endpoint. Expiration defaults to 30 days but can be changed per token by updating `expires_at`.

## Manual testing with cURL

```bash
# Login
curl -X POST http://localhost:8000/api/rider/login \
  -H 'Accept: application/json' \
  -d 'email=courier@example.com' \
  -d 'password=password'

# List parcels
curl http://localhost:8000/api/rider/parcels \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer {TOKEN}'

# Update status
curl -X POST http://localhost:8000/api/rider/parcels/1024/events \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer {TOKEN}' \
  -d 'status=delivered' \
  -d 'comment=Recibido por Juan'
```

## Pending tasks

- Add push notifications & background sync once the mobile client stabilizes.
- Revisit token rotation/refresh policies.
- Wire assignment logic (parcels per courier) when that data model is ready.
