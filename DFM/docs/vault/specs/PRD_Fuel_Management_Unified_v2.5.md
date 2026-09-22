# Enterprise Fuel Management System — Unified PRD
**Project:** PT. Trissan — Mining Site Fuel Monitoring & Anti-Theft System
**Version:** 2.4 (v2.3 + live fleet map with geofencing, and a correction to how FMC225 GPS validity was modelled)
**Tech Stack:** Laravel (PHP) · MySQL · Teltonika TCP Gateway (Codec 8 Extended, mobile units) · React (Vite) · Inertia.js · Tailwind CSS · Recharts · Laravel Sanctum · Laravel Reverb/Pusher · S3 (backups)

---

## 0. Document Purpose

This document is the single source of truth for the backend and frontend (previously two separate specs) and records the risk review against the requirement that the system operate at *zero-mistake* tolerance in an active mining environment (physical fuel theft, remote connectivity, regulatory audit exposure, safety-critical alerting).

Sections 1–6 are the functional spec (final, not proposals). **Section 7** is the decision log: what was found, what was decided, and why. **v2.1** fixes six inconsistencies found in v2.0 and adds the real hardware model, dynamic roles, alarm lifecycle, registration/replacement flow, and an operational baseline (see §7.E). **v2.2** adapts the spec to the actual hardware platform — Teltonika FMC225 on the trucks and Teltonika routers on the Main Tank lines — and adds GPS coordinates to alarms (see §7.F). **v2.3** removes MQTT: every device link is TCP + TLS — the FMC225 over Teltonika's TCP protocol, the routers over HTTPS (§4, §7.F). **v2.4** adds a live fleet map with geofencing (§2.K) and corrects how a missing GPS fix is detected (§7.G).

**Scope bar:** stable, fast, secure, zero data lost, expandable to more sites and devices — and nothing beyond that. Anything beyond the bar is explicitly deferred (§7.C), not silently dropped.

---

## 1. System Architecture Overview

The Laravel backend is the Source of Truth for business logic, hardware authentication, anomaly detection, and API/WebSocket delivery to the React (Inertia.js) frontend. All timestamps are stored in UTC; display and hourly bucketing use the site's configurable timezone (`sites.timezone`, §3).

### 1.1 Physical Flow & Hardware Inventory

```
Vendor (external, outside the system)
   │ fills
   ▼
Fuel Tanker ──[ Fill Line ]──► Main Tank ──[ Dispense Line ]──► Browser Tank
FMC225+ATG     RUT956          ATG           RUT956               FMC225+ATG
               flow meter +    (via RUT956)  flow meter +
               RFID reader                   RFID on nozzle
```

There are four kinds of hardware, each with a defined main board:

| # | Unit (`device_type`) | Main board | Installed on | Contains | Reports |
|---|---|---|---|---|---|
| 1 | **Fill Line unit** (`fill_line`) — "pipa main fuel tanker" | **Teltonika RUT956** | Pipe from Fuel Tanker into Main Tank | RUT956 + flow meter, directional valve, gate valve, RFID reader | Which Fuel Tanker is unloading (RFID) and how many liters (flow meter) |
| 2 | **Dispense Line unit** (`dispense_line`) — "pipa penyedot" | **Teltonika RUT956** | Fuel dispenser + nozzle, Main Tank → Browser Tank | RUT956 + flow meter, directional valve, gate valve, RFID reader on the nozzle | Which Browser Tank is being filled (RFID) and how many liters (flow meter) |
| 3 | **Mobile unit** (`mobile_unit`) | **Teltonika FMC225** | Every Browser Tank and every Fuel Tanker | FMC225 (built-in GNSS and SIM) + ATG level sensor + RFID tag | Tank level and GPS position over time; its RFID tag identifies the truck at a line |
| 4 | **Main Tank ATG** (`main_tank_atg`) | **Teltonika RUT956** (assumed, A3) | Main Tank | RUT956 + ATG level sensor | Main Tank level over time |

**Main board per device (v2.3):**

| Board | Used by | Role on the unit | Link to the server | `device_id` | Authentication |
|---|---|---|---|---|---|
| **Teltonika FMC225** | `mobile_unit` — every Browser Tank and Fuel Tanker | Built-in GNSS, modem/SIM and flash buffering; reads the ATG sensor. The RFID tag is a separate passive tag on the truck. | TCP + TLS, Teltonika Codec 8 Extended → TCP gateway → Laravel (§4.1) | IMEI | TLS + IMEI allowlist (§7.A) |
| **Teltonika RUT956** | `fill_line`, `dispense_line`, and `main_tank_atg` (A3) | Cellular router with I/O and RS232/RS485. Runs the unit's logic itself as a Python 3 script: RFID check, valve control, flow totalisation, offline queue and retry (§2.A, §4.2). On the Main Tank it reads the ATG. | HTTPS + JSON → Laravel (§4.2) | Device ID configured on the router (its IMEI or serial) | TLS + hashed API token (§7.A) |

The board is implied by `device_type`, so there is no separate column: registering a `mobile_unit` means an FMC225 identified by IMEI, and registering a `fill_line`, `dispense_line` or `main_tank_atg` means a RUT956 identified by its configured device ID (§2.D). The RUT956 units are called **site units**; the FMC225 units are **mobile units**.

