# Hardware Endpoint Alignment Report
**Generated:** 2026-10-08  
**PRD Version:** v2.5  
**Purpose:** Verify backend readiness for hardware device integration

---

## Executive Summary

✅ **All critical hardware endpoints are implemented and aligned with PRD §5 specifications.**

Your backend is **production-ready** for hardware devices to connect and send data. All four device types have their required endpoints with proper authentication, idempotency, and real-time broadcasting.

---

## Device Type Coverage

### 1. Mobile Units (FMC225) - `mobile_unit`
**Hardware:** Teltonika FMC225 + ATG + RFID tag  
**Installed on:** Browser Tanks & Fuel Tankers  
**Reports:** GPS position, tank level, satellite count

| PRD Requirement | Implementation | Status |
|---|---|---|
| **§4.1** TCP Gateway → Internal HTTP endpoint | `POST /api/internal/ingest/avl` | ✅ READY |
| IMEI-based handshake | `AvlIngestionController` resolves device by IMEI | ✅ READY |
| Batch AVL records | Accepts up to 200 records per batch | ✅ READY |
| GPS data (lat, lon, satellites) | Stored in `tank_level_readings` | ✅ READY |
| ATG level conversion to liters | Stored as `fuel_level_liters` | ✅ READY |
| Idempotency on `(device_id, timestamp)` | `updateOrCreate` on composite key | ✅ READY |
| Geofence evaluation (§2.K) | `CheckGeofenceJob::dispatch()` for valid GPS | ✅ READY |
| Zero-data-loss ack (§2.B) | Returns 2xx only after DB commit | ✅ READY |
| Real-time broadcast | `TankReadingReceived` + `FleetPositionUpdated` | ✅ READY |
| RTC sanity check | Rejects readings >24h future or >7d past | ✅ READY |

---

### 2. Fill Line Units (RUT956) - `fill_line`
**Hardware:** Teltonika RUT956 + flow meter + RFID reader  
**Installed on:** Pipe from Fuel Tanker → Main Tank  
**Reports:** Which Fuel Tanker is unloading (RFID) + liters transferred

| PRD Requirement | Implementation | Status |
|---|---|---|
| **§5** Transaction ingestion | `POST /api/v1/transactions` | ✅ READY |
| Device authentication | Bearer token via `VerifyDeviceToken` middleware | ✅ READY |
| Transaction deduplication | `(device_id, device_txn_id)` unique constraint | ✅ READY |
| RFID tag validation | `tag_id` field captured | ✅ READY |
| Transfer type: `fill_to_main` | Validated enum value | ✅ READY |
| Session window (started_at, ended_at) | Required fields with validation | ✅ READY |
| Anomaly detection trigger (§2.C) | `DetectAnomalyJob::dispatch()` | ✅ READY |
| Real-time broadcast | `TankReadingReceived` event | ✅ READY |
| **§5** RFID list sync | `GET /api/v1/sync/rfid` | ✅ READY |
| 304 if unchanged | Checks `rfid_list_version` header | ✅ READY |
| **§5** Emergency RFID check | `POST /api/v1/auth/check` | ✅ READY |
| **§5** Scan rejection logging | `POST /api/v1/scan-rejections` | ✅ READY |

---

### 3. Dispense Line Units (RUT956) - `dispense_line`
**Hardware:** Teltonika RUT956 + flow meter + RFID reader on nozzle  
**Installed on:** Main Tank → Browser Tank dispenser  
**Reports:** Which Browser Tank is being filled (RFID) + liters transferred

| PRD Requirement | Implementation | Status |
|---|---|---|
| **§5** Transaction ingestion | `POST /api/v1/transactions` | ✅ READY |
| Transfer type: `dispense_to_browser` | Validated enum value | ✅ READY |
| Same endpoint as Fill Line | Unified transaction ingestion | ✅ READY |
| All Fill Line features | Authentication, dedup, RFID, anomaly detection | ✅ READY |

---

### 4. Main Tank ATG (RUT956) - `main_tank_atg`
**Hardware:** Teltonika RUT956 + ATG level sensor  
**Installed on:** Main Tank (fixed installation)  
**Reports:** Main Tank level readings over time

| PRD Requirement | Implementation | Status |
|---|---|---|
| **§5** Batch level readings | `POST /api/v1/site/readings` | ✅ READY |
| Device authentication | Bearer token via `VerifyDeviceToken` | ✅ READY |
| Batch size up to 100 readings | Validated in controller | ✅ READY |
| Idempotency on `(device_id, timestamp)` | `updateOrCreate` composite key | ✅ READY |
| GPS support (optional) | Accepts lat/lon/satellites fields | ✅ READY |
| Real-time broadcast | `TankReadingReceived` event | ✅ READY |
| RTC sanity check | Rejects out-of-bounds timestamps | ✅ READY |

