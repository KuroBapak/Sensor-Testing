# Enterprise Fuel Management System — Website Specification & Phased Roadmap
**Project:** PT. Trissan — Mining Site Fuel Monitoring & Anti-Theft System  
**Source PRD:** Unified PRD v2.4 (Teltonika Platform, TCP Gateway, S3 Backup, Live Map & Geofencing)  
**Target Architecture:** Laravel (PHP 8.4) · MySQL 8 · React 19 (Inertia.js v3) · Tailwind CSS · Recharts · Leaflet.js · Laravel Reverb  
**Scope Focus:** **Website Application (Frontend & Backend REST/WebSocket)** — Hardware IoT/Gateway interfaces fully documented at HTTP boundary.

---

## 0. Executive Strategy & Phased Implementation Plan

Sistem ini didesain dengan toleransi kesalahan nol (*zero-mistake tolerance*) di lingkungan operasional tambang aktif. Implementasi dibagi ke dalam **7 Phase bertahap dan terisolasi** untuk memastikan tidak ada fitur yang terlewat.

### Matriks 7 Phase Implementasi

```
Phase 1: Foundation, Auth, RBAC & Core Shell
   │
   ▼
Phase 2: Fleet & Hardware Management (Master Data, RFID, Token, Vendor Fill)
   │
   ▼
Phase 3: Ingestion Pipeline & Monitoring Dashboards (Main Tank, Mobile Tanks, Echo)
   │
   ▼
Phase 4: Anomaly Detection Engine & Alarm Management (C1 Theft, C2 Calibration, Inbox)
   │
   ▼
Phase 5: CSV Export & Comprehensive Reporting (7 Reports, Streaming, Excel ID, Audit)
   │
   ▼
Phase 6: Live Fleet Map & Server-Side Geofencing (Leaflet, MySQL Spatial, Debounce)
   │
   ▼
Phase 7: System Administration & Operations (Sensitivity, S3 Backup, Site Settings)
```

| Phase | Fokus Utama | Fitur Website Frontend (§6 PRD) | Endpoint Backend Terkait (§5 PRD) |
|---|---|---|---|
| **Phase 1** | Auth, RBAC, Navigasi, Shell UI | #17, #18, #22, #23 | `/admin/users`, `/admin/roles`, Auth session |
| **Phase 2** | Master Data Fleet, Hardware, RFID | #9, #10, #11, #12, #13, #14 | `/admin/tanks`, `/admin/hardware`, `/admin/rfid`, `/admin/vendor-fills`, `/sync/rfid` |
| **Phase 3** | Ingestion, Real-time Dashboard Main & Mobile | #1, #2, #3 | `/dashboard/main-tank`, `/dashboard/mobile-tanks`, `/internal/ingest/avl`, `/transactions` |
| **Phase 4** | Anomaly Detection, Alarm Lifecycle & Inbox | #4, #5, #6 | `/dashboard/alerts`, `/alerts/{id}/read`, `/alerts/{id}/status` |
| **Phase 5** | Export Laporan & Audit Trail | #21 + Shortcuts | `/reports/{report}/export.csv` |
| **Phase 6** | Live Fleet Map & Geofencing Spasial | #7, #8 | `/dashboard/map`, `/admin/geofences` |
| **Phase 7** | Sensitivitas Alert, S3 Backup & Settings | #15, #16, #19, #20 | `/admin/sensitivity`, `/admin/settings`, `/admin/backup/*` |

---

## 1. Feature Inventory Matrix (23 Fitur Website)

Daftar lengkap 23 fitur website dari PRD v2.4 (§6) yang wajib tersedia pada UI dan API:

