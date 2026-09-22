# Spec: CSV Export & Reports (Laporan)

## Tujuan
Memberikan kemampuan ekspor data ke CSV untuk 7 jenis laporan dengan filter tanggal, format yang bisa dibuka Excel Indonesia, dan kontrol permission yang ketat. Setiap ekspor tercatat dalam audit log.

Fokus spesifikasi ini adalah **implementasi aplikasi web (backend REST API streaming, frontend React/Inertia, format CSV, dan kontrol hak akses)** — bukan urusan hardware IoT/gateway.

## Ruang Lingkup
- [x] **TERMASUK:**
  - 7 jenis laporan (Main Tank transaksi & rekonsiliasi harian, Browser Tank isi ulang & konsumsi harian, Fuel Tanker unload & vendor fill, Alarm Log, raw level readings)
  - Filter per laporan (date range, tank, direction, type, status)
  - 2 preset format: Standard (comma, decimal point) dan Excel Indonesia (semicolon, decimal comma)
  - Streamed download (chunked query, no temp file, no queue)
  - Permission gating: `reports.export` + view permission data yang di-export
  - Export log (audit trail): siapa, laporan apa, filter apa, row count, kapan
  - Shortcut "Export CSV" dari monitoring pages (Main Tank, Mobile Tanks, Alarm Log) yang buka Reports dengan filter pre-filled
  - Range limit: reports 1–6 up to 366 days; report 7 (raw readings) 1 tank + up to 31 days
  - CSV injection protection (prefix `'` pada cell yang mulai dengan `=`, `+`, `-`, `@`, `\t`, `\r`)
  - Timestamps dalam site timezone sebagai `YYYY-MM-DD HH:mm:ss`; timezone name di file name
  - Volumes dengan 2 decimal, no thousands separator
  - UTF-8 with BOM (Excel compatibility)

- [ ] **TIDAK TERMASUK (deferred Phase 2):**
  - Scheduled/emailed reports
  - PDF export
  - Excel (.xlsx) binary format
  - Custom report builder
  - Background queue untuk large exports (semua streamed langsung)

---

## 1. 7 Jenis Laporan

### Report 1: Main Tank — Transactions
**One row per:** Fill Line / Dispense Line session pada Main Tank

**Columns:**
- `started_at` — waktu mulai (site timezone)
- `ended_at` — waktu selesai (site timezone)
- `main_tank` — nama Main Tank
- `direction` — `In` (fill_to_main) atau `Out` (dispense_to_browser)
- `tank` — Fuel Tanker (jika In) atau Browser Tank (jika Out)
- `division` — `fuel_tanker` atau `browser_tank`
- `rfid_tag` — RFID tag yang digunakan (format HEX)
- `line_device_id` — device ID line unit (Fill/Dispense)
- `liters` — volume (2 decimal)
- `sync_status` — `live` atau `backfilled`

**Filters:**
- Date range (required)
- Main Tank (dropdown, optional)
- Direction: All / In / Out
- Tank (autocomplete, optional)

### Report 2: Main Tank — Daily Reconciliation
**One row per:** Main Tank per hari

**Columns:**
- `date` — tanggal (YYYY-MM-DD site timezone)
- `main_tank` — nama Main Tank
- `opening_level_l` — level awal hari (00:00 site time)
- `received_l` — total `fill_to_main` hari itu
- `dispensed_l` — total `dispense_to_browser` hari itu
- `expected_closing_l` — opening + received - dispensed
- `closing_level_l` — level akhir hari (24:00 site time, atau latest jika hari ini)
- `variance_l` — closing - expected
- `variance_percent` — variance / capacity × 100
- `is_complete` — `true` jika bukan hari ini, `false` jika hari ini (belum selesai)

**Filters:**
- Date range (required)
- Main Tank (dropdown, optional) — jika dikosongkan/"All", hasilnya **satu baris per Main Tank per hari** (tidak digabung); konsisten dengan definisi "One row per: Main Tank per hari"

**Logic & Rules:**
- `opening_level_l` / `closing_level_l` = latest ATG reading at or before 00:00 / 24:00 site time of that day (current day: latest reading, `is_complete = false`); blank jika no reading exists.
- `received_l` = Σ `fill_to_main` liters dan `dispensed_l` = Σ `dispense_to_browser` liters, dihitung pada hari session berakhir.
- Ini **murni reporting**, tidak memicu alarm theft/calibration (deteksi theft tetap di C1/C2).