Notes:
- A Fuel Tanker is filled by an external vendor. On site it only **unloads into the Main Tank through the Fill Line**. (This replaces v2.0's statement that a Fuel Tanker dispenses to equipment.)
- A Browser Tank is filled from the Main Tank through the Dispense Line and is the **end consumer**: the machine burns the fuel, and by site rule it must never be used to move fuel to another vehicle (§7.E). A fast, significant drop is therefore always flagged (C1).
- Both lines are **fail-closed**: the gate valve stays closed unless a registered, active tag is scanned (§2.A).

### 1.2 Channels

| Channel | Purpose |
|---|---|
| **HTTPS (TLS)** — site units | The Teltonika routers (Fill Line, Dispense Line, Main Tank ATG) call the REST API directly (§5, Edge API). Each call is a request/response and the response is the acknowledgement. No MQTT broker is needed. |
| **TCP + TLS (Teltonika Codec 8 Extended)** — mobile units | FMC225 units on Browser Tanks and Fuel Tankers send AVL records (GPS + ATG level) to the TCP gateway (§4.1). The device buffers records in flash; the server acks by record count. |
| **REST API** | Internal ingest from the TCP gateway; frontend dashboard hydration and admin actions (Sanctum). |
| **WebSocket (private channels, Laravel Reverb/Pusher)** | `trissan.live.main-tank`, `trissan.live.mobile-tanks`, `trissan.live.alarms`, `trissan.live.map` — each authorized by the matching view permission (§2.F). Replaces v2.0's single `trissan.live.updates`. |

### 1.3 Ingestion, Scale & Operations Baseline

- **Ingestion — two paths, one pipeline.** (a) *Site units* (HTTPS): the Edge API endpoints in §5 — authenticate → dedup → commit to MySQL → respond `2xx` (the acknowledgement, §2.B). (b) *Mobile units* (Teltonika TCP): a small standalone **TCP gateway** service (§4.1) that answers the IMEI handshake, parses Codec 8 Extended, posts the records to Laravel's internal ingest endpoint, and acks the device with the record count only after Laravel confirms the commit. Both paths end in the same ingest service, so dedup and detection (§2.C, queued jobs) are identical. More app workers or gateway instances can be added as the fleet grows.
- **Scale.** One site now; schema and endpoints are site-ready (`site_id`). Initial fleet is at least 7 trucks (Browser Tank + Fuel Tanker) and the device count is not fixed, so registration is fully dynamic (§2.D). Level readings arrive about every 30 s per device (set on the device, §3). Rough load, assuming one record per truck every 30 s: about 0.3 requests/s and 23 k reading rows/day for 7 trucks and one Main Tank; about 2 requests/s and 150 k rows/day for 50 trucks, 3 Main Tanks and 6 line units — comfortably within one Laravel + MySQL server given the monthly partitions in §3. This is an estimate to re-measure on site; the real limit is cellular coverage, not the protocol.
- **Backup.** Scheduled server-side MySQL backups to any S3-compatible storage (AWS S3, Cloudflare R2, MinIO, …), configured, tested and triggered from the web app (§2.H); daily full is the proposed baseline. Restore is a manual procedure that should be rehearsed periodically. Clustering/HA-DR stays Phase 2 (§7.C).
- **Timezone.** Stored in UTC; the site timezone is editable by admins (Settings page).

---

## 2. Core Business Logic & Enterprise Workflows

### A. RFID Synchronization & Scan Enforcement
1. **Sync by polling.** Each line unit (Fill Line, Dispense Line) polls `GET /api/v1/sync/rfid?version=<current>` every `rfid_poll_seconds` (default 60). If the version is unchanged the server answers `304 Not Modified`; otherwise it returns the new version and a lightweight CSV/plain-text list of `active` tags, which the unit stores locally. The version (`sites.rfid_list_version`) is bumped on any tag change (register, assign, block, replace), so a change reaches every line unit within one poll interval. Mobile units only carry a tag; they don't read tags and don't sync.
2. **Scan enforcement (fail-closed).** On each line the gate valve is closed by default. On scan:
   - tag in the local list → the valve may open and a session starts;
   - tag not in the local list → emergency validation via `POST /api/v1/auth/check`; the response is allow or deny (allow only if the tag is `active`);
   - unregistered tag, `blocked` tag, server denial, or no server response (offline) → **rejected, valve stays closed**.
   Every rejected scan is sent to `POST /api/v1/scan-rejections` (queued and retried until acknowledged, §2.B) and recorded as an `unauthorized_scan` entry in the Alarm Log (§3).
3. One session = valve open → valve closed. On close, the line unit logs one transaction (`POST /api/v1/transactions`, §3).
4. Steps 1–3 run on the RUT956 itself — RFID check (local list first, server for unknown tags), gate and directional valve control, flow totalisation, and the offline queue with retry — as a Python 3 script (§4.2, §7.F). There is no separate controller.

### B. Store-and-Forward, Time Sync & Acknowledgement (Blank Spot Handling)
1. Every record is stamped by the device clock (UTC): the FMC225 from GNSS/network time, the routers via NTP. The server processes and evaluates by **device timestamp, not arrival time**, so backlog uploads are evaluated correctly.
2. **Mobile units (FMC225, TCP).** The device buffers records in its own flash while it has no signal and resends them on reconnect. The protocol's acknowledgement is the number of records the server accepted, and the device treats records as delivered only when that number comes back. The TCP gateway therefore sends it **only after Laravel has committed those records** (or found them already stored through the `(device_id, timestamp)` unique key). Duplicates from retries are harmless. Every record also carries a `satellites` count from the GPS Element, used to tell a fresh GPS fix from a repeated stale one (§7.G).
3. **Site units (routers, HTTPS).** The acknowledgement is the HTTP response: the server answers `2xx` only after the row is committed (or found already stored through the dedup key). The unit deletes a queued item only after that `2xx`; on a timeout, a `5xx` or no signal it retries later with the **same** `device_txn_id` (safe because of the unique constraint in §3). A `401` means the device is not registered or has been retired; any other `4xx` means the request itself is wrong, so it is logged and not retried blindly.
4. Site-unit transactions and rejected scans are sent individually. Main Tank ATG readings are posted to `POST /api/v1/site/readings` in small batches (each with a `batch_id`); one `2xx` acks the batch, and a batch that fails is kept and retried.

### C. Anomaly Detection — Two Separate Mechanisms (server-side)

All detection runs on the server against stored data; Edge devices report, they never decide theft. The two mechanisms are kept apart because they answer different questions with different confidence levels. Cross-device reconciliation is indirect and noisy; it should never be the thing that accuses someone of theft.

**Shared definitions**
- **Window `W`** (default 30 min, configurable).
- **`Δlevel`** of a tank = level at `t` minus level at `t0`, where `t0` is the latest stored reading at or before `t − W`.
- **`expected`** = signed sum of the transaction volumes for that tank inside `(t0, t]` (sessions that straddle a window edge are pro-rated by time overlap). Rises (+): `dispense_to_browser` on a Browser Tank, `vendor_fill` on a Fuel Tanker, `fill_to_main` on the Main Tank. Drops (−): `fill_to_main` on a Fuel Tanker, `dispense_to_browser` on the Main Tank.
- **`unexplained %`** = `|Δlevel − expected|` ÷ the tank's `capacity_liters` × 100.

**C1. Sudden Change Detection — this is the theft signal.**
Per tank (Main Tank, each Browser Tank, each Fuel Tanker) — never compares against another tank.
- If `unexplained %` ≥ the theft threshold for the tank's division under the active sensitivity mode (§2.G) (drop *or* rise) → `Theft Alarm` (`sudden_change_theft`), status `open`, pushed on `trissan.live.alarms`.
- A window is evaluated only after a grace period (`eval_grace_minutes`, default 10) so a transfer still in progress has already been logged. Late-arriving backlog is evaluated when it arrives.
- One alarm per event: no new `sudden_change_theft` row for a tank while an earlier one for that tank is younger than one window.
- Every alarm on a mobile tank carries a one-time GPS snapshot of the truck (§2.J).
- After a hardware replacement (§2.D) the baseline restarts from the new device's first reading.
- Example: Browser Tank (capacity 500 L) reads 100 L at 13:00 and 50 L at 13:30 with no matching transaction. 50 L = 10% of capacity → Theft Alarm under Medium (10%) and High (5%); under Low (15% = 75 L) it does not trigger (§2.G). Ordinary fuel burn is gradual and stays below the threshold; moving fuel out of a Browser Tank to another vehicle is prohibited by site rule, so a fast, significant drop is flagged by design.
- Vendor fills: a Fuel Tanker's rise after a vendor fill is covered by its `vendor_fill` entry (§2.D). An unrecorded vendor fill is an unexplained rise and will raise a Theft Alarm; the admin closes it as `false_positive` with a note ("vendor fill not recorded"). Recording vendor fills promptly avoids this.

**C2. Transfer Reconciliation — this is a calibration signal, never a theft signal.**
For each completed Fill Line / Dispense Line session, compare the flow-meter volume (reference) with the mobile tank's ATG change over the session.
- **Pairing:** RFID tag → tank, plus the session's time window `[started_at, ended_at + settle_seconds]` (default 60 s so the ATG can settle). The transaction row *is* the session and its `tag_id` identifies the truck; the mobile unit is passive at the line (only its tag is read), so it can't carry a session id. Pairing is an indexed lookup on `tank_level_readings(tank_id, timestamp)`, so it scales with the number of devices.
- **`variance %`** = `|ATG change − expected change|` ÷ flow-meter liters × 100, where expected change = +liters (Dispense: Browser Tank rises) or −liters (Fill: Fuel Tanker drops).

| Variance | Action |
|---|---|
| < calibration tolerance of the active sensitivity mode (§2.G; Medium = 1%) | Logged as valid, no action |
| ≥ tolerance | `Sensor Needs Calibration` (`calibration_needed`), status `open`, shown amber in the Alarm Log for maintenance — **regardless of how large the variance is, it never escalates to a Theft Alarm.** Variance reflects sensor/temperature/timing noise, not evidence of a specific event, so it can't responsibly be used to accuse anyone. |

- The alarm references both the mobile unit and the line unit involved; maintenance decides which one is off.
- C2 is skipped for a session if a C1 Theft Alarm already covers that tank and session, and it never applies to `vendor_fill` (no flow meter; the value is declared).
- Example: the Fill Line flow meter reports 40 L but the Fuel Tanker's ATG dropped 50 L. C1 sees 10 L unexplained (far below the theft threshold → no theft). C2 sees 25% variance → `Sensor Needs Calibration`.

### D. Registration, Assignment & Hardware Replacement
1. **Site.** One site is seeded now; its `timezone` is editable in Settings.
2. **Register a tank.** Admin enters `name`, `capacity_liters` and `division` (dropdown: `main_tank` / `browser_tank` / `fuel_tanker`). `division` drives the C1 threshold, the Page 2 split, and whether vendor fills apply (Fuel Tanker only). The Main Tank is registered the same way, so it has a capacity too.
3. **Register hardware.** Admin enters the `device_id` and `device_type`: the **IMEI** for an FMC225 (`mobile_unit`), or the device ID configured on the router (its IMEI or serial) for a site unit. For a `mobile_unit`, the RFID tag bundled with it is registered in the same step (already paired physically). For site units the server generates an `api_token` (shown once, stored only as a hash, regenerable); FMC225 units have no token and are admitted by IMEI (§7.A). New devices start as `spare` (the gateway accepts a spare FMC225's IMEI so it can be provisioned); the tag is created `blocked` and is unusable until assigned.
4. **Assign.** Pick a `spare` device for a tank → sets `hardware_devices.tank_id` and status `active`; for a mobile unit its tag is linked to the tank and set `active`; the RFID list version is bumped and published. Rules: each Browser Tank / Fuel Tanker has exactly one mobile unit; the Main Tank has one ATG and one or more Fill/Dispense Line units.
5. **Replace hardware.** "Replace unit" on a tank runs in one DB transaction: the old device → `retired` (rejected by the gateway/API from now on, `tank_id` cleared) and its tag → `blocked`; the new spare device and tag are assigned as in step 4; the RFID version is bumped. History stays with the tank because `transactions`, `tank_level_readings` and `anomaly_logs` each store `tank_id` at write time, so a device change never splits a truck's history. The C1 baseline restarts from the new device's first reading.
6. **Visibility.** Page 2 lists **all registered tanks** (from `tanks`; "no data yet" until the first reading). Register/assign/replace broadcast a `tank.updated` event on `trissan.live.mobile-tanks` (and `trissan.live.main-tank` for Main Tank changes) so pages update without a reload. This corrects v2.0, whose transaction-driven channel would never show a tank that had no transactions.
7. **Vendor fill.** On a Fuel Tanker row, "Record vendor fill" (permission `vendor_fill.create`) takes the declared liters (default = `capacity_liters`, editable) and the fill time, and creates a `vendor_fill` transaction (manual, `entered_by`). It is not sensor-verified and is skipped by C2.

### E. Alarm Lifecycle
- **Status** (`status_investigasi`): `open` (on creation) → `investigating` → `resolved` or `false_positive`. Closing as `resolved` or `false_positive` requires a `resolution_note`. Only roles with `alarms.manage` (Admin and Super Admin by default) can change status. This applies to all three alarm types.
- **Read state is per user** (`anomaly_reads`): an alarm stays unread for a user until that user opens it (`PATCH …/read`). Read is not the same as resolved.
- **Notification is website-only**: WebSocket push, a toast, and an unread badge in the navigation. No external channels (WhatsApp/Telegram/email/SMS) in this version (§7.C).

### F. Users, Roles & Permissions
- Roles are dynamic: a Super Admin creates a role and ticks permissions from a per-feature checklist; changes apply immediately. **Super Admin** is built-in, non-deletable, holds every permission, and by default is the only role that manages users, roles, alert sensitivity, backup storage and geofences.
- Permission catalog (extended by adding keys as features are added):

| Feature | Permission keys |
|---|---|
| Pages | `main_tank.view`, `mobile_tanks.view`, `alarms.view`, `map.view` (§2.K) |
| Alarms | `alarms.manage` (change status, resolve) |
| Fleet | `tanks.manage` (register tanks), `hardware.manage` (register / assign / replace), `rfid.manage` (block / unblock tags), `vendor_fill.create` |
| System | `settings.manage` (timezone and other tunables), `sensitivity.manage` (alert sensitivity, §2.G), `backup.manage` (backup storage, §2.H), `geofences.manage` (draw/edit geofences, §2.K), `users.manage`, `roles.manage` |
| Reports | `reports.export` (CSV export, §2.I; the user also needs the view permission of the data being exported) |

- Seeded defaults (editable): **Super Admin** — everything. **Admin** — everything except `users.manage`, `roles.manage`, `sensitivity.manage`, `backup.manage` and `geofences.manage`.
- Enforcement: the backend (Laravel Gates/Policies on the permission key) is authoritative; the frontend hides pages and buttons using the permission list shared through Inertia props; WebSocket channels authorize with the same keys.

### G. Alert Sensitivity (Low / Medium / High)
Applies to every threshold-based alert: **Theft Alarm** (C1) and **Sensor Needs Calibration** (C2). `Unauthorized Scan` has no threshold — every rejected scan is always logged — so sensitivity does not apply to it.

- **Modes.** *High* = more sensitive (lower thresholds: catches smaller losses, more false positives). *Low* = less sensitive (higher thresholds: fewer alarms, smaller losses pass unflagged). *Medium* = balanced default. Each alert type has its **own active mode** (e.g., Theft = High while Calibration = Medium).
- **Only the thresholds change between modes.** Window, grace period and settle time (§3) are identical in every mode, so modes differ by one variable and are easy to compare.
- **Editable by the Super Admin** (permission `sensitivity.manage`): switch the active mode per alert type and edit every Low / Medium / High value. Validation: each value is > 0 and ≤ 100, and per division Low > Medium > High so the modes stay ordered. "Reset to presets" restores the values below. The UI asks for confirmation when a change makes detection *less* sensitive.
- **Effect.** A change applies to windows and sessions evaluated after it; existing alarms are not re-evaluated. Each alarm stores the mode and threshold it fired under (`sensitivity_mode`, `threshold_percent`), and the change itself is recorded with user and time (`sites.settings_updated_by` / `settings_updated_at`).
- **Calculation preview.** The sensitivity page shows, for every registered tank, what each mode means in liters, so the trade-off is visible before saving.

**Formulas**
- C1 trigger (liters) = `threshold_percent × capacity_liters ÷ 100` — the smallest unexplained level change in one window that raises a Theft Alarm.
- C2 tolerance (liters) = `tolerance_percent × flow-meter liters ÷ 100` — the largest ATG-vs-flow-meter gap still accepted as valid.

**Preset values** (starting values, assumption A2 in §7.E):

| Alert / parameter | Low | Medium (default) | High |
|---|---|---|---|
| Theft — Browser Tank threshold | 15% | 10% | 5% |
| Theft — Fuel Tanker threshold | 15% | 10% | 5% |
| Theft — Main Tank threshold | 4% | 2% | 1% |
| Calibration — C2 tolerance | 2% | 1% | 0.5% |

**Worked example** (illustrative capacities; the page uses the real registered tanks):

| Case | Low | Medium | High |
|---|---|---|---|
| Browser Tank 500 L — unexplained change that triggers | 75 L | 50 L | 25 L |
| Fuel Tanker 10,000 L — unexplained change that triggers | 1,500 L | 1,000 L | 500 L |
| Main Tank 20,000 L — unexplained change that triggers | 800 L | 400 L | 200 L |
| C2 tolerance on a 500 L transfer | 10 L | 5 L | 2.5 L |
| Browser Tank 500 L drops 100 L → 50 L, no transaction (50 L unexplained) | no alarm | Theft Alarm | Theft Alarm |
| Flow meter 40 L but ATG dropped 50 L (25% variance) | Sensor Needs Calibration | Sensor Needs Calibration | Sensor Needs Calibration |

### H. Backup Storage (S3-compatible, configured in the web app)
Backups go to any **S3-compatible** object storage, configured entirely from the web app (`/admin/backup`, permission `backup.manage`, Super Admin by default) — no `.env` edit and no redeploy.

- **Providers:** anything that speaks the S3 API — AWS S3, Cloudflare R2, MinIO, Wasabi, Backblaze B2, DigitalOcean Spaces, and similar. The provider dropdown only prefills hints (e.g., R2: region `auto`, endpoint `https://<account_id>.r2.cloudflarestorage.com`); every provider uses the same S3 client, so adding one is a form entry, not new code. Storage that is not S3-compatible is out of scope (§7.C).
- **Form fields:** provider, endpoint, region, bucket, path prefix, access key ID, secret access key, and a "path-style URLs" toggle (needed by some self-hosted stores such as MinIO).

**Setup flow — each step gates the next**
1. **Save** the storage settings.
2. **Test Connection** — reports pass/fail per check with a readable error: endpoint reachable over HTTPS → credentials accepted → bucket accessible → a small marker object is written, read back and deleted (proves the key has the permissions the backup needs).
3. **Run Test Backup** — runs the real backup job once (dump → compress → upload → verify) and shows file name, size and duration.
4. **Enable schedule** — available only after steps 2 and 3 have passed since the last change to the storage settings (changing any storage field resets both checks). Set the run time (site timezone; daily by default) and `retention_days` (default 30).

Afterwards, **Run Backup Now** runs the same job on demand.

**Backup job.** Consistent `mysqldump` (InnoDB snapshot, no table lock) → gzip → upload through Laravel's S3 filesystem driver, built at runtime from the saved settings → verify the object exists with the expected size → record in `backup_runs`. Files are stored under `{path_prefix}/{site}/{YYYY}/{MM}/`. Retention deletes only this app's files older than `retention_days`, and never deletes the last successful backup.

**Visibility.** Run history is on the same page. If the last scheduled run failed, or no backup has succeeded for more than 2 days, users with `backup.manage` see a warning banner (website notification only, consistent with §2.E).

**Security**
- The access key and secret are stored encrypted at rest; the secret is never returned to the browser after saving (shown masked; entering a new one replaces it). HTTPS endpoints only.
- Recommended: a key scoped to that one bucket (read, write, delete, list).
- **Keep `APP_KEY` outside the database and outside the backup bucket** (secrets manager or an offline copy). Encrypted values in a restored database — the backup credentials, for example — cannot be read without it. (Device API tokens are stored as hashes, so device authentication does not depend on `APP_KEY`.)
- Restore stays a manual procedure (download from the bucket, import). A restore button in the UI is deferred (§7.C).

### I. CSV Export (Reports)
Users export data to CSV from the web app (`/reports`). Permission: `reports.export`, **plus** the view permission of the data being exported (e.g., `alarms.view` for the Alarm Log). The Main Tank, Mobile Tanks and Alarm Log pages each have an **Export CSV** button that opens Reports with the report and filters prefilled.

| # | Report | One row per | Columns | Filters |
|---|---|---|---|---|
| 1 | **Main Tank — Transactions** | Fill Line / Dispense Line session on the Main Tank | `started_at`, `ended_at`, `main_tank`, `direction` (In / Out), `tank` (Fuel Tanker or Browser Tank), `division`, `rfid_tag`, `line_device_id`, `liters`, `sync_status` | date range, Main Tank, direction, tank |
| 2 | **Main Tank — Daily Reconciliation** | Main Tank per day | `date`, `main_tank`, `opening_level_l`, `received_l`, `dispensed_l`, `expected_closing_l`, `closing_level_l`, `variance_l`, `variance_percent`, `is_complete` | date range, Main Tank |
| 3 | **Browser Tank — Refuels** | Dispense Line session | `started_at`, `ended_at`, `browser_tank`, `rfid_tag`, `liters`, `main_tank`, `line_device_id`, `sync_status` | date range, Browser Tank(s) |
| 4 | **Browser Tank — Daily Consumption** | Browser Tank per day | `date`, `browser_tank`, `refuel_count`, `liters_received` | date range, Browser Tank(s) |
| 5 | **Fuel Tanker — Unloads & Vendor Fills** | Fill Line session or vendor fill | `started_at`, `ended_at`, `fuel_tanker`, `type` (Unload to Main Tank / Vendor fill), `rfid_tag`, `liters`, `main_tank`, `line_device_id`, `entered_by`, `sync_status` | date range, Fuel Tanker(s), type |
| 6 | **Alarm Log** | Alarm | `anomaly_time`, `type`, `tank`, `division`, `rfid_tag`, `volume_diff_l`, `observed_percent`, `threshold_percent`, `sensitivity_mode`, `status`, `resolution_note`, `resolved_by`, `resolved_at`, `latitude`, `longitude` | date range, type, status, tank |
| 7 | **Tank Level Readings** | ATG reading | `timestamp`, `tank`, `level_l`, `latitude`, `longitude`, `device_id` | one tank (required), date range |

Cells that don't apply are left blank (e.g., `entered_by` on a Fill Line unload). For an `Unauthorized Scan`, `rfid_tag` holds the raw tag UID.

**Definitions**
- **Daily Reconciliation** (report only — it never raises an alarm; theft and calibration detection stay with C1/C2):
  - `opening_level_l` / `closing_level_l` = latest ATG reading at or before 00:00 / 24:00 site time of that day (current day: latest reading, `is_complete = false`); blank if no reading exists.
  - `received_l` = Σ `fill_to_main` liters and `dispensed_l` = Σ `dispense_to_browser` liters, counted on the day the session ended (a session crossing midnight can leave a small edge effect).
  - `expected_closing_l` = opening + received − dispensed; `variance_l` = closing − expected; `variance_percent` = `variance_l` ÷ tank capacity × 100.
- **Daily Consumption:** `liters_received` = Σ `dispense_to_browser` liters that day — the fuel supplied to that unit, which is its consumption figure because a Browser Tank is the end consumer (§7.E).

**Export rules**
- **Range limits:** reports 1–6 up to 366 days per export; report 7 is one tank and up to 31 days (raw readings are kept for `reading_retention_days`, default 90).
- **Time:** timestamps in the site timezone as `YYYY-MM-DD HH:mm:ss`; the timezone name is in the file name: `{report}_{from}_to_{to}_{timezone}.csv`.
- **Format preset** (dropdown): *Standard* — comma delimiter, decimal point. *Excel (Indonesia)* — semicolon delimiter, decimal comma, so the file opens in proper columns with correct numbers on an Indonesian-locale Excel. Both: UTF-8 with BOM, header row, volumes with 2 decimals, no thousands separators.
- **Delivery:** streamed download using chunked queries — no queue and no temp file.
- **Safety:** text fields (tank names, resolution notes, etc.) that start with `=`, `+`, `-` or `@` are prefixed with `'` so Excel cannot run them as formulas; numeric columns are never altered.
- **Audit:** every export is recorded in `export_logs` (who, which report, filters, row count, when).

### J. Alarm Location (GPS)
Each FMC225 record carries the truck's GNSS position, a satellite count and the ATG value at the same timestamp, so the position is stored with every level reading (`tank_level_readings.latitude` / `longitude` / `satellites`) — no separate tracking feed.
- **Snapshot, not tracking.** When an alarm is created for a Browser Tank or Fuel Tanker (`sudden_change_theft` or `calibration_needed`), it stores the coordinates from the latest reading at or before the alarm's window end (`anomaly_time`), **once**, with that reading's time (`position_time`). Nothing is updated afterwards.
- **Validity (corrected in v2.4, §7.G).** The FMC225 does not send an empty position when it has no GPS fix — it resends the last valid fix, stamped with the current time. Validity is judged from the `satellites` count on the reading (`satellites > 0`, §7.G), not from the coordinates being present. The snapshot uses the latest reading with `satellites > 0` at or before the alarm's `anomaly_time`; if that reading is missing or older than `position_max_age_minutes` (default 15), the alarm shows "position unavailable".
- **Not applicable.** Alarms on the Main Tank (fixed location) and `unauthorized_scan` (no truck identified) carry no coordinates.
- **Display.** The Alarm Log shows `lat, lon` and an **Open in map** link (`https://www.google.com/maps?q={lat},{lon}`); there is no embedded map in this version. CSV exports include the coordinates.
- The change itself happened somewhere inside the preceding window. The per-reading coordinates already hold the route for that window, so a route replay can be added later (§7.C) without collecting new data.

### K. Geofencing & Live Fleet Map
A **geofence** is a boundary an admin draws on a map. Every Browser Tank and Fuel Tanker is checked against the active geofences that apply to its division on every valid GPS reading, and the truck leaving a geofence raises an alarm. Main Tank ATG units and Fill/Dispense Line units are fixed installations and are never checked.

- **Definition.** A geofence is a polygon (`geofences.boundary`, MySQL `POLYGON`), created by drawing it on the map (`/fuel-monitoring/map`, `geofences.manage`) and saved with a name and which division(s) it applies to (`browser_tank`, `fuel_tanker`, or both — default both). A site can have more than one geofence (e.g., the overall site boundary plus a smaller zone); each is evaluated independently. `is_active` lets a geofence be disabled without deleting it (its history stays on past alarms).
- **Evaluation (server-side, same pipeline as C1/C2).** On every incoming FMC225 reading with a valid fix (`satellites > 0`, §7.G), the ingest job checks the point against each active geofence that applies to the tank's division, using MySQL's `ST_Within` (`tank_level_readings.latitude/longitude` as `POINT`, SRID 4326, against `geofences.boundary`). This never runs against a stale repeated fix, so a truck parked with no signal doesn't spuriously "exit" the moment its old coordinate is judged against a newly drawn boundary.
- **Debounce.** A single reading outside is not enough — GPS jitter near a boundary would otherwise cause repeated false alerts. A tank must have `geofence_exit_confirm_readings` (default 2) **consecutive** valid readings outside a geofence before the state flips to outside and an alarm is raised; the same count of consecutive valid readings inside flips it back. Current state per tank/geofence is tracked in `tank_geofence_state` so the check is O(1) per reading, not a re-scan of history.
- **Alarm.** Crossing outside raises `geofence_exit` (status `open`, pushed on `trissan.live.alarms`, shown in the Alarm Log with a slate badge like `unauthorized_scan` — a location event, not a volume anomaly) with the geofence name and the confirming reading's coordinates as `latitude`/`longitude` (§2.J's snapshot model, reused). It follows the same lifecycle as every other alarm (§2.E) and is closed manually by `alarms.manage`; when the tank re-enters, the system appends a note to the still-open alarm ("re-entered {geofence} at {time}") rather than auto-resolving it, so the resolution stays a deliberate action.
- **No threshold in §2.G.** Geofencing is a boundary crossing, not a percentage, so it sits outside the Low/Medium/High sensitivity model; `geofence_exit_confirm_readings` is a single tunable in Settings instead.
- **Live Fleet Map** (`/fuel-monitoring/map`, `map.view`): every Browser Tank and Fuel Tanker plotted on a Leaflet/OpenStreetMap map from its latest valid reading, with active geofence boundaries drawn as overlays; markers move live as new readings arrive over `trissan.live.map` (no polling). A marker for a tank whose latest valid fix is older than `position_max_age_minutes` is shown muted with a "signal lost" label rather than silently freezing in place. Drawing and editing geofences (`geofences.manage`) happens on the same map.
- **Reuses existing data.** No new device configuration and no new ingest path — the coordinates and (from v2.4) the satellite count were already being collected for §2.J; geofencing and the live map are a read of the same `tank_level_readings` stream plus one evaluation step.

---

## 3. Database Schema (MySQL)

Physical hardware identity (`device_id`) is decoupled from logical identity (Tank/RFID), and every fact row stores the `tank_id` it belongs to at write time, so field hardware swaps don't corrupt history.

- **`sites`**: `site_id` (PK), `name`, `timezone` (IANA name, admin-editable), `rfid_list_version`, `settings` (JSON — tunables and sensitivity presets below), `settings_updated_by`, `settings_updated_at`
- **`tanks`** (replaces v2.0 `vehicles`): `tank_id` (PK), `site_id` (FK), `name`, `division` (enum: `main_tank` / `browser_tank` / `fuel_tanker`), `capacity_liters`; soft-deleted when decommissioned
- **`hardware_devices`**: `device_id` (PK — the IMEI for an FMC225, the router's device ID for a site unit), `device_type` (enum: `main_tank_atg` / `fill_line` / `dispense_line` / `mobile_unit`), `tank_id` (FK, nullable — the tank it is attached to; NULL while spare/retired), `status` (`spare` / `active` / `retired`), `api_token_hash` (SHA-256 hash of the site unit's API token — the token itself is shown once; NULL for FMC225 — §7.A), `last_seen`
- **`rfid_tags`**: `tag_id` (PK), `tank_id` (FK, nullable until assigned), `sector`, `status` (`active` / `blocked`)
- **`transactions`** — one row per completed line session or vendor fill: `id`, `device_txn_id` (UUID generated by the Edge device — dedup key for retries; server-generated for manual entries), `device_id` (line unit; NULL for `vendor_fill`), `transfer_type` (enum: `fill_to_main` / `dispense_to_browser` / `vendor_fill`), `tag_id`, `tank_id` (the Browser Tank / Fuel Tanker involved, resolved from the tag at write time), `main_tank_id` (NULL for `vendor_fill`), `liters` `DECIMAL(10,2)`, `started_at`, `ended_at` (device RTC, UTC), `server_received_at`, `rtc_out_of_bounds` (boolean, §7.B), `sync_status` (`live` / `backfilled` — informational: arrived in real time vs. from the offline queue), `entered_by` (user; manual entries only)
  - Unique constraint `(device_id, device_txn_id)` — prevents a double write when a retry is delivered twice. Indexes `(tank_id, started_at)` and `(main_tank_id, ended_at)`.
  - Page 1's `liter_masuk` = Σ `fill_to_main` and `liter_keluar` = Σ `dispense_to_browser`, computed in the API resource (§5).
- **`tank_level_readings`**: `id`, `tank_id`, `device_id`, `level_liters` `DECIMAL(10,2)`, `latitude`, `longitude` (`DECIMAL(9,6)`, nullable — the FMC225's GNSS fix; NULL only for a device with no GPS, i.e. a Main Tank ATG on a RUT956), `satellites` (`TINYINT UNSIGNED`, nullable — visible-satellite count from the same GPS Element; `0` or NULL means the coordinates are a repeated stale fix, not a fresh one — §7.G), `timestamp` (device clock, UTC), `server_received_at`, `rtc_out_of_bounds`. Unique `(device_id, timestamp)` (idempotent re-delivery); index `(tank_id, timestamp)`; **`SPATIAL INDEX` on a generated `POINT(longitude, latitude)` column, SRID 4326** (§2.K). Partitioned by month; raw rows purged after `reading_retention_days` so the purge is a partition drop.
- **`anomaly_logs`**: `id`, `anomaly_type` (enum: `sudden_change_theft` / `calibration_needed` / `unauthorized_scan` / `geofence_exit` — drives the UI badge in §6), `tank_id` (nullable — NULL for an unregistered tag), `device_id`, `tag_id` (nullable), `transaction_id` (nullable — the session, for C2), `geofence_id` (nullable — the geofence exited, for `geofence_exit`), `device_event_id` (nullable UUID — rejected-scan dedup; unique with `device_id`), `volume_diff` `DECIMAL(10,2)`, `observed_percent`, `threshold_percent` (the configured threshold that was applied), `sensitivity_mode` (`low` / `medium` / `high` — the mode active when it fired; NULL for `unauthorized_scan`), `anomaly_time`, `latitude`, `longitude`, `position_time` (one-time GPS snapshot, §2.J; NULL when not applicable or unavailable), `status_investigasi` (`open` / `investigating` / `resolved` / `false_positive`), `resolution_note`, `resolved_by`, `resolved_at`, `meta` (JSON — e.g., line device ID, raw tag UID, rejection reason)
- **`anomaly_reads`**: `anomaly_id`, `user_id`, `read_at` — PK `(anomaly_id, user_id)`
- **`geofences`**: `id`, `site_id` (FK), `name`, `boundary` `POLYGON NOT NULL SRID 4326` with a `SPATIAL INDEX`, `applies_to` (SET: `browser_tank`, `fuel_tanker` — default both), `is_active`, `created_by`, `updated_by`, `updated_at` (§2.K)
- **`tank_geofence_state`**: `tank_id`, `geofence_id` — PK `(tank_id, geofence_id)` — `is_inside`, `consecutive_count` (readings agreeing with the pending state, toward `geofence_exit_confirm_readings`), `updated_at` (§2.K)
- **`users`** (Laravel default + `role_id`), **`roles`** (`role_id`, `name`, `is_system` — true for Super Admin), **`role_permissions`** (`role_id`, `permission_key`). The permission catalog is defined in code (§2.F), not in a table.
- **`backup_settings`** (one row per site): `site_id` (FK), `provider` (label only), `endpoint`, `region`, `bucket`, `path_prefix`, `use_path_style` (boolean), `access_key_id` (encrypted), `secret_access_key` (encrypted), `schedule_time` (site-local time, daily), `retention_days` (default 30), `enabled`, `last_connection_test_at`, `last_connection_test_ok`, `updated_by`, `updated_at`
- **`backup_runs`**: `id`, `site_id`, `trigger` (`scheduled` / `manual` / `test`), `status` (`running` / `success` / `failed`), `started_at`, `finished_at`, `size_bytes`, `object_key`, `error_message`, `triggered_by` (user; NULL when scheduled)
- **`export_logs`**: `id`, `user_id`, `report_type`, `filters` (JSON), `format_preset` (`standard` / `excel_id`), `row_count` (filled in when the export completes), `created_at`

**`sites.settings` tunables** (starting values — see assumption A2, §7.E):

| Key | Default | Meaning |
|---|---|---|
| `window_minutes` | 30 | C1 comparison window |
| `sensitivity` | active mode `medium` per alert type; preset values in §2.G | Low / Medium / High thresholds for C1 (per division) and C2 — editable by Super Admin |
| `eval_grace_minutes` | 10 | Delay before a window is evaluated; must exceed the longest normal session |
| `settle_seconds` | 60 | ATG settle time after a session ends |
| `reading_interval_seconds` | 30 | Expected level-reading interval (configured on the device; informational) |
| `position_max_age_minutes` | 15 | Oldest GPS fix still used for an alarm's location snapshot (§2.J) |
| `rfid_poll_seconds` | 60 | How often line units poll for RFID list changes (§2.A) |
| `geofence_exit_confirm_readings` | 2 | Consecutive valid readings outside (or back inside) a geofence before the alarm/state flips (§2.K) |
| `reading_retention_days` | 90 | Retention of raw level readings (transactions and alarms are kept) |

**Note on table naming:** the Frontend spec's `main_tank_logs` / `mobile_tank_logs` / `alarm_logs` are not separate tables — they are API resource shapes returned by the endpoints in §5, built from `transactions`, `tank_level_readings` and `anomaly_logs`. One schema, no duplication.

---

## 4. Device Protocols

Two device families, one transport family (TCP + TLS), one ingest pipeline (§1.3). No MQTT broker is needed.

### 4.1 Mobile units — Teltonika FMC225 over TCP (Codec 8 Extended)
**Why TCP.** Per Teltonika's documentation the FM/FMC devices send data over TCP, UDP or MQTT, but MQTT is documented only through AWS IoT (or a custom server modelled on AWS's protocol), and the FMC configurator offers AWS and Azure as MQTT client types — a self-hosted broker is not a supported path. The generic MQTT mode that appears on some firmware has no TLS. TCP is Teltonika's primary path: TLS is supported, and its record-count acknowledgement gives exactly the zero-data-loss guarantee of §2.B.
- **Device configuration** (Teltonika Configurator / FOTA Web): gateway domain and port, protocol TCP, Codec 8 Extended, the gateway's TLS certificate uploaded to the device, a data-acquisition interval of about 30 s, and the GNSS and ATG I/O elements enabled.
- **Handshake.** The device sends its IMEI. The gateway accepts (01) only if the IMEI belongs to a registered, non-retired `mobile_unit`; otherwise it rejects (00).
- **Data.** The gateway parses each AVL packet into records (timestamp, latitude, longitude, **satellites**, ATG value) — `satellites` is a fixed field of the GPS Element, not an optional I/O element, so no extra device configuration is needed. It converts the ATG value to liters (A1) and posts the records to Laravel's internal ingest endpoint (§5). Laravel resolves `tank_id` from `device_id`, stores `tank_level_readings` (idempotent on `(device_id, timestamp)`), evaluates geofences for a record with `satellites > 0` (§2.K), and returns `2xx` after commit.
- **Ack.** Only after that `2xx` does the gateway reply to the device with the number of records accepted (§2.B).
- **Deployment.** A standalone, supervised service (e.g., Node.js — open-source Codec 8/8E parsers exist), TLS terminated in the service or in a TCP/TLS proxy in front of it, running on the **same server as Laravel** and calling it over `localhost` with a shared internal token; the internal endpoint is blocked from the public network (e.g., in nginx: allow `127.0.0.1`, deny all). Laravel itself never listens on raw TCP: it exposes only the internal HTTP endpoint in §5.
- **Not in this version:** server-to-device commands (Codec 12) and remote configuration from the web app (§7.C). FMC225 units are configured with Teltonika's tools.

### 4.2 Site units — Teltonika routers over HTTPS
Fill Line, Dispense Line and Main Tank ATG units call the REST API (§5, Edge API) directly over HTTPS — TCP with TLS, the same transport family as the FMC225 link. Each call is a request/response, so the response doubles as the acknowledgement (§2.B), scan validation is an ordinary API call, and there is no broker, topic ACL or persistent session to run.
- **The RUT956 runs the site-unit logic itself**: RFID check, valve control, flow totalisation, offline queue and retry, written as a Python 3 script (Teltonika's Python3 package from the router's Package Manager) that calls the REST API over HTTPS and starts at boot.
- **Why not a raw TCP link from the router.** The RUT956 can act as a TCP client with TLS (Serial over IP) and can wrap TCP in TLS with Stunnel, but a long-lived raw link means inventing our own framing, acknowledgement and authentication, and a community report describes a RUT956 Serial-over-IP SSL client that fails to reconnect after the server restarts. Independent HTTPS calls avoid both.
- Device identity = `X-Device-ID` + a bearer API token over TLS (§7.A).
- Traffic is small: a few JSON calls per session plus one reading batch about every 30 s.
- **To verify on the RUT956 firmware in use:** the Python 3 package installs with enough free flash (there is no Docker on the RUT956); Python's `ssl` module and a CA bundle validate our server certificate; HTTPS POST of JSON with a bearer token works; the script restarts automatically after a reboot or crash; and the I/O and serial budget covers both valves (outputs plus position/status inputs), the flow meter (a pulse input the router can count reliably, or a Modbus/RS485 flow meter) and the RFID reader (RS232 or RS485).

---

## 5. API Endpoints

**Edge API** (site units, over HTTPS; auth: `X-Device-ID` header + `Authorization: Bearer <api_token>`). Responses: `2xx` = committed (the acknowledgement); `304` = nothing new; `401` = unknown or retired device; other `4xx` = bad request, not retried; `5xx` / timeout = retry with the same ids.
- `GET /api/v1/sync/rfid?version=` — RFID list poll: `304`, or the new version plus the active tag list (CSV/plain text).
- `POST /api/v1/auth/check` — `{tag_uid}` → `{allow, reason}`; emergency validation for a tag missing from the local list.
- `POST /api/v1/transactions` — a completed session (`device_txn_id`, `tag_id`, `liters`, `started_at`, `ended_at`); dedups on `(device_id, device_txn_id)`.
- `POST /api/v1/scan-rejections` — a rejected scan (`device_event_id`, raw tag UID, reason, time); dedups on `(device_id, device_event_id)`.
- `POST /api/v1/site/readings` — a batch of Main Tank ATG readings (`batch_id`, timestamp and liters per reading); dedups on `(device_id, timestamp)`.

**Internal API** (TCP gateway → Laravel; localhost only, shared internal token)
- `POST /internal/ingest/avl` — batch of parsed FMC225 records (`device_id`, timestamp, latitude, longitude, satellites, ATG value). Idempotent on `(device_id, timestamp)`; returns `2xx` only after commit (which includes geofence evaluation, §2.K), and the gateway then acks the device with the accepted count.

**Frontend API** — all routes sit behind Laravel Sanctum session auth **and** check the permission shown; no anonymous access to live fuel/theft data.
- `GET /api/v1/dashboard/main-tank` (`main_tank.view`) — hourly `liter_masuk` / `liter_keluar` for a Main Tank, hours in the site timezone (Page 1)
- `GET /api/v1/dashboard/mobile-tanks` (`mobile_tanks.view`) — all registered tanks per `division` with latest level and last transaction, plus transaction history (Page 2)
- `GET /api/v1/dashboard/alerts` (`alarms.view`) — `anomaly_logs`, newest first, paginated, filter by type/status; includes `anomaly_type`, `status_investigasi` and per-user `is_read` (Page 3)
- `GET /api/v1/dashboard/map` (`map.view`) — every Browser Tank / Fuel Tanker with its latest valid reading (lat, lon, `satellites`, timestamp) and the active geofence polygons for the site (Page 4)
- `PATCH /api/v1/dashboard/alerts/{id}/read` (`alarms.view`) — records that the current user opened the alarm
- `PATCH /api/v1/dashboard/alerts/{id}/status` (`alarms.manage`) — sets status; `resolution_note` required for `resolved` / `false_positive`

**Admin API**
- `GET|POST|PATCH|DELETE /api/v1/admin/geofences` (`geofences.manage`) — create/edit a geofence as GeoJSON, toggle `is_active`, set `applies_to`
- `GET|POST|PATCH /api/v1/admin/tanks` (`tanks.manage`)
- `GET|POST /api/v1/admin/hardware` (`hardware.manage`) — registers a device (+ tag for a mobile unit) and returns the `api_token` once (site units only); `POST …/{device_id}/regenerate-token`
- `POST /api/v1/admin/tanks/{id}/assign-hardware` and `POST …/replace-hardware` (`hardware.manage`)
- `PATCH /api/v1/admin/rfid/{tag_id}` (`rfid.manage`) — block / unblock
- `POST /api/v1/admin/vendor-fills` (`vendor_fill.create`)
- `GET|PATCH /api/v1/admin/settings` (`settings.manage`) — timezone and the non-sensitivity tunables in `sites.settings`
- `GET|PATCH /api/v1/admin/sensitivity` (`sensitivity.manage`) — active mode per alert type and the Low / Medium / High values; `GET` also returns the calculation preview per registered tank (§2.G)
- `GET|PUT /api/v1/admin/backup/settings` (`backup.manage`) — storage configuration; `GET` never returns the secret key
- `POST /api/v1/admin/backup/test-connection` (`backup.manage`) — per-check pass/fail result
- `POST /api/v1/admin/backup/run` (`backup.manage`) — runs a backup now (used for the test backup and manual runs)
- `GET /api/v1/admin/backup/runs` (`backup.manage`) — run history
- `GET|POST|PATCH|DELETE /api/v1/admin/users` (`users.manage`) and `/api/v1/admin/roles` (`roles.manage`)

**Reports API** (Sanctum + `reports.export` + the view permission of the underlying data)
- `GET /api/v1/reports/{report}/export.csv` — streamed CSV. `report` is one of `main-tank-transactions`, `main-tank-daily-reconciliation`, `browser-tank-refuels`, `browser-tank-daily-consumption`, `fuel-tanker-unloads`, `alarm-log`, `tank-level-readings`. Query: `from`, `to`, the report's filters, `format=standard|excel_id`.

**WebSocket** (private channels, authorized per permission)
- `trissan.live.main-tank` (`main_tank.view`) — new Main Tank transactions, latest Main Tank level, `tank.updated`
- `trissan.live.mobile-tanks` (`mobile_tanks.view`) — new mobile-tank transactions, latest levels, `tank.updated`
- `trissan.live.alarms` (`alarms.view`) — new and updated `anomaly_logs`
- `trissan.live.map` (`map.view`) — a new valid reading's position for any mobile tank; geofence created/updated/deleted

---

## 6. Frontend Implementation (React / Inertia / Tailwind)

### Feature Inventory (by view)

Every feature a user can see or use in the web app. Which menu items appear depends on the user's role permissions (§2.F). Backend/Edge-only capabilities (RFID sync, scan enforcement, C1/C2 detection, store-and-forward) are not views and are specified in §2.

| # | Module | Feature | Route | What the user sees / does | Permission |
|---|---|---|---|---|---|
| 1 | Monitoring | **Main Tank Monitoring** | `/fuel-monitoring/main-tank` | Hourly bar chart of fuel in (`fill_to_main`) vs. out (`dispense_to_browser`) for the Main Tank; transaction table that auto-scrolls to the newest row; live via `trissan.live.main-tank` | `main_tank.view` |
| 2 | Monitoring | **Browser Tank Monitoring** | `/fuel-monitoring/mobile-tanks` (Browser Tank half) | All registered Browser Tanks; ATG level area chart per tank; refuel history from the Dispense Line; RFID shown as `HEX(Sector N)`; live via `trissan.live.mobile-tanks` | `mobile_tanks.view` |
| 3 | Monitoring | **Fuel Tanker Monitoring** | `/fuel-monitoring/mobile-tanks` (Fuel Tanker half) | All registered Fuel Tankers; ATG level area chart per tank; unload history from the Fill Line and recorded vendor fills; live via `trissan.live.mobile-tanks` | `mobile_tanks.view` |
| 4 | Alarms | **Alarm Log** | `/fuel-monitoring/alarms` | Newest-first list with type badges (`Theft Alarm`, `Sensor Needs Calibration`, `Unauthorized Scan`), status, filters by type / status / date, and coordinates with an Open-in-map link for alarms on a mobile tank; live via `trissan.live.alarms` | `alarms.view` |
| 5 | Alarms | **Alarm Handling** | Alarm Log (row action) | Set status Investigating / Resolved / False positive; a note is required for the last two | `alarms.manage` |
| 6 | Alarms | **Read/Unread & Notifications** | Global (navigation) | Per-user read state, unread-alarm badge, toast on each new alarm (website notification only) | `alarms.view` |
| 7 | Monitoring | **Live Fleet Map** | `/fuel-monitoring/map` | Every Browser Tank and Fuel Tanker plotted on a live map from its latest valid GPS reading; active geofence boundaries overlaid; a muted "signal lost" marker for a stale fix; live via `trissan.live.map` (§2.K) | `map.view` |
| 8 | Monitoring | **Geofence Management** | `/fuel-monitoring/map` | Draw, edit, activate/deactivate a geofence and choose which division(s) it applies to, on the same map (§2.K) | `geofences.manage` |
| 9 | Fleet | **Register Tank** | `/admin/tanks` | Add a Main Tank, Browser Tank or Fuel Tanker: name, capacity (liters), division dropdown | `tanks.manage` |
| 10 | Fleet | **Register Hardware + RFID** | `/admin/tanks` | Register a unit by device ID (IMEI for an FMC225, router ID for a site unit) and type; a mobile unit is registered together with its RFID tag as one bundle; a site unit's API token is shown once and can be regenerated | `hardware.manage` |
| 11 | Fleet | **Assign Hardware** | `/admin/tanks` | Attach a spare unit to a tank; its tag becomes active and the RFID list is republished | `hardware.manage` |
| 12 | Fleet | **Replace Hardware** | `/admin/tanks` | Swap a unit on a tank: old unit retired, old tag blocked, new unit assigned; history stays with the tank | `hardware.manage` |
| 13 | Fleet | **Block / Unblock RFID Tag** | `/admin/tanks` | Disable or re-enable a tag; the line units reject it once the list syncs | `rfid.manage` |
| 14 | Fleet | **Record Vendor Fill** | `/admin/tanks` (Fuel Tanker row) | Enter declared liters (default = capacity) and fill time for a Fuel Tanker filled by the external vendor | `vendor_fill.create` |
| 15 | System | **Alert Sensitivity** | `/admin/sensitivity` | Low / Medium / High per alert type, editable values, reset to presets, calculation preview in liters per tank | `sensitivity.manage` |
| 16 | System | **Settings** | `/admin/settings` | Site timezone; window, grace, settle time, reading interval, retention | `settings.manage` |
| 17 | System | **User Management** | `/admin/users` | Create, edit, delete users and assign a role | `users.manage` |
| 18 | System | **Role & Permission Management** | `/admin/roles` | Create roles with a per-feature permission checklist; Super Admin is built-in | `roles.manage` |
| 19 | System | **Backup Storage Setup** | `/admin/backup` | Configure any S3-compatible storage (provider, endpoint, region, bucket, keys, path prefix), **Test Connection**, **Run Test Backup**, then enable the schedule (time, retention) | `backup.manage` |
| 20 | System | **Backup History & Run Now** | `/admin/backup` | List of runs (time, trigger, status, size, error) and a Run Backup Now button; warning banner if the last backup failed or is overdue | `backup.manage` |
| 21 | Reports | **CSV Export (Reports)** | `/reports` | Pick one of 7 reports (Main Tank transactions and daily reconciliation, Browser Tank refuels and daily consumption, Fuel Tanker unloads and vendor fills, Alarm Log, raw level readings), set date range and filters, choose Standard or Excel (Indonesia) format, download CSV; Export CSV shortcuts on the monitoring and alarm pages | `reports.export` + the view permission of the data |
| 22 | Global | **Login / Logout** | — | Sanctum session; no anonymous access to any data | — |
| 23 | Global | **Global UI** | All pages | Light/Dark toggle, permission-based navigation, timestamps in the site timezone, live updates without reload | — |

### Page details

**Global:** industrial high-contrast Light/Dark toggle, `font-mono` on all numeric tables, Recharts for charts. Data is hydrated via Inertia props on initial load, **then kept live by subscribing to the page's private channel (Laravel Echo)** — Inertia props are only the first paint. Navigation, pages and buttons render according to the user's permissions. Timestamps display in the site timezone. The nav shows an unread-alarm badge and new alarms raise a toast.

**Page 1 — Main Tank** (`/fuel-monitoring/main-tank`): `<BarChart>` of `liter_masuk` (green, = `fill_to_main`) vs `liter_keluar` (orange, = `dispense_to_browser`) per hour for the selected Main Tank; table with auto-scroll to newest row, appended live via `trissan.live.main-tank`.

**Page 2 — Mobile Tanks** (`/fuel-monitoring/mobile-tanks`): 50/50 split grid by `tanks.division`, Fuel Tanker vs Browser Tank, listing **all registered tanks** ("no data yet" until the first reading). `<AreaChart>` per tank from `tank_level_readings` — both divisions have their own ATG, so both show real level; a Browser Tank rises at Dispense Line sessions, a Fuel Tanker rises at vendor fills and drops at Fill Line sessions. Table filtered by `division` (Browser Tank: `dispense_to_browser`; Fuel Tanker: `fill_to_main` and `vendor_fill`), RFID shown as `HEX(Sector N)`, updated live via `trissan.live.mobile-tanks`.

**Page 3 — Alarm Log** (`/fuel-monitoring/alarms`): high-priority full-width grid, newest-first, paginated, filterable by type/status/date, with an `anomaly_type` badge per row: **`Theft Alarm`** (`bg-red-600 text-white font-bold` + `animate-pulse`) for `sudden_change_theft`; a calmer **`Sensor Needs Calibration`** badge (amber, no pulse) for `calibration_needed`; a neutral **`Unauthorized Scan`** badge (slate, no pulse) for `unauthorized_scan` — visually distinct so a calibration note or a rejected scan never reads with the urgency of a real theft signal. Alarms on a mobile tank also show the truck's coordinates with an **Open in map** link (§2.J). Each row shows its status; users with `alarms.manage` get a status menu (Investigating / Resolved / False positive, with a required note for the last two). **Read/unread is per user:** unread rows render highlighted per their type; opening/clicking a row calls `PATCH …/read`, which drops it to normal styling for that user only. New pushes over `trissan.live.alarms` always arrive unread.

**Page 4 — Live Fleet Map** (`/fuel-monitoring/map`): Leaflet map (OpenStreetMap tiles) showing every registered Browser Tank and Fuel Tanker at its latest valid GPS reading, updated live via `trissan.live.map` as new readings arrive (no polling). Active geofence boundaries are drawn as overlays. A truck whose latest valid fix is older than `position_max_age_minutes` shows a muted marker labelled "signal lost" instead of silently staying frozen at its last spot. Users with `geofences.manage` get drawing tools (Leaflet.draw) to create or edit a geofence, set its name and which division(s) it applies to, and toggle it active; everyone with `map.view` sees the boundaries and the fleet, read-only.

**Reports** (`/reports`, `reports.export`): report dropdown (only the reports the user may export), date range, report-specific filters, format preset, and an Export CSV button; range limits are shown next to the date picker. Opened prefilled from the Export CSV buttons on Pages 1–3 (§2.I).

**Admin pages** (each gated by its permission):
- **Tanks & Hardware** (`/admin/tanks`): register tanks (name, capacity, division dropdown); per tank: assign hardware, replace hardware, block/unblock tag; on a Fuel Tanker: record vendor fill.
- **Settings** (`/admin/settings`): site timezone and the other detection tunables (window, grace, settle, reading interval, retention; §3).
- **Alert Sensitivity** (`/admin/sensitivity`, `sensitivity.manage`): per alert type, a Low / Medium / High selector, an editable value table, a "Reset to presets" action, and the calculation preview in liters per registered tank (§2.G).
- **Backup Storage** (`/admin/backup`, `backup.manage`): storage form, Test Connection, Run Test Backup, schedule and retention, run history, Run Backup Now (§2.H).
- **Users & Roles** (`/admin/users`, `/admin/roles`): create roles with a permission checklist grouped by feature; assign users to roles.

---

## 7. Review Outcomes

### 7.A — Applied (reflected in §3/§5/§6)

**Idempotency and acknowledgement.** Retries are normal on cellular links, so every write path is idempotent — transactions on `(device_id, device_txn_id)`, readings on `(device_id, timestamp)`, rejected scans on `(device_id, device_event_id)` — and a device discards data only after an explicit acknowledgement that follows the MySQL commit: the HTTP `2xx` for site units, Teltonika's record-count ack for FMC225 units (§2.B, §4.1). *History:* v2.0 specified "server returns MQTT `PUBACK` after the MySQL write", which could not work because `PUBACK` comes from the broker; v2.1 replaced it with an application-level ack, and v2.3 removed MQTT altogether.

**Device authentication (revised in v2.2, simplified in v2.3).** Every link is TCP + TLS, and authentication is per platform:
- *Site units (routers, HTTPS):* TLS (the unit validates our server certificate) + a per-device **API token** sent as a bearer token together with `X-Device-ID`. The token is shown once at registration and stored only as a hash, so a database leak or restore does not expose usable device credentials; it can be regenerated and stops working when the unit is retired. Individual payloads are not signed.
- *Mobile units (FMC225, TCP):* TLS (the device verifies the gateway's certificate) + an **IMEI allowlist** at the handshake — unknown or retired IMEIs are rejected. An IMEI is an identifier, not a secret, so someone who learns it could try to impersonate a truck. This residual risk is accepted, with mitigations: a private APN/VPN for the SIMs where the carrier offers it, plausibility checks on incoming data (level within 0..capacity, `rtc_out_of_bounds` on timestamps, sudden jumps still go through C1), and rate limiting on the gateway.
- **Plain, unencrypted TCP is never enabled:** the gateway and the API accept TLS connections only.

Mutual TLS / client certificates are deferred (§7.C).

**Backend/Frontend schema alignment.** §3 is the single schema; the frontend's log shapes are API resources, not tables.

**Live data on the dashboard.** Each page subscribes to its own private channel after initial load, so "real-time monitoring" is real-time, not a snapshot.

**Capacity-based theft threshold.** The threshold is a percentage of the tank's `capacity_liters` (§2.C), not a flat 50 L. **v2.1:** the Main Tank is now a `tanks` record with a capacity, and thresholds are set per division, because a percentage that suits a Browser Tank is too coarse for a Main Tank.

**Alarm read/unread state.** Inbox pattern; **per user** in v2.1 (`anomaly_reads`), not a global flag.

**Kept as free/required:**
- **Decimal volume precision** (`DECIMAL(10,2)`) — a column type, not a subsystem; the difference between "zero data lost" holding at the fraction-of-a-liter level or not.
- **Basic endpoint auth (Sanctum)** — the alternative is an unauthenticated live theft dashboard.

### 7.B — Adjusted by decision

**Temperature compensation (ATC) — dropped.** Decision: the C2 tolerance (1%) and the C1 theft threshold are treated as sufficient buffer for calibration drift and thermal variance combined. No ATC subsystem. Operational note: if false positives cluster around temperature extremes (midday vs. night), check that first before tuning thresholds. (v2.0's "1%/3% band" wording predated the C1/C2 split and is retired.)

**RTC trust — lightweight guard instead of a full fix.** The device keeps its clock synced (NTP on the routers, GNSS/network time on the FMC225) and only free-runs it while genuinely offline, so drift/tamper exposure is bounded to actual dead-zone time. No hash-chaining. The schema records `server_received_at` on every transaction and reading, and `rtc_out_of_bounds` flags a device timestamp in the future relative to server time, or older than the maximum plausible offline duration.

### 7.C — Deferred (Phase 2 / optional)

Not built into this revision; listed so the decision is visible:
- Device-offline alert ("no data for N minutes" banner)
- Full audit trail for admin actions (v2.1 already records `resolved_by`, `entered_by` and per-user reads)
- Database clustering and formal HA-DR (RTO/RPO) — daily S3 backup is the v2.1 baseline
- Canary/staged rollout for RFID version pushes
- Policy for emergency validation while fully offline, beyond the fail-closed rejection in §2.A
- External alarm notifications (WhatsApp/Telegram/email/SMS) — website notification only, by decision
- Restore from the web UI (restore stays a manual, rehearsed procedure), client-side encryption of backup files, and non-S3 storage providers
- Scheduled or e-mailed reports, PDF / Excel (.xlsx) export and a custom report builder — CSV export only in this version
- Mutual TLS / client certificates for device authentication, Codec 12 commands and remote configuration of FMC225 units from the web app, route replay (the per-reading GPS trail already exists, §2.J) and an embedded provider map (OpenStreetMap/Leaflet is already embedded for the live map, §2.K)

These don't block go-live at the stated bar. If one is worth a second look later, the **device-offline alert** is the cheapest — `hardware_devices.last_seen` already exists, so it needs no new protocol, and it is the most direct way to know a device went dark.

### 7.D — Round 2: operational logic (after leadership discussion)

**Theft detection split into two independent mechanisms.** The original single reconciliation rule did two jobs with one signal. **C1 (sudden change within a tank)** is the theft signal; **C2 (session reconciliation)** is calibration-only and can never escalate to a Theft Alarm, no matter how large the variance. Reconciliation variance is hard to attribute to theft with confidence, so it is not labeled as theft.

**Registration captures `division`.** It routes a tank into the right thresholds and the correct half of the Page 2 split. Hardware + RFID continue to be registered as one bundle. *(The Round 2 rule that excluded the Fuel Tanker from reconciliation is superseded — see §7.E.)*

### 7.E — v2.1: corrections and site decisions

**Corrections to v2.0**
1. Stale 1%/3% band wording removed (§7.B, §9); C2 has a single 1% tolerance and never becomes theft.
2. `PUBACK`-after-MySQL replaced by an application-level ack (§2.B).
3. "New vehicle appears via the existing WebSocket" corrected: Page 2 lists from `tanks`, and register/assign/replace broadcast `tank.updated` (§2.D).
4. WebSocket channels are private and permission-authorized (§1.2).
5. Terminology unified: `division` everywhere; `vehicles` → `tanks`; "vehicle type" → "tank" (one row per physical unit).
6. Main Tank now has a capacity: `division = main_tank` in `tanks`; thresholds per division.

**Decisions from site input**
- **Hardware model** = four kinds of unit (§1.1). The Fuel Tanker unloads into the Main Tank through the Fill Line, so C2 now covers **both** lines; only vendor fills are excluded (supersedes the Round 2 exclusion, which assumed a Fuel Tanker dispensed to equipment).
- **Both Browser Tank and Fuel Tanker carry ATG + SIM + RFID tag**, so C1 runs on both.
- **Browser Tank is the end consumer (confirmed).** Fuel is burned by the machine and must never be transferred to another vehicle. A fast, significant drop is a Theft Alarm by design; there is no "legitimate transfer out" path. If this rule ever changes, transfers out would need their own transaction source.
- **Mismatch rule:** a small unexplained volume next to a matching transaction (e.g., 50 L drop vs. 40 L transaction) is a *calibration* warning (C2); a large unexplained change is theft (C1).
- **C2 pairing** by RFID tag + session window (§2.C) — no separate session table; scales with fleet size.
- **Detection is server-side** on stored level readings (`tank_level_readings`), ordered by device time.
- **Alarm lifecycle:** `open → investigating → resolved | false_positive`, with a resolution note; per-user read; website notification only; admin and super admin close alarms (§2.E). The status set follows the common alert-lifecycle pattern (new → acknowledged/in progress → resolved, with false positive as a closure outcome) without extra states.
- **Dynamic roles** with a per-feature permission checklist (§2.F).
- **Alert sensitivity:** Low / Medium / High per threshold-based alert type, editable by the Super Admin, with a calculation preview in liters (§2.G).
- **Backup storage** is configured in the web app for any S3-compatible provider (e.g., Cloudflare R2): save → test connection → test backup → enable schedule, with run history and a failure banner (§2.H).
- **CSV export for reports** (client request): 7 reports, streamed download, Excel-friendly format presets, permission-gated and logged (§2.I).
- **Scale & ops:** one site now, expandable; S3 backups; timezone editable by admins (§1.3).
- **Registration, assignment and replacement** flow with admin pages and endpoints (§2.D, §5, §6).
- **Fail-closed scan enforcement**, and rejected scans are logged as `unauthorized_scan` (§2.A). *(The rejected-scan log is a small addition beyond the request; it costs one enum value and one badge.)*

**Assumptions to confirm at commissioning**
- **A1.** The ATG value reaches the server as liters (scaled in the FMC225 I/O configuration, or converted by the gateway from a per-tank calibration table). If the ATG outputs a raw signal (voltage or height), that conversion must be defined per tank before go-live. Readings arrive about every 30 s.
- **A2.** The defaults in §2.G and §3 (Low / Medium / High presets, with Medium = theft threshold 10% Browser/Fuel and 2% Main, C2 1%; window 30 min; settle 60 s; grace 10 min; retention 90 days) are starting values to tune with real site data.

### 7.F — v2.2 / v2.3: Teltonika hardware platform, transport and GPS

**Platform**
- Mobile units (Browser Tank, Fuel Tanker) = Teltonika **FMC225** + ATG sensor + RFID tag; the SIM/modem is part of the FMC225.
- Fill Line and Dispense Line units = Teltonika **RUT956** router connected to the RFID reader, flow meter and valves; it runs the unit's logic itself.

**Transport decision (v2.3): TCP + TLS for everything.** The FMC225 uses Teltonika's TCP protocol: MQTT on the FMC225 is documented only via AWS IoT (or a custom server modelled on AWS's protocol) and has no TLS in its generic mode, so a self-hosted broker is not a supported path; TCP supports TLS and its record-count ack matches the zero-data-loss requirement (§4.1). The routers use HTTPS to the REST API. v2.2 had kept MQTT for the routers, but that meant a second protocol and a broker to install, secure and monitor for three site units; HTTPS needs none of that and turns acknowledgement, scan validation and RFID sync into ordinary request/response calls (§4.2).
- **Cost accepted:** the server cannot push to a unit behind a cellular NAT, so RFID list changes are polled (`rfid_poll_seconds`, default 60). A tag blocked on the server can therefore still open a valve on a unit that has not polled yet, for up to that interval; lowering the interval shrinks the window.

**Security consequence.** Per-device HMAC signing is dropped because neither Teltonika platform can be customised at firmware level; §7.A records the revised model (TLS + API token for routers, TLS + IMEI allowlist for FMC225) and the residual IMEI risk.

**GPS.** Position is stored with every level reading and copied once onto each alarm that concerns a mobile tank (§2.J).

**Decision and assumption A3.** *Decided:* the site-unit logic required by §2.A/§2.B — RFID check against the local list and the server, fail-closed valve control, flow totalisation, offline queue and retry until `2xx` — runs on the RUT956 itself as a Python 3 script; there is no controller behind it. *Still assumed:* the Main Tank ATG is also read by a RUT956 and reports over HTTPS like the line units. The RUT956 checks that must pass before build are listed in §4.2 (Python package and storage, TLS validation, auto-restart, I/O and serial budget, flow-meter input type).

### 7.G — v2.4: live fleet map, geofencing, and a GPS-validity correction

**Correction (found while researching the map feature).** v2.2/v2.3 assumed a level reading's `latitude`/`longitude` are NULL when the FMC225 has no GPS fix. Teltonika's own documentation says otherwise: *"If record is without valid coordinates ... Longitude, Latitude and Altitude values are last valid fix"* — the device resends its last known position, stamped with the current time, rather than sending nothing. Left uncorrected, this would have made geofencing (and the §2.J alarm snapshot) trust a stale, possibly long-out-of-date position as if it were fresh. The fix: the GPS Element also carries a `satellites` count (visible-satellite count, a fixed field, not an optional one), which is `0` on a repeated stale fix in Teltonika's own examples. `tank_level_readings` now stores `satellites`, and every place that reads a position — §2.J's alarm snapshot and §2.K's geofence check — uses the latest reading with `satellites > 0`, not just the latest reading (§2.J, §3).

**Map and geofencing decision.** Researched three pieces before committing: a mapping library, where a geofence boundary is stored, and where "inside/outside" is decided.
- **Leaflet.js + OpenStreetMap** over Google Maps or Mapbox: no per-load or per-request fee, which matters for a page that live-updates continuously, and it is the standard choice for open-source fleet-tracking and geofencing projects.
- **MySQL 8 spatial types** (`POLYGON`, `SPATIAL INDEX`, `ST_Within`) over a hand-rolled point-in-polygon check: it is already the database in the stack, needs no extra service, and the standard idiom for "is this point inside that shape" queries.
- **Evaluation runs server-side, in the same ingest job as C1/C2** — not in the browser. A browser-side check would only catch an exit while someone has the map open; a server-side check, keyed off the same `tank_level_readings` stream that already feeds detection, raises the alarm whether or not anyone is looking, exactly like every other alarm in this document.

**GPS format compatibility (verified).** Codec 8 Extended's GPS Element encodes longitude and latitude as signed 32-bit integers in degrees × 10,000,000 (WGS84) — ordinary decimal-degree GPS coordinates once divided down, which existing open-source Codec 8E parsers already do. This is exactly the format MySQL's `SRID 4326` and Leaflet both expect, so no extra conversion layer is needed beyond the parsing already planned for the gateway (§4.1).

**Scope decision.** Geofencing applies only to Browser Tank and Fuel Tanker (the only tanks that move); the Main Tank and line units are fixed infrastructure and are never checked. Only an *exit* raises an alarm; re-entry updates the tracked state and appends a note to the alarm rather than resolving it, keeping the manual-close model of §2.E consistent for every alarm type. A debounce (`geofence_exit_confirm_readings`, default 2 consecutive readings) avoids alarm spam from ordinary GPS jitter at a boundary.

---

## 8. Schema Deltas from v2.0 (Applied in §3)

- **New tables:** `sites`, `tank_level_readings`, `anomaly_reads`, `roles`, `role_permissions` (+ `role_id` on `users`).
- **`vehicles` → `tanks`:** adds the `main_tank` division and `site_id`; `mac_address` moves off the tank and onto `hardware_devices.tank_id`.
- **`hardware_devices`:** `device_type` becomes the four kinds (`main_tank_atg` / `fill_line` / `dispense_line` / `mobile_unit`); adds `tank_id` and `status`; `device_secret` stored encrypted.
- **`rfid_tags`:** `vehicle_id` → `tank_id` (nullable until assigned).
- **`transactions`:** `liter_masuk` / `liter_keluar` / `fill_source` replaced by `liters` + `transfer_type`; adds `tank_id`, `main_tank_id`, `started_at` / `ended_at` (replaces `timestamp`), `entered_by`; `sync_status` now defined.
- **`anomaly_logs`:** adds the `unauthorized_scan` type, `tank_id`, `transaction_id`, `device_event_id`, `observed_percent`, `resolution_note` / `resolved_by` / `resolved_at`, `meta`; `threshold_percent` now means the configured threshold applied; `status_investigasi` values defined; `is_read` / `read_at` moved to `anomaly_reads`.
- **Alert sensitivity:** `sites.settings` now holds the `sensitivity` presets (replacing `theft_threshold_percent` and `c2_variance_percent`); `sites` adds `settings_updated_by` / `settings_updated_at`; `anomaly_logs` adds `sensitivity_mode`; new permission `sensitivity.manage`.
- **Backup storage:** new tables `backup_settings` and `backup_runs`; new permission `backup.manage`.
- **CSV export:** new table `export_logs`; new permission `reports.export`.
- **Removed:** MQTT topic `alert/+/theft`; the single WebSocket channel `trissan.live.updates`.

**v2.2 deltas**
- `mac_address` → `device_id` everywhere (IMEI for an FMC225, MQTT client ID for a site unit); topic segment `{mac}` → `{device_id}`; `X-Device-MAC` → `X-Device-ID`.
- `tank_level_readings` adds `latitude`, `longitude`; `anomaly_logs` adds `latitude`, `longitude`, `position_time`; new setting `position_max_age_minutes`.
- `hardware_devices.device_secret` now applies to site units only (NULL for FMC225).
- Removed: per-device HMAC (`sig`, `X-Signature`); `POST /api/v1/sync/time` (routers use NTP, the FMC225 uses GNSS time); the MQTT telemetry topic for mobile units (levels arrive over TCP).
- Added: TCP gateway (§4.1) and internal endpoint `POST /internal/ingest/avl` (§5).

**v2.3 deltas**
- MQTT removed: no broker, topics, topic ACL, `trissan/ack` or persistent subscriber. Site units call HTTPS endpoints: `auth/check`, `scan-rejections`, `site/readings`, plus the existing `transactions` and `sync/rfid` (now polled with a version).
- RFID broadcast trigger replaced by polling; new setting `rfid_poll_seconds` (60).
- `hardware_devices.device_secret` → `api_token_hash` (SHA-256 hash; the token is shown once); admin endpoint `regenerate-secret` → `regenerate-token`.
- Topology: the TCP gateway runs on the same server as Laravel, and `/internal/ingest/avl` is reachable from localhost only.

**v2.4 deltas**
- **Correction:** `tank_level_readings.latitude`/`longitude` are no longer described as NULL when there's no fix — the device repeats the last valid fix instead. New column `tank_level_readings.satellites`; validity is `satellites > 0`, not "coordinates present" (§7.G).
- New tables: `geofences`, `tank_geofence_state`.
- `anomaly_logs` adds the `geofence_exit` type and `geofence_id`.
- New setting `geofence_exit_confirm_readings` (default 2).
- New permissions: `map.view`, `geofences.manage`.
- New WebSocket channel `trissan.live.map`.
- New endpoints: `GET /api/v1/dashboard/map`, `GET|POST|PATCH|DELETE /api/v1/admin/geofences`.
- Internal AVL ingest payload (`/internal/ingest/avl`) adds `satellites` per record.

---

## 9. Status Summary

| Item | Status | Notes |
|---|---|---|
| Transaction idempotency | ✅ Applied | `device_txn_id` unique constraint |
| Delivery acknowledgement (zero data lost) | ✅ Applied (v2.1, updated v2.3) | Ack only after MySQL commit: HTTP `2xx` (site units) or Teltonika record-count ack (FMC225) |
| Device authentication | ✅ Applied (revised v2.3) | Site units: TLS + hashed per-device API token. FMC225: TLS + IMEI allowlist (residual IMEI risk, §7.A) |
| Teltonika platform (FMC225 over TCP, RUT956 over HTTPS) | ✅ Applied (v2.3) | §4, §7.F; site-unit logic runs on the RUT956 (Python 3); Main Tank ATG wiring assumed (A3) |
| Transport security | ✅ Applied (v2.3) | TCP + TLS on every link; no plain-TCP port; no MQTT broker to run |
| GPS coordinates on alarms | ✅ Applied (v2.2, corrected v2.4) | One-time snapshot per alarm on a mobile tank; now uses `satellites > 0` to find a genuinely fresh fix (§2.J, §7.G) |
| Live fleet map | ✅ Applied (v2.4) | Leaflet + OpenStreetMap, all Browser/Fuel Tanker trucks, live via `trissan.live.map` (§2.K) |
| Geofencing | ✅ Applied (v2.4) | Server-side `ST_Within` on every valid reading; exit alarm with debounce (§2.K) |
| Backend/Frontend schema alignment | ✅ Applied | Single schema, resource transformers |
| Live WebSocket consumption on frontend | ✅ Applied | Three private, permission-authorized channels |
| Capacity-based theft threshold | ✅ Applied | Per division; Main Tank is now a tank with capacity |
| Real hardware model (4 units, Fill/Dispense Lines) | ✅ Applied (v2.1) | §1.1 |
| Level readings + server-side C1/C2 | ✅ Applied (v2.1) | `tank_level_readings`; C2 covers both lines |
| Fail-closed RFID scan enforcement | ✅ Applied (v2.1) | Unregistered/blocked/offline → rejected, logged |
| Registration, assignment, hardware replacement | ✅ Applied (v2.1) | Admin pages + endpoints, history kept via `tank_id` |
| Alarm lifecycle + per-user read | ✅ Applied (v2.1) | `open → investigating → resolved / false_positive` |
| Dynamic roles & permission checklist | ✅ Applied (v2.1) | Super Admin built-in |
| Alert sensitivity (Low / Medium / High) | ✅ Applied (v2.1) | Per alert type, Super Admin-editable, calculation preview in liters |
| Timezone configurable, multi-site-ready | ✅ Applied (v2.1) | `sites.timezone`, `site_id` |
| CSV export for reports | ✅ Applied (v2.1) | 7 reports, streamed download, Excel-friendly presets, export log (§2.I) |
| Backup to S3-compatible storage | ✅ Applied (v2.1) | Configured, tested and triggered from the web app (§2.H); daily full proposed; restore is manual |
| Decimal volume precision | ✅ Applied | Kept as free/required |
| Basic API auth (Sanctum) | ✅ Applied | Kept as free/required |
| Temperature compensation (ATC) | ✖️ Dropped by decision | C2 1% + C1 threshold treated as sufficient buffer |
| RTC hash-chaining | ✖️ Downgraded by decision | Replaced with lightweight bound-check only |
| Device-offline alert ("no data for N minutes") | ⏸ Deferred | Phase 2 — cheapest to revisit (`last_seen` already exists) |
| Full audit trail for admin actions | ⏸ Deferred | Phase 2 |
| Database HA-DR plan | ⏸ Deferred | Phase 2 |
| Canary rollout for RFID pushes | ⏸ Deferred | Phase 2 |
| Offline emergency-validation policy (beyond fail-closed) | ⏸ Deferred | Phase 2 |
| External alarm notifications | ⏸ Deferred | Website-only by decision |

**Bar for this version:** stable, fast, secure, zero data lost, expandable — all ✅ items are what that bar requires; ⏸ items are explicitly out of scope for now, not overlooked.