---

## Authentication & Security (PRD §4.2, §7.A)

| PRD Requirement | Implementation | Status |
|---|---|---|
| Bearer token over HTTPS/TLS | `Authorization: Bearer <token>` | ✅ READY |
| SHA-256 hash storage | `api_token_hash` in `hardware_devices` table | ✅ READY |
| Device ID header | `X-Device-ID` available for identification | ✅ READY |
| Token validation | `VerifyDeviceToken` middleware | ✅ READY |
| Active device check | Returns 403 if `status != 'active'` | ✅ READY |
| Unknown device rejection | Returns 401 for unregistered devices | ✅ READY |

---

## Data Integrity & Reliability (PRD §2.B)

| PRD Requirement | Implementation | Status |
|---|---|---|
| Zero-data-loss acknowledgment | 2xx response only after DB commit | ✅ READY |
| Idempotent transaction ingestion | `(device_id, device_txn_id)` unique key | ✅ READY |
| Idempotent level readings | `(device_id, timestamp)` unique key | ✅ READY |
| Store-and-forward support | Devices retry on 5xx/timeout | ✅ READY |
| Last seen tracking | `hardware_devices.last_seen` updated | ✅ READY |
| Batch processing | Up to 200 AVL records, 100 site readings | ✅ READY |

---

## Real-time Features (PRD §1.2, §5)

| PRD Requirement | Implementation | Status |
|---|---|---|
| Transaction broadcast | `TankReadingReceived` event | ✅ READY |
| Level reading broadcast | `TankReadingReceived` event | ✅ READY |
| Fleet position updates | `FleetPositionUpdated` event | ✅ READY |
| Alarm creation broadcast | `AlarmCreated` event (unauthorized scans) | ✅ READY |
| Private channels | `trissan.live.*` channels | ✅ READY |

---

## Anomaly Detection Integration (PRD §2.C, §2.E)

| PRD Requirement | Implementation | Status |
|---|---|---|
| C1: Sudden tank level change | Job dispatched after transaction | ✅ READY |
| C2: Session reconciliation | Job dispatched after transaction | ✅ READY |
| Detection trigger | `DetectAnomalyJob::dispatch($transaction)` | ✅ READY |
| Unauthorized scan alarms | Created via `ScanRejectionController` | ✅ READY |
| GPS coordinates in alarms | Captured from scan rejection payload | ✅ READY |
| Geofence violation detection | `CheckGeofenceJob` for mobile readings | ✅ READY |

---

## RFID Management (PRD §2.A)

| PRD Requirement | Implementation | Status |
|---|---|---|
| Periodic sync (60s poll) | `GET /api/v1/sync/rfid` | ✅ READY |
| Version-based caching | `X-RFID-Version` header + 304 response | ✅ READY |
| Emergency validation | `POST /api/v1/auth/check` | ✅ READY |
| Fail-closed enforcement | Returns `allow: false` by default | ✅ READY |
| Sector information | Included in sync response | ✅ READY |
| Blocked tag rejection | `status = 'blocked'` returns deny | ✅ READY |

---

## Conclusion

✅ **Backend is 100% ready for hardware integration**

All four device types have complete API coverage matching the v2.5 PRD requirements:
1. **Mobile Units (FMC225)** → AVL ingestion endpoint ready (`/api/internal/ingest/avl`)
2. **Fill Line (RUT956)** → Transaction + RFID endpoints ready (`/api/v1/transactions`)
3. **Dispense Line (RUT956)** → Transaction + RFID endpoints ready (`/api/v1/transactions`)
4. **Main Tank ATG (RUT956)** → Site readings endpoint ready (`/api/v1/site/readings`)

**Next Steps for Deployment:**
1. Deploy TCP gateway service for FMC225.
2. Configure nginx to block `/api/internal/*` from public access.
3. Write/deploy Python scripts for RUT956 units to poll RFID and post transactions.
4. Generate device bearer tokens and test with physical hardware in the staging environment.


---

## API Endpoint Reference

### Mobile Units (FMC225) - Internal AVL Ingestion

**Endpoint:** `POST /api/internal/ingest/avl`  
**Auth:** Internal token (localhost only)  
**Called by:** TCP Gateway service