---

### Report 3: Browser Tank — Refuels
**One row per:** Dispense Line session (Browser Tank diisi dari Main Tank)

**Columns:**
- `started_at`
- `ended_at`
- `browser_tank` — nama Browser Tank
- `rfid_tag`
- `liters`
- `main_tank` — Main Tank sumber
- `line_device_id` — Dispense Line device ID
- `sync_status`

**Filters:**
- Date range (required)
- Browser Tank(s) — multi-select atau All

---

### Report 4: Browser Tank — Daily Consumption
**One row per:** Browser Tank per hari

**Columns:**
- `date`
- `browser_tank`
- `refuel_count` — jumlah pengisian pada hari tersebut
- `liters_received` — total liter diterima

**Filters:**
- Date range (required)
- Browser Tank(s) — multi-select atau All

**Definisi:** `liters_received` = Σ `dispense_to_browser` liters hari itu — bahan bakar yang disuplai ke unit tersebut, yang merupakan angka konsumsinya karena Browser Tank adalah pengguna akhir (end consumer yang membakar BBM, bukan memindahkannya).

---

### Report 5: Fuel Tanker — Unloads & Vendor Fills
**One row per:** Fill Line session atau vendor fill

**Columns:**
- `started_at`
- `ended_at`
- `fuel_tanker`
- `type` — `Unload to Main Tank` (fill_to_main) atau `Vendor fill` (vendor_fill)
- `rfid_tag` — blank untuk vendor fill
- `liters`
- `main_tank` — blank untuk vendor fill
- `line_device_id` — blank untuk vendor fill
- `entered_by` — nama user (khusus input manual vendor fill), blank untuk sensor
- `sync_status` — blank untuk vendor fill

**Filters:**
- Date range (required)
- Fuel Tanker(s) — multi-select atau All
- Type: All / Unload / Vendor fill


---

### Report 6: Alarm Log
**One row per:** Alarm entry

**Columns:**
- `anomaly_time` — waktu anomaly terdeteksi
- `type` — `Theft Alarm`, `Sensor Needs Calibration`, `Unauthorized Scan`, `Geofence Exit`
- `tank` — nama tank (blank untuk unauthorized scan jika tag unregistered)
- `division`
- `rfid_tag` — tag yang terlibat (raw UID untuk unauthorized scan)
- `volume_diff_l` — volume difference (blank untuk unauthorized scan & geofence exit)
- `observed_percent` — observed % (blank untuk unauthorized scan & geofence exit)
- `threshold_percent` — threshold yang dipakai (blank untuk unauthorized scan & geofence exit)
- `sensitivity_mode` — Low / Medium / High (blank untuk unauthorized scan & geofence exit)
- `status` — open / investigating / resolved / false_positive
- `resolution_note`
- `resolved_by` — nama user
- `resolved_at`
- `latitude` — GPS coordinate (blank jika N/A atau unavailable)
- `longitude`

**Filters:**
- Date range (required)
- Type: All / Theft Alarm / Sensor Needs Calibration / Unauthorized Scan / Geofence Exit
- Status: All / open / investigating / resolved / false_positive
- Tank (autocomplete, optional)

---

### Report 7: Tank Level Readings (Raw Sensor Data)
**One row per:** ATG reading

**Columns:**
- `timestamp` — waktu reading (site timezone)
- `tank` — nama tank
- `level_l` — level dalam liter
- `latitude`
- `longitude`
- `satellites` — jumlah satelit GPS (critical untuk validasi GPS fix; `0` atau NULL = koordinat basi/stale, bukan fix baru — §7.G PRD)
- `device_id` — IMEI (FMC225) atau router device ID

**Filters:**
- Tank (required, single select) — **WAJIB**
- Date range (required, max 31 days) — **limit ketat karena high volume**

**Catatan:**
- Raw readings dipurge setelah `reading_retention_days` (default 90 hari) via monthly partition drop
- Report ini hanya bisa pull data dalam window tersebut
- Limit 31 hari karena volume tinggi: ~2,880 rows/day/tank × 50 tanks × 31 days = ~4.3 juta rows
- **Kolom `satellites` penting untuk audit kualitas GPS:** tanpa ini, user tidak bisa membedakan mana koordinat yang valid (`satellites > 0`) dan mana yang stale (device mengulang posisi terakhir saat tidak ada fix)