| # | Modul | Nama Fitur | Route Frontend | Permission Key | Phase |
|---|---|---|---|---|---|
| 1 | Monitoring | **Main Tank Monitoring** | `/fuel-monitoring/main-tank` | `main_tank.view` | 3 |
| 2 | Monitoring | **Browser Tank Monitoring** | `/fuel-monitoring/mobile-tanks` | `mobile_tanks.view` | 3 |
| 3 | Monitoring | **Fuel Tanker Monitoring** | `/fuel-monitoring/mobile-tanks` | `mobile_tanks.view` | 3 |
| 4 | Alarms | **Alarm Log Table & Badges** | `/fuel-monitoring/alarms` | `alarms.view` | 4 |
| 5 | Alarms | **Alarm Handling** | `/fuel-monitoring/alarms` | `alarms.manage` | 4 |
| 6 | Alarms | **Alarm Read/Unread & Notifications** | Global Header | `alarms.view` | 4 |
| 7 | Monitoring | **Live Fleet Map** | `/fuel-monitoring/map` | `map.view` | 6 |
| 8 | Monitoring | **Geofence Management** | `/fuel-monitoring/map` | `geofences.manage` | 6 |
| 9 | Fleet | **Register Tank** | `/admin/tanks` | `tanks.manage` | 2 |
| 10 | Fleet | **Register Hardware + RFID** | `/admin/tanks` | `hardware.manage` | 2 |
| 11 | Fleet | **Assign Hardware to Tank** | `/admin/tanks` | `hardware.manage` | 2 |
| 12 | Fleet | **Replace Hardware (Atomic)** | `/admin/tanks` | `hardware.manage` | 2 |
| 13 | Fleet | **Block / Unblock RFID Tag** | `/admin/tanks` | `rfid.manage` | 2 |
| 14 | Fleet | **Record Vendor Fill** | `/admin/tanks` | `vendor_fill.create` | 2 |
| 15 | System | **Alert Sensitivity (L/M/H)** | `/admin/sensitivity` | `sensitivity.manage` | 7 |
| 16 | System | **System Settings & Timezone** | `/admin/settings` | `settings.manage` | 7 |
| 17 | System | **User Management** | `/admin/users` | `users.manage` | 1 |
| 18 | System | **Role & Permission Management** | `/admin/roles` | `roles.manage` | 1 |
| 19 | System | **Backup Storage Setup (S3)** | `/admin/backup` | `backup.manage` | 7 |
| 20 | System | **Backup History & Run Now** | `/admin/backup` | `backup.manage` | 7 |
| 21 | Reports | **CSV Export (7 Reports)** | `/reports` | `reports.export` + view | 5 |
| 22 | Global | **Authentication** | `/login`, `/logout` | Public | 1 |
| 23 | Global | **Global UI (Theme, Nav, Echo)** | All Pages | Authenticated | 1 |

---

## 2. Phase 1: Foundation, Auth, Dynamic RBAC & Core Shell

### 2.1 Tujuan
Membangun fondasi aplikasi, autentikasi session yang aman, dynamic Role-Based Access Control (RBAC) berbasis checklist permission, serta layout shell (sidebar, navigation, theme toggle, timezone context).

### 2.2 Model Data & Migrasi
- **`users`**: `id`, `name`, `email`, `password`, `role_id` (FK), `remember_token`, timestamps.
- **`roles`**: `id`, `name`, `is_system` (boolean: `true` untuk Super Admin built-in non-deletable), timestamps.
- **`role_permissions`**: `role_id` (FK), `permission_key` (string). PK: `(role_id, permission_key)`.
- **`sites`**: `id`, `name`, `timezone` (string default `'Asia/Jakarta'`), `rfid_list_version` (bigint default 1), `settings` (JSON), `settings_updated_by`, `settings_updated_at`.

### 2.3 Permission Catalog (Hardcoded in Code)
- **Pages**: `main_tank.view`, `mobile_tanks.view`, `alarms.view`, `map.view`
- **Alarms**: `alarms.manage`
- **Fleet**: `tanks.manage`, `hardware.manage`, `rfid.manage`, `vendor_fill.create`
- **System**: `settings.manage`, `sensitivity.manage`, `backup.manage`, `geofences.manage`, `users.manage`, `roles.manage`
- **Reports**: `reports.export`