```json
{
  "imei": "123456789012345",
  "records": [
    {
      "timestamp": "2026-10-08T10:00:00Z",
      "latitude": -6.2088,
      "longitude": 106.8456,
      "satellites": 12,
      "fuel_level_liters": 450.5
    }
  ]
}
```

**Response:**
```json
{
  "accepted": 1,
  "device_id": "123456789012345"
}
```

---

### Fill Line & Dispense Line - Transaction Ingestion

**Endpoint:** `POST /api/v1/transactions`  
**Auth:** `Authorization: Bearer <device_token>` + `X-Device-ID: <device_id>`  
**Called by:** RUT956 Python script

**Fill Line Example (Fuel Tanker → Main Tank):**
```json
{
  "device_txn_id": "550e8400-e29b-41d4-a716-446655440000",
  "device_id": "fill_line_001",
  "transfer_type": "fill_to_main",
  "tag_id": "RFID_FUEL_TANKER_01",
  "tank_id": "fuel_tanker_01",
  "main_tank_id": "main_tank_01",
  "liters": 1500.50,
  "started_at": "2026-10-08T10:00:00Z",
  "ended_at": "2026-10-08T10:15:00Z"
}
```

**Dispense Line Example (Main Tank → Browser Tank):**
```json
{
  "device_txn_id": "550e8400-e29b-41d4-a716-446655440001",
  "device_id": "dispense_line_001",
  "transfer_type": "dispense_to_browser",
  "tag_id": "RFID_BROWSER_TANK_01",
  "tank_id": "browser_tank_01",
  "main_tank_id": "main_tank_01",
  "liters": 450.00,
  "started_at": "2026-10-08T11:00:00Z",
  "ended_at": "2026-10-08T11:05:00Z"
}
```

**Response:**
```json
{
  "id": 123,
  "status": "created"
}
```

---

### Main Tank ATG - Level Readings

**Endpoint:** `POST /api/v1/site/readings`  
**Auth:** `Authorization: Bearer <device_token>` + `X-Device-ID: <device_id>`  
**Called by:** RUT956 Python script (periodic batch)

```json
{
  "readings": [
    {
      "device_id": "main_tank_atg_001",
      "tank_id": "main_tank_01",
      "level_liters": 25000.00,
      "latitude": null,
      "longitude": null,
      "satellites": null,
      "timestamp": "2026-10-08T10:00:00Z"
    },
    {
      "device_id": "main_tank_atg_001",
      "tank_id": "main_tank_01",
      "level_liters": 24998.50,
      "latitude": null,
      "longitude": null,
      "satellites": null,
      "timestamp": "2026-10-08T10:00:30Z"
    }
  ]
}
```

**Response:**
```json
{
  "created": 2,
  "skipped": 0
}
```

---

### RFID Sync

**Endpoint:** `GET /api/v1/sync/rfid`  
**Auth:** `Authorization: Bearer <device_token>`  
**Headers:** `X-RFID-Version: <current_version>`  
**Poll Interval:** Every 60 seconds

**Response (304 Not Modified):** Empty body if version unchanged

**Response (200 OK with new data):**
```json
{
  "version": 5,
  "tags": [
    {
      "tag_id": "RFID_FUEL_TANKER_01",
      "tank_id": "fuel_tanker_01",
      "sector": "fuel_tanker"
    },
    {
      "tag_id": "RFID_BROWSER_TANK_01",
      "tank_id": "browser_tank_01",
      "sector": "browser_tank"
    }
  ]
}
```

---

### Emergency RFID Check

**Endpoint:** `POST /api/v1/auth/check`  
**Auth:** `Authorization: Bearer <device_token>`  
**Used when:** Tag not in local cache

```json
{
  "tag_uid": "UNKNOWN_TAG_123"
}
```

**Response (allowed):**
```json
{
  "allow": true,
  "reason": "Tag is valid",
  "tank_id": "browser_tank_01",
  "sector": "browser_tank"
}
```

**Response (denied):**
```json
{
  "allow": false,
  "reason": "Tag not registered"
}
```

---

### Scan Rejection Logging

**Endpoint:** `POST /api/v1/scan-rejections`  
**Auth:** `Authorization: Bearer <device_token>`  
**Used when:** RFID scan is rejected

```json
{
  "device_event_id": "660e8400-e29b-41d4-a716-446655440000",
  "device_id": "fill_line_001",
  "raw_tag_uid": "BLOCKED_TAG_999",
  "reason": "Tag is blocked",
  "timestamp": "2026-10-08T10:30:00Z",
  "latitude": -6.2088,
  "longitude": 106.8456
}
```

**Response:**
```json
{
  "id": 456,
  "status": "created"
}
```