---

## 2. Format Presets

### Standard Format
- **Delimiter:** `,` (comma)
- **Decimal separator:** `.` (point)
- **Encoding:** UTF-8 with BOM
- **Use case:** Import ke tools lain, scripting

### Excel (Indonesia) Format
- **Delimiter:** `;` (semicolon)
- **Decimal separator:** `,` (comma)
- **Encoding:** UTF-8 with BOM
- **Use case:** Langsung double-click buka di Excel dengan regional setting Indonesia

**Kenapa semicolon?** Regional setting Indonesia: decimal separator = `,`, list separator = `;`. Jika CSV pakai `,` delimiter tapi angka juga pakai `,` decimal → Excel bingung → semua data jadi 1 column. Solusi: semicolon delimiter + comma decimal.

---

## 3. Security: CSV Injection Protection

Text fields (tank names, resolution notes, user names, dll.) yang mulai dengan karakter formula Excel (`=`, `+`, `-`, `@`, `\t`, `\r`) harus di-prefix `'` (single quote) supaya Excel tidak execute sebagai formula.

**Implementation:**
```php
function escapeCSVCell($value) {
    if (is_string($value) && preg_match('/^[=+\-@\t\r]/', $value)) {
        return "'" . $value;
    }
    return $value;
}
```

**Example:**
- Input: `=1+1` (resolution note)
- Output: `'=1+1` — Excel render as text, bukan evaluate sebagai formula

Numeric columns tidak pernah diubah.

---

## 4. File Naming Convention

```
{report_type}_{from_date}_to_{to_date}_{timezone}.csv
```

**Examples:**
- `main-tank-transactions_2026-01-01_to_2026-01-31_Asia-Jakarta.csv`
- `alarm-log_2026-09-01_to_2026-09-22_Asia-Jakarta.csv`

Timezone name di file name supaya penerima file tahu data ini dalam timezone apa.

---

## 5. Range Limits & Validation

| Report | Date range limit | Other constraints |
|--------|------------------|-------------------|
| 1–6 | Up to 366 days | — |
| 7 (raw readings) | Up to 31 days | Tank (single) required |

**Backend validation:**
```php
$maxDays = $reportType === 'tank-level-readings' ? 31 : 366;
$daysDiff = $from->diffInDays($to);

if ($daysDiff > $maxDays) {
    throw ValidationException::withMessages([
        'to' => "Date range cannot exceed {$maxDays} days."
    ]);
}

if ($reportType === 'tank-level-readings' && !$request->tank_id) {
    throw ValidationException::withMessages([
        'tank_id' => 'Tank selection is required for raw level readings export.'
    ]);
}
```


---

## 6. Streaming Implementation (No Queue, No Temp File)

Export langsung stream ke browser via chunked query. **Tidak pakai queue, tidak pakai temp file** — row-by-row langsung ditulis ke output buffer.

**Critical: Streaming yang benar-benar streaming**

Tanpa `ob_flush(); flush();` per chunk, PHP dan web server (Nginx/FPM) bisa nge-buffer seluruh output sampai selesai baru dikirim ke browser — jadi behaviour-nya sama aja kayak generate lalu download, bukan progressive streaming. Untuk memastikan user langsung dapat feedback (download starts immediately), perlu flush eksplisit dan disable buffering di web server.

