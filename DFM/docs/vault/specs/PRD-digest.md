# PRD Fuel Management System — Quick Reference Digest

**Version:** 2.5 | **Tech:** Laravel · MySQL · Teltonika TCP · React · Inertia

## Hardware Units

| Type | Main Board | Reports |
|------|-----------|---------|
| Fill Line (`fill_line`) | RUT956 | RFID (Fuel Tanker) + flow meter |
| Dispense Line (`dispense_line`) | RUT956 | RFID (Browser Tank) + flow meter |
| Mobile Unit (`mobile_unit`) | FMC225 | ATG level + GPS position |
| Main Tank ATG (`main_tank_atg`) | RUT956 | Main Tank level |

## Core API Endpoints

### Device Registration & Auth
- `POST /api/v1/devices/register` — register new device
- `POST /api/v1/auth/check` — emergency RFID validation (fail-closed)

### Data Ingestion
- `POST /api/v1/telemetry` — FMC225 telemetry (TCP gateway)
- `POST /api/v1/transactions` — fill/dispense sessions
- `POST /api/v1/scan-rejections` — unauthorized RFID scans
- `POST /api/v1/site/readings` — Main Tank ATG readings (batched)

### RFID Tag Management
- `GET /api/v1/rfid/list?list_version={n}` — sync RFID whitelist to line units
- `POST /api/v1/rfid/register` — register new RFID tag
- `PATCH /api/v1/rfid/{id}/status` — activate/block tag

### Fleet & Monitoring
- `GET /api/v1/fleet/live` — real-time fleet positions
- `GET /api/v1/alarms?status=active` — active alarms
- `GET /api/v1/dashboard/stats` — dashboard aggregates

## Pages (Inertia Routes)

| Route | Component | Permission |
|-------|-----------|------------|
| `/dashboard` | Dashboard | `view_dashboard` |
| `/fleet-map` | FleetMap | `view_fleet` |
| `/fuel-monitoring` | FuelMonitoring | `view_fuel_data` |
| `/alarms` | Alarms | `view_alarms` |
| `/rfid-tags` | RfidTags | `manage_rfid` |
| `/devices` | Devices | `manage_devices` |
| `/users` | Users | `manage_users` |

## Alarm Types

| Type | Trigger | Priority |
|------|---------|----------|
| `tank_anomaly` | Δlevel vs expected > threshold | HIGH |
| `unauthorized_scan` | Blocked/unknown RFID scan | MEDIUM |
| `device_offline` | No telemetry > timeout | LOW |
| `geofence_violation` | Mobile unit outside boundary | MEDIUM |
| `low_fuel` | Tank level < min_threshold | MEDIUM |

## Permission Keys

**View:** `view_dashboard`, `view_fleet`, `view_fuel_data`, `view_alarms`  
**Manage:** `manage_rfid`, `manage_devices`, `manage_users`, `manage_roles`

## Key Constraints

- All timestamps UTC, display in `sites.timezone`
- Store-and-forward: device timestamp > arrival time for evaluation
- RFID validation fail-closed (deny if server unreachable)
- Anomaly detection window `W` = 30 min (configurable)
- GPS validity: `satellites > 0` = fresh fix (see §7.G)

## Detail Sections in Full PRD

- §2: Database Schema (full tables)
- §3: Transaction & Alarm Lifecycle
- §4: Device Protocols (TCP/HTTPS)
- §5: Frontend Components & State
- §6: Permissions & Multi-Site
- §7: Decision Log & Risk Review