### 2.4 Seeded Roles
1. **Super Admin** (`is_system = true`): Memiliki semua permission secara implisit. Tidak bisa dihapus.
2. **Admin**: Memiliki semua permission KECUALI: `users.manage`, `roles.manage`, `sensitivity.manage`, `backup.manage`, `geofences.manage`.

### 2.5 Frontend Implementation (React + Inertia + Tailwind)
- **AppSidebar Component**: Menu navigasi conditional berdasarkan `auth.permissions` dari Inertia shared props.
- **Dark/Light Mode**: Industrial high-contrast toggle dengan Tailwind `dark:` classes.
- **Typography**: Seluruh angka volume dan koordinat wajib `font-mono`. Timestamps dalam timezone site.
- **User Management Page** (`/admin/users`, #17): CRUD user, dropdown role, validasi password/email unik.
- **Role Management Page** (`/admin/roles`, #18): CRUD role kustom dengan checklist permission terkelompok per modul. Super Admin locked.
- **Login/Logout** (#22): Laravel Sanctum session auth.

---

| 20 | System | **Backup History & Run Now** | `/admin/backup` | `backup.manage` | 7 |
| 21 | Reports | **CSV Export (7 Reports)** | `/reports` | `reports.export` + view | 5 |
| 22 | Global | **Authentication** | `/login`, `/logout` | Public | 1 |
| 23 | Global | **Global UI (Theme, Nav, Echo)** | All Pages | Authenticated | 1 |

## 3. Phase 2: Fleet & Hardware Management

### 3.1 Tujuan
Menyediakan antarmuka dan API untuk mengelola fisik tangki, pendaftaran perangkat keras (IMEI FMC225 / Router RUT956), binding RFID, penggantian hardware secara atomic, pemblokiran tag, dan pencatatan manual vendor fill.

### 3.2 Model Data & Migrasi
- **`tanks`**: `id`, `site_id`, `name`, `division` (`main_tank`|`browser_tank`|`fuel_tanker`), `capacity_liters` (DECIMAL(10,2)), `deleted_at`.
- **`hardware_devices`**: `device_id` (PK), `device_type`, `tank_id` (nullable), `status` (`spare`|`active`|`retired`), `api_token_hash`, `last_seen`.
- **`rfid_tags`**: `tag_id` (PK HEX), `tank_id` (nullable), `sector`, `status` (`active`|`blocked`).
- **`transactions`** (Vendor Fill part): `transfer_type = 'vendor_fill'`, `liters`, `tank_id`, `entered_by`.

### 3.3 Alur Bisnis Kunci
1. **Register Hardware** (#10, `hardware.manage`):
   - **Mobile Unit**: Input IMEI (FMC225) + RFID Tag UID (bundled). Initial status: device `spare`, tag `blocked`.
   - **Site Unit**: Input Router Device ID. Server generates API token (SHA-256 hash stored), shows plain token **once** dengan copy button.
2. **Assign to Tank** (#11, `hardware.manage`):
   - Pilih `spare` device untuk tank → set `active`, link `tank_id`, activate tag, bump `rfid_list_version`, broadcast `tank.updated`.
3. **Replace Hardware** (#12, `hardware.manage`):
   - Atomic DB transaction: old device → `retired` + tag `blocked`, new spare → `active` + assigned.
   - History intact (transactions/readings linked to `tank_id`).
   - C1 baseline reset untuk tank tersebut.
4. **Vendor Fill** (#14, `vendor_fill.create`):
   - Modal pada Fuel Tanker row: input liters (default = capacity) + timestamp.
   - Insert `transactions` dengan `transfer_type = vendor_fill`, `entered_by = auth()->id()`.

### 3.4 Frontend Pages & API Endpoints
- **Tanks & Hardware Page** (`/admin/tanks`): Tabs Main Tank | Browser Tanks | Fuel Tankers.
- Modals: Register Tank (#9), Register Hardware (#10), Assign Hardware (#11), Replace Hardware (#12), Record Vendor Fill (#14).
- Endpoints: `GET|POST|PATCH /api/v1/admin/tanks`, `GET|POST /api/v1/admin/hardware`, `POST .../{id}/assign-hardware`, `POST .../{id}/replace-hardware`, `PATCH /api/v1/admin/rfid/{tag_id}`, `POST /api/v1/admin/vendor-fills`, `GET /api/v1/sync/rfid`.

---

---

## 4. Phase 3: Ingestion Pipeline & Real-Time Monitoring Dashboards

### 4.1 Tujuan
Mengimplementasikan penyimpanan data sensor (ATG & transaksi), pipeline ingest internal, integrasi WebSocket Laravel Echo, dan dashboard monitoring visual Main Tank & Mobile Tanks dengan live update.

### 4.2 Model Data & Migrasi
- **`tank_level_readings`**: `id`, `tank_id`, `device_id`, `level_liters` (DECIMAL(10,2)), `latitude`, `longitude` (DECIMAL(9,6) nullable), `satellites` (TINYINT nullable), `position` (POINT SRID 4326 + SPATIAL INDEX), `timestamp` (UTC), `server_received_at`, `rtc_out_of_bounds`. Unique: `(device_id, timestamp)`. Monthly partitioned, 90 days retention.
- **`transactions`**: `id`, `device_txn_id` (UUID), `device_id`, `transfer_type`, `tag_id`, `tank_id`, `main_tank_id`, `liters` (DECIMAL(10,2)), `started_at`, `ended_at`, `server_received_at`, `sync_status`, `entered_by`. Unique: `(device_id, device_txn_id)`.

### 4.3 Ingestion Backend
1. **Edge HTTPS API** (Site Units): `POST /api/v1/transactions`, `POST /api/v1/site/readings`. Auth: `X-Device-ID` + Bearer token. Response 2xx only after commit.
2. **Internal AVL API**: `POST /internal/ingest/avl` (localhost only). Batch FMC225 records, resolve tank_id, trigger geofence if `satellites > 0`.

### 4.4 WebSocket Channels
- `trissan.live.main-tank` (auth: `main_tank.view`): `transaction.created`, `reading.created`, `tank.updated`
- `trissan.live.mobile-tanks` (auth: `mobile_tanks.view`): Same events

### 4.5 Frontend Monitoring Pages
1. **Main Tank Page** (#1, `/fuel-monitoring/main-tank`): BarChart hourly liter_masuk vs liter_keluar, real-time transaction table, level gauge, Echo subscription. Export CSV shortcut.
2. **Mobile Tanks Page** (#2 & #3, `/fuel-monitoring/mobile-tanks`): Split 50/50 Browser Tank | Fuel Tanker. All registered tanks shown. AreaChart per tank, transaction history, RFID format `HEX(Sector N)`. Export shortcuts.


## 5. Phase 4: Anomaly Detection Engine & Alarm Management

### 5.1 Tujuan
Mengimplementasikan dua mekanisme deteksi server-side yang terpisah (C1 Pencurian vs C2 Kalibrasi), pencatatan unauthorized scan, alur investigasi alarm, read state per user, dan notifikasi UI (WebSocket + Toast).

### 5.2 Model Data & Migrasi
- **`anomaly_logs`**: `id`, `anomaly_type` (`sudden_change_theft`|`calibration_needed`|`unauthorized_scan`|`geofence_exit`), `tank_id` (nullable), `device_id`, `tag_id`, `transaction_id`, `geofence_id`, `device_event_id` (UUID), `volume_diff` (DECIMAL), `observed_percent`, `threshold_percent`, `sensitivity_mode`, `anomaly_time`, `latitude`, `longitude`, `position_time`, `status_investigasi` (`open`|`investigating`|`resolved`|`false_positive`), `resolution_note`, `resolved_by`, `resolved_at`, `meta` (JSON).
- **`anomaly_reads`**: `anomaly_id`, `user_id`, `read_at`. PK: `(anomaly_id, user_id)`.

### 5.3 Aturan Mesin Deteksi (Server-Side)
1. **C1. Sudden Change Detection (Sinyal Pencurian)**:
   - Per tangki mandiri. Window `W` = 30 min. `unexplained %` = `|Δlevel - expected| / capacity * 100`.
   - Jika `unexplained %` ≥ threshold aktif → Trigger `sudden_change_theft` (open). GPS snapshot dari latest reading `satellites > 0`.
2. **C2. Transfer Reconciliation (Sinyal Kalibrasi SAJA)**:
   - Per sesi Fill/Dispense Line. `variance %` = `|ATG change - flow meter| / flow meter * 100`.
   - Jika `variance %` ≥ toleransi C2 → `calibration_needed` (amber badge). **TIDAK PERNAH** naik ke Theft Alarm.
3. **Unauthorized Scan**: Tag tidak valid/blocked/offline → valve closed, logged sebagai `unauthorized_scan` (slate badge).

### 5.4 Frontend Alarm Page & Handling
- **Alarm Log** (#4, `/fuel-monitoring/alarms`, `alarms.view`): Tabel paginasi, filter type/status/date.
- **Badge Tipe**:
  - `Theft Alarm`: `bg-red-600 text-white font-bold animate-pulse`
  - `Sensor Needs Calibration`: `bg-amber-500 text-white` (no pulse)
  - `Unauthorized Scan`: `bg-slate-600 text-white` (no pulse)
  - `Geofence Exit`: `bg-slate-700 text-white` (no pulse)
- **Alarm Handling** (#5, `alarms.manage`): Modal ganti status, wajib `resolution_note` untuk `resolved`/`false_positive`.
- **Read/Unread** (#6): Per user via `anomaly_reads`. Unread row highlighted. Click → `PATCH /api/v1/dashboard/alerts/{id}/read`.
- **Global Notification**: Badge unread count di header, toast on new alarm via `trissan.live.alarms`.

---

---


## 6. Phase 5: CSV Export & Comprehensive Reporting System

### 6.1 Tujuan
Memberikan fitur ekspor data tabular ke CSV streaming untuk 7 jenis laporan dengan filter lengkap, format Excel Indonesia yang ramah, proteksi formula injection, dan pencatatan audit trail menyeluruh.

### 6.2 7 Jenis Laporan (Detail lengkap di `export-laporan.md`)
1. **Main Tank — Transactions**: Fill/Dispense Line sessions. Max 366 days.
2. **Main Tank — Daily Reconciliation**: Per hari, opening/closing/variance. Max 366 days.
3. **Browser Tank — Refuels**: Dispense Line sessions. Max 366 days.
4. **Browser Tank — Daily Consumption**: Per hari, refuel count & liters. Max 366 days.
5. **Fuel Tanker — Unloads & Vendor Fills**: Fill Line + manual entries. Max 366 days.
6. **Alarm Log**: All anomaly types, status, GPS, resolution. Max 366 days.
7. **Tank Level Readings**: Raw ATG data. **1 tank required, max 31 days**. Include `satellites` column.

### 6.3 Format Preset & Keamanan
- **Standard**: Delimiter `,`, decimal `.`, UTF-8 BOM.
- **Excel (Indonesia)**: Delimiter `;`, decimal `,`, UTF-8 BOM. Langsung buka di Excel ID tanpa error kolom.
- **CSV Injection Protection**: Prefix `'` untuk cell text dimulai dengan `=`, `+`, `-`, `@`, `\t`, `\r`.

### 6.4 Arsitektur Streaming
- `StreamedResponse` dengan `chunkById(1000)`. **Qualified ID column wajib** (e.g., `'transactions.id'`) pada query JOIN.
- Flush per chunk: `if (ob_get_level() > 0) { ob_flush(); } flush();`. Header `X-Accel-Buffering: no`.
- No queue, no temp file. Download start immediately.

### 6.5 Frontend & API
- **Reports Page** (#21, `/reports`, `reports.export` + data view permission): Dropdown report type, dynamic filters, format preset, Export CSV button.
- **Shortcuts dari Monitoring Pages**: Main Tank, Mobile Tanks, Alarm Log → redirect ke `/reports` dengan filters prefilled.
- **Audit Log**: `export_logs` table (`user_id`, `report_type`, `filters` JSON, `row_count`, `created_at`).
- Endpoint: `GET /api/v1/reports/{report}/export.csv?from=...&to=...&format=...&...`


## 7. Phase 6: Live Fleet Map & Server-Side Geofencing

### 7.1 Tujuan
Menampilkan visualisasi posisi armada truk (Browser Tank & Fuel Tanker) secara langsung pada peta OpenStreetMap via Leaflet, memungkinkan admin menggambar batas geofence poligonal, serta mengevaluasi batas wilayah secara server-side pada setiap data GPS masuk.

### 7.2 Model Data & Migrasi
- **`geofences`**: `id`, `site_id`, `name`, `boundary` (POLYGON SRID 4326 + SPATIAL INDEX), `applies_to` (SET: `browser_tank`, `fuel_tanker`), `is_active` (boolean), `created_by`, `updated_by`, timestamps.
- **`tank_geofence_state`**: `tank_id`, `geofence_id` (PK), `is_inside` (boolean), `consecutive_count` (integer debounce counter), `updated_at`.

### 7.3 Mesin Evaluasi Server-Side & Debounce
1. **Pemicu**: Job ingest AVL untuk setiap record mobile tank dengan `satellites > 0`. Record stale (`satellites = 0`) diabaikan.
2. **Kueri Spasial**: `ST_Within(POINT(longitude, latitude), geofences.boundary)` via MySQL.
3. **Debounce**: `geofence_exit_confirm_readings = 2` consecutive valid readings outside → flip state `is_inside = false` → raise `geofence_exit` alarm. Same untuk re-entry (tidak auto-resolve, hanya append note).

### 7.4 Frontend Live Map
- **Map Viewer** (#7, `/fuel-monitoring/map`, `map.view`): Leaflet.js + OpenStreetMap. Markers update live via `trissan.live.map`. Tooltip: tank name, division, level, satellites. Stale fix indicator (muted marker "signal lost" jika > `position_max_age_minutes`).
- **Geofence Manager** (#8, `geofences.manage`): Leaflet.draw tools. Modal: name, applies_to (Browser/Tanker/Both), is_active. Endpoints: `GET|POST|PATCH|DELETE /api/v1/admin/geofences`.

---

## 8. Phase 7: System Administration, Operations & Storage

### 8.1 Tujuan
Menyediakan pengelolaan sensitivitas deteksi (Low/Medium/High) beserta pratinjau kalkulasi dalam liter, backup otomatis database MySQL ke S3-compatible storage dari UI, serta konfigurasi parameter situs.

### 8.2 Model Data & Migrasi
- **`backup_settings`**: `id`, `site_id`, `provider`, `endpoint`, `region`, `bucket`, `path_prefix`, `use_path_style`, `access_key_id` (encrypted), `secret_access_key` (encrypted), `schedule_time`, `retention_days`, `enabled`, `last_connection_test_at`, `last_connection_test_ok`, `updated_by`, timestamps.
- **`backup_runs`**: `id`, `site_id`, `trigger` (`scheduled`|`manual`|`test`), `status` (`running`|`success`|`failed`), `started_at`, `finished_at`, `size_bytes`, `object_key`, `error_message`, `triggered_by`.

### 8.3 Alert Sensitivity Management
- **Page** (#15, `/admin/sensitivity`, `sensitivity.manage`): Per alert type (C1 Theft, C2 Calibration), pilih mode Low/Medium/High, edit threshold values.
- **Preset Default**: Low (Browser 15%, Tanker 15%, Main 4%, C2 2%), Medium (10%, 10%, 2%, 1%), High (5%, 5%, 1%, 0.5%).
- **Calculation Preview**: Real-time preview threshold dalam liter untuk setiap registered tank.
- **Validasi**: `Low > Medium > High`, semua > 0 dan ≤ 100. Dialog konfirmasi jika menurunkan sensitivitas.

### 8.4 S3-Compatible Backup Storage
- **Page** (#19 & #20, `/admin/backup`, `backup.manage`): 4-step setup: Save Config → Test Connection → Run Test Backup → Enable Schedule.
- **Storage Providers**: AWS S3, Cloudflare R2, MinIO, Wasabi, Backblaze B2, DigitalOcean Spaces, dll. (any S3-compatible).
- **Test Connection**: Check HTTPS → auth key → bucket access → write/read/delete marker object.
- **Run Backup Now**: Ad-hoc mysqldump → gzip → S3 upload → verify size.
- **Schedule**: Daily default 02:00 site time, retention 30 days. Warning banner if last backup failed or > 2 days overdue.

### 8.5 Site Settings
- **Page** (#16, `/admin/settings`, `settings.manage`): Timezone (IANA format), operational params: `window_minutes`, `eval_grace_minutes`, `settle_seconds`, `position_max_age_minutes`, `rfid_poll_seconds`, `reading_retention_days`.

---

## 9. Critical Engineering Gotchas

1. **chunkById() Ambiguous Column**: Query JOIN wajib qualified ID: `'transactions.id'`, bukan `'id'`.
2. **Streaming ob_flush()**: Wrap dengan `if (ob_get_level() > 0)`. Header `X-Accel-Buffering: no`.
3. **GPS Validity (`satellites > 0`)**: FMC225 repeats stale fix, tidak kosongkan koordinat. Geofence check dan alarm snapshot hanya pakai reading valid.
4. **C2 TIDAK Boleh Escalate ke Theft**: Variance berapapun tetap `calibration_needed` (amber), never `sudden_change_theft` (red).
5. **Backup Encryption**: `access_key_id` & `secret_access_key` encrypted. `APP_KEY` harus backup terpisah dari DB.
6. **Inertia Props vs Echo**: Props hanya first paint. Komponen wajib subscribe Echo channel untuk real-time updates.

---

## 10. Summary Verification Checklist

### Phase 1: Foundation
- [ ] Users, Roles, Permissions seeded
- [ ] Login/Logout (Sanctum session)
- [ ] AppSidebar renders conditional menu
- [ ] Dark/Light mode toggle
- [ ] User & Role management pages

### Phase 2: Fleet & Hardware
- [ ] Register Tank, Hardware, RFID
- [ ] Assign/Replace hardware atomic
- [ ] Vendor Fill manual entry
- [ ] RFID sync endpoint (304 when unchanged)

### Phase 3: Ingestion & Monitoring
- [ ] Edge API (transactions, site readings)
- [ ] Internal AVL API (localhost only)
- [ ] Main Tank page (BarChart, live table)
- [ ] Mobile Tanks page (split Browser/Tanker, AreaChart, live)

### Phase 4: Anomaly & Alarms
- [ ] C1 Sudden Change detection (server-side)
- [ ] C2 Transfer Reconciliation (calibration only)
- [ ] Unauthorized Scan logging
- [ ] Alarm Log page (badges, filters, GPS link)
- [ ] Alarm handling (status, resolution note)
- [ ] Per-user read/unread state
- [ ] Global notification (badge, toast)

### Phase 5: CSV Export
- [ ] 7 reports implemented
- [ ] Streaming with qualified chunkById()
- [ ] CSV injection protection
- [ ] Excel (Indonesia) format preset
- [ ] Export shortcuts from monitoring pages
- [ ] Export audit log

### Phase 6: Map & Geofencing
- [ ] Live fleet map (Leaflet + OpenStreetMap)
- [ ] Marker update via Echo `trissan.live.map`
- [ ] Stale fix indicator (signal lost)
- [ ] Geofence drawing tools (Leaflet.draw)
- [ ] Server-side ST_Within check (satellites > 0)
- [ ] Debounce (consecutive_count)
- [ ] Geofence exit alarm

### Phase 7: Admin & Operations
- [ ] Alert Sensitivity page (L/M/H, calculation preview)
- [ ] Site Settings (timezone, operational params)
- [ ] Backup storage config (4-step setup)
- [ ] Test Connection, Run Test Backup
- [ ] Backup schedule, Run Now, history
- [ ] Warning banner (backup overdue/failed)

---

**End of Website Specification & Phased Roadmap**

---