**Laravel implementation:**
```php
use Symfony\Component\HttpFoundation\StreamedResponse;

public function export(Request $request, string $reportType)
{
    // Validate permissions
    $this->authorize('export-reports');
    $this->authorize('view', $this->getResourceClass($reportType));
    
    // Validate filters & range
    $validated = $request->validate($this->getValidationRules($reportType));
    
    // Build query (NOT executed yet)
    $query = $this->buildQuery($reportType, $validated);
    
    // Log export start
    $exportLog = ExportLog::create([
        'user_id' => auth()->id(),
        'report_type' => $reportType,
        'filters' => $validated,
        'format_preset' => $validated['format'] ?? 'standard',
        'row_count' => null, // filled on completion
    ]);
    
    // Stream response
    return new StreamedResponse(function () use ($query, $validated, $exportLog, $reportType) {
        $format = $validated['format'] ?? 'standard';
        $delimiter = $format === 'excel_id' ? ';' : ',';
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Header row
        fputcsv($output, $this->getHeaders($reportType), $delimiter);
        
        // Flush header immediately (safely check ob_get_level to avoid warnings)
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
        
        $rowCount = 0;
        
        // IMPORTANT: Gunakan chunkById() dengan nama kolom yang ter-qualify
        // misal: 'transactions.id', 'anomaly_logs.id', 'tank_level_readings.id'
        // Jika query menggunakan JOIN ke tabel lain (tanks, users) yang juga punya kolom id,
        // tanpa kualifikasi tabel akan terjadi ambiguous column atau cursor ke id yang salah.
        $qualifiedIdColumn = $this->getQualifiedIdColumn($reportType);
        
        $query->chunkById(1000, function ($rows) use ($output, $delimiter, $format, &$rowCount) {
            foreach ($rows as $row) {
                $csvRow = $this->formatRow($row, $format);
                fputcsv($output, $csvRow, $delimiter);
                $rowCount++;
            }
            
            // Flush each chunk to ensure progressive streaming
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }, $column = $qualifiedIdColumn, $alias = 'id');
        
        fclose($output);
        
        // Update export log with final count
        $exportLog->update(['row_count' => $rowCount]);
    }, 200, [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $this->getFileName() . '"',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'X-Accel-Buffering' => 'no', // Disable Nginx buffering for true streaming
    ]);
}
```

**Why chunkById() instead of chunk():**
- `chunk()` menggunakan offset-based pagination (`LIMIT 1000 OFFSET 0`, `LIMIT 1000 OFFSET 1000`, dst.)
- Jika range mencakup "hari ini" dan tabel menerima insert live (setiap ~30 detik dari device), row baru yang masuk di tengah proses export bisa geser offset → **baris ke-skip atau ke-duplikat**
- `chunkById()` menggunakan cursor berdasarkan kolom `id` (primary key) → stabil meskipun ada insert baru selama export berjalan
- Untuk export dengan `to date` = hari berjalan (yang kemungkinan besar sering terjadi), `chunkById()` adalah pilihan aman
- **Penting:** Pastikan pass kolom ID yang di-qualify (misal `$column = 'transactions.id'`) jika query menggunakan `JOIN`, agar SQL tidak `ambiguous column` atau melakukan pagination di atas `id` yang salah.

**Why ob_flush() + flush() + X-Accel-Buffering: no:**
- PHP output buffering dan Nginx/FPM buffering bisa menahan seluruh response sampai selesai
- Tanpa flush eksplisit, user tidak akan melihat "download started" sampai query selesai
- `X-Accel-Buffering: no` memberitahu Nginx untuk tidak buffer response
- Panggilan `ob_flush()` dibungkus dengan `if (ob_get_level() > 0)` agar tidak memunculkan PHP notice "failed to flush buffer" jika tidak ada output buffer aktif (mengingat `StreamedResponse` tidak memanggil `ob_start()` otomatis).

---

## 7. Permission Gating

**Rule:** User butuh **DUA** permissions:
1. `reports.export` — base permission untuk akses Reports page
2. View permission dari data yang di-export:
   - Main Tank reports → `main_tank.view`
   - Browser Tank reports → `mobile_tanks.view`
   - Fuel Tanker reports → `mobile_tanks.view`
   - Alarm Log → `alarms.view`
   - Raw readings → `main_tank.view` atau `mobile_tanks.view`

**Backend enforcement:**
```php
Gate::define('export-main-tank-transactions', function (User $user) {
    return $user->hasPermission('reports.export') 
        && $user->hasPermission('main_tank.view');
});

Gate::define('export-alarm-log', function (User $user) {
    return $user->hasPermission('reports.export') 
        && $user->hasPermission('alarms.view');
});
```

**Frontend:** Report dropdown hanya show reports yang user punya permission-nya.

---

## 8. Export Audit Log

**Table:** `export_logs`

**Columns:**
- `id`
- `user_id` (FK to users)
- `report_type` (enum: 7 report types)
- `filters` (JSON — date range, tank selections, type/status filters)
- `format_preset` (`standard` | `excel_id`)
- `row_count` (integer, filled setelah stream selesai)
- `created_at` (timestamp)

**Purpose:**
- Audit trail: siapa export apa kapan
- Usage monitoring: report mana yang paling sering di-pull
- Troubleshooting: jika user complain "export saya kosong", bisa check filters yang mereka pakai

**Visibility:** Only admins/super admins see this log (permission `reports.manage`, future).

---

## 9. Frontend: Reports Page (`/reports`)

### Route & Permission
- **Route:** `/reports`
- **Permission:** `reports.export` (halaman tidak muncul di nav jika user tidak punya permission ini)

### Layout
Single-page form dengan sections:

```
┌─────────────────────────────────────────────┐
│  Export Reports                              │
│                                              │
│  ┌─────────────────────────────────────┐   │
│  │ Report Type (Dropdown)               │   │
│  │ [Main Tank - Transactions ▼]         │   │
│  └─────────────────────────────────────┘   │
│                                              │
│  ┌──────────────┬──────────────┐           │
│  │ From Date    │ To Date       │           │
│  │ [2026-01-01] │ [2026-01-31] │           │
│  └──────────────┴──────────────┘           │
│  Max range: 366 days                        │
│                                              │
│  ┌─────────────────────────────────────┐   │
│  │ Filters (dynamic based on report)    │   │
│  │ • Main Tank: [All ▼]                 │   │
│  │ • Direction: [All ▼]                 │   │
│  │ • Tank: [Start typing...]            │   │
│  └─────────────────────────────────────┘   │
│                                              │
│  ┌─────────────────────────────────────┐   │
│  │ Format                               │   │
│  │ ○ Standard (CSV)                     │   │
│  │ ● Excel (Indonesia)                  │   │
│  └─────────────────────────────────────┘   │
│                                              │
│  [Export CSV ↓]                             │
└─────────────────────────────────────────────┘
```

### Report Type Dropdown
**Options** (only yang user punya permission):
```
Main Tank
  - Transactions
  - Daily Reconciliation

Browser Tank
  - Refuels (Dispense Line sessions)
  - Daily Consumption

Fuel Tanker
  - Unloads & Vendor Fills

Alarms
  - Alarm Log

Raw Data
  - Tank Level Readings
```

Ganti report type → filters section update (dynamic).

### Export Button & Error Handling
1. Click "Export CSV"
2. Frontend GET ke `/api/v1/reports/{reportType}/export.csv?from=...&to=...&format=...&filters...`
3. Backend validate permissions & filters
4. Browser receive streamed download
5. File saved locally dengan naming convention

**Error handling:**
- Permission denied → 403 toast
- Validation error (e.g., range too wide) → red validation message below field
- Server error → generic toast "Export failed, please try again"

---

## 10. Export Shortcuts dari Monitoring Pages

**Goal:** User bisa langsung export dari monitoring page tanpa navigate ke Reports dulu.

### Main Tank Page (`/fuel-monitoring/main-tank`)
**Button:** "Export CSV" (top-right corner, next to date range selector)

**Behavior:**
- Click → redirect ke `/reports?report=main-tank-transactions&from={current_from}&to={current_to}&main_tank={current_tank}`
- Reports page pre-filled dengan filters dari Main Tank page

### Mobile Tanks Page (`/fuel-monitoring/mobile-tanks`)
**Buttons:** 
- Browser Tank section: "Export Refuels" (above table)
- Fuel Tanker section: "Export Unloads" (above table)

**Behavior:**
- Browser: redirect ke `/reports?report=browser-tank-refuels&from={last_30_days}&to={today}&tanks={all_visible}`
- Fuel Tanker: redirect ke `/reports?report=fuel-tanker-unloads&from={last_30_days}&to={today}&tanks={all_visible}`

### Alarm Log Page (`/fuel-monitoring/alarms`)
**Button:** "Export Alarm Log" (top-right, next to filters)

**Behavior:**
- Click → redirect ke `/reports?report=alarm-log&from={current_from}&to={current_to}&type={current_filter_type}&status={current_filter_status}`

**Permission check:** Button hanya muncul jika user punya `reports.export`.

---

## 11. API Endpoint

### `GET /api/v1/reports/{reportType}/export.csv`

**Auth:** Sanctum session

**Permission:** `reports.export` + view permission dari data

**Path param:**
- `reportType` — one of: `main-tank-transactions`, `main-tank-daily-reconciliation`, `browser-tank-refuels`, `browser-tank-daily-consumption`, `fuel-tanker-unloads`, `alarm-log`, `tank-level-readings`

**Query params (semua optional kecuali ditandai required):**
- `from` (required) — date `YYYY-MM-DD`
- `to` (required) — date `YYYY-MM-DD`
- `format` — `standard` (default) atau `excel_id`
- Report-specific filters (see section 1)

**Response:**
- Success: Streamed CSV file
- 403: Permission denied
- 422: Validation error

**Example requests:**
```
GET /api/v1/reports/main-tank-transactions/export.csv
  ?from=2026-01-01&to=2026-01-31&main_tank=1&direction=in&format=excel_id

GET /api/v1/reports/alarm-log/export.csv
  ?from=2026-09-01&to=2026-09-22&type=sudden_change_theft&status=open
```

---

## 12. Timezone Handling

**Rule:** Semua timestamps di CSV adalah **site timezone**, bukan UTC.

**Implementation:**
```php
// Get site timezone from settings
$timezone = Site::first()->timezone; // e.g., 'Asia/Jakarta'

// Convert UTC timestamp to site timezone for display
$displayTime = $record->started_at
    ->setTimezone($timezone)
    ->format('Y-m-d H:i:s');
```

**Daily aggregations (reports 2 & 4):**
- "Day" boundaries = 00:00–24:00 dalam site timezone, bukan UTC
- Query pakai `DATE(CONVERT_TZ(timestamp, 'UTC', :timezone))`

---

## 13. Testing Checklist

### Backend
- [ ] Each report returns correct columns
- [ ] Filters work correctly
- [ ] Range validation (366 days untuk 1–6, 31 days untuk 7)
- [ ] Tank required validation for report 7
- [ ] Permission gating (403 jika user tidak punya view permission)
- [ ] CSV injection protection
- [ ] Format preset: Standard vs Excel ID
- [ ] Timezone conversion benar
- [ ] File name correct dengan timezone
- [ ] Export log recorded dengan row count
- [ ] Streaming works untuk large dataset (test 100k+ rows)
- [ ] UTF-8 BOM present

### Frontend
- [ ] Reports page hanya muncul jika user punya `reports.export`
- [ ] Report dropdown hanya show reports user punya permission
- [ ] Dynamic filters update saat ganti report type
- [ ] Date range validation
- [ ] Export button trigger download
- [ ] Shortcut buttons dari monitoring pages pre-fill filters correctly
- [ ] Error toast jika permission denied atau validation failed

### Integration
- [ ] Export dari Main Tank page → Reports page pre-filled
- [ ] Export dari Alarm Log page → Reports page pre-filled
- [ ] Downloaded CSV bisa dibuka Excel tanpa error
- [ ] Excel Indonesia format: columns split correctly, numbers recognized
- [ ] Decimal precision = 2 for volumes
- [ ] Blank cells untuk N/A values (tidak `NULL` text)

---

## Kriteria Selesai

- [ ] 7 reports implemented dengan correct queries & columns
- [ ] 2 format presets (Standard & Excel ID) working
- [ ] Permission gating enforced (backend Gate + frontend visibility)
- [ ] Streaming download (no queue, no temp file)
- [ ] CSV injection protection
- [ ] Export audit log recorded
- [ ] Reports page dengan dynamic filters
- [ ] Export shortcuts dari monitoring pages
- [ ] Range limits validated
- [ ] Timezone handling correct
- [ ] File naming convention followed
- [ ] UTF-8 BOM for Excel compatibility
- [ ] Tested dengan real data

---

## Notes & Gotchas

### 1. Excel Indonesia Format — Why Semicolon?
Regional setting Indonesia: decimal separator = `,`, list separator = `;`. Jika CSV pakai `,` delimiter tapi angka juga pakai `,` decimal → Excel bingung → semua data jadi 1 column. Solution: semicolon delimiter + comma decimal.

### 2. UTF-8 BOM — Why Needed?
Excel (terutama versi lama) default assume CSV = ANSI encoding. Tanpa BOM, text dengan non-ASCII atau karakter khusus bisa corrupt. UTF-8 BOM (3 bytes: `EF BB BF`) di awal file → Excel recognize as UTF-8.

### 3. Streaming vs Queue — Why No Queue?
PRD explicitly deferred background queue (Phase 2). Trade-off:
- **Pro streaming:** Immediate feedback, no job management overhead, simpler implementation
- **Con streaming:** User must keep browser open, no retry jika network fail

Untuk versi ini: streaming sudah memadai karena export adalah user-initiated, on-demand action.

### 4. Report 7 Range Limit — Why 31 Days?
Raw readings = ~30 s interval = 2,880 rows/day/tank. Untuk 50 tanks × 90 days = 12.9 juta rows. Even dengan streaming, query jutaan baris = slow + beban DB tinggi. Limit 31 days × 1 tank = ~89k rows (sangat aman).

### 5. Daily Reconciliation — Not an Alarm
Report 2 shows variance_l & variance_percent, tapi ini **tidak raise alarm**. C1/C2 adalah alarm mechanisms; daily reconciliation adalah **report-only** untuk audit/review.

### 6. chunk() vs chunkById() — Critical untuk Live Data
**Masalah:** `chunk()` pakai offset-based pagination. Jika export range mencakup "hari ini" dan tabel `tank_level_readings` terus menerima insert baru (setiap ~30 detik dari device), row yang masuk di tengah proses export akan menggeser offset pagination → **hasil skip atau duplikat**.

**Solusi:** Pakai `chunkById()` yang menggunakan cursor berdasarkan primary key (`id > last_id`). Ini stabil karena `id` monotonik naik, tidak terpengaruh insert baru.

**Kapan aman pakai chunk() biasa:** Hanya jika `to date` < hari ini (data sudah final, tidak ada insert baru). Tapi karena user kemungkinan besar sering export sampai hari ini, lebih aman selalu pakai `chunkById()`.

**Perhatian Khusus untuk Query dengan JOIN:**
Jika query melakukan JOIN (misalnya Report 1, 3, 5, 6 JOIN ke tabel `tanks` atau `users` yang juga memiliki kolom `id`), argumen `$column` pada `chunkById()` **wajib di-qualify** dengan nama tabel asal (misal: `'transactions.id'`). Tanpa kualifikasi tabel:
- Database akan melempar error `Column 'id' in where clause is ambiguous`, ATAU
- Query cursor keliru mengacu ke kolom `id` milik tabel hasil JOIN (misalnya ID tank), menyebabkan pagination rusak dan export menghasilkan data tidak lengkap/infinite loop.
Gunakan format: `$query->chunkById(1000, $callback, $column = 'nama_tabel.id', $alias = 'id')`.

### 7. Streaming Harus Benar-Benar Streaming
**Masalah:** Tanpa `ob_flush(); flush();` per chunk dan header `X-Accel-Buffering: no`, PHP dan Nginx akan nge-buffer seluruh response sampai selesai baru dikirim ke browser. User tidak akan lihat "download started" sampai query selesai. Selain itu, Symfony `StreamedResponse` tidak otomatis memanggil `ob_start()`, sehingga memanggil `ob_flush()` tanpa buffer aktif bisa memicu PHP notice/warning `"failed to flush buffer"`.

**Solusi:**
1. Panggil `if (ob_get_level() > 0) { ob_flush(); } flush();` setelah menulis header dan di akhir setiap chunk. Pemeriksaan `ob_get_level()` mencegah notice buffer saat output buffering dinonaktifkan di `php.ini`.
2. Tambahkan header `X-Accel-Buffering: no` di response untuk disable Nginx buffering
3. Verifikasi di production: download harus mulai dalam beberapa detik, bukan menunggu query selesai

### 8. Kolom `satellites` di Report 7 — Kenapa Penting
**PRD v2.4 (§7.G) fix:** FMC225 tidak mengirim koordinat kosong saat tidak ada GPS fix — dia mengulang posisi terakhir (stale fix) dengan timestamp baru. Satu-satunya cara membedakan fix baru vs stale adalah `satellites > 0`.

Tanpa kolom `satellites` di Report 7:
- User tidak bisa tahu mana koordinat valid dan mana yang basi
- Analisis rute/geofencing dari raw data akan salah (menganggap stale fix sebagai posisi aktual)
- Audit kualitas GPS tidak bisa dilakukan

**Kasus penggunaan:** Operator ingin cek kenapa geofence alarm tidak muncul padahal truck sudah keluar → pull Report 7, lihat `satellites`, sadar GPS signal lost (semua `satellites = 0`) → bukan bug sistem, tapi hardware/coverage issue.

---

## Related PRD Sections
- §2.I — CSV Export (Reports) business rules
- §5 — API endpoint `/api/v1/reports/{report}/export.csv`
- §6 Feature #21 — Frontend Reports page & Export CSV shortcuts
- §7.C — Deferred items (scheduled reports, PDF, Excel .xlsx, custom report builder)


