# DummyHardSim - Fixes Applied

## Issues Fixed (October 9, 2026)

### 1. ✅ HTML/JavaScript Syntax Error
**Problem:** Malformed HTML with misplaced closing `</div>` tags (lines 139-141) breaking the JavaScript structure.

**Fix:** Removed the errant closing div tags that were inserted between the `init()` function and `proxyPost()` function.

**Location:** `index.html` lines 139-141

---

### 2. ✅ Missing ATG Auto Button Handler
**Problem:** The "Start Auto" button for Main Tank ATG (`btn-atg-auto`) was rendered but had no click handler, making automatic ATG readings impossible.

**Fix:** Added complete event handler with start/stop toggle functionality, mirroring the mobile unit auto behavior.

**Location:** `index.html` lines 188-201

```javascript
document.getElementById('btn-atg-auto').onclick = (e) => {
    if (autoAtgInterval) {
        clearInterval(autoAtgInterval);
        autoAtgInterval = null;
        e.target.innerText = "Start Auto";
        e.target.className = "btn btn-light btn-sm py-0";
    } else {
        e.target.innerText = "Stop Auto";
        e.target.className = "btn btn-danger btn-sm py-0";
        autoAtgInterval = setInterval(() => {
            document.getElementById('btn-atg-send').click();
        }, config.simulation.atg_interval_sec * 1000);
    }
};
```

---

### 3. ✅ Field Name Mismatch for AVL Endpoint
**Problem:** AVL ingestion controller expects `fuel_level_liters` but simulator was sending `level_liters`, causing validation errors.

**Fix:** Changed field name in mobile unit payload from `level_liters` to `fuel_level_liters`.

**Location:** `index.html` line 163

**Backend Reference:** `app/Http/Controllers/Api/AvlIngestionController.php` line 31

---

### 4. ✅ RFID Sync Incorrect HTTP Method
**Problem:** RFID sync endpoint is GET, but `syncRfid()` function was calling `proxyPost()` which always used POST for non-null payloads.

**Fix:** 
- Enhanced `proxyPost()` to accept optional `method` parameter
- Updated `syncRfid()` to explicitly pass `'GET'` as the method
- Updated Python proxy handler to respect the `method` field from client

**Locations:**
- `index.html` lines 150-168 (enhanced proxyPost)
- `index.html` line 287 (syncRfid GET call)
- `simulator.py` line 70 (method parameter support)

---

### 5. ✅ crypto.randomUUID() Compatibility
**Problem:** `crypto.randomUUID()` might not be available in all browsers/contexts.

**Fix:** Added fallback UUID generator function that works in all browsers.

**Location:** `index.html` lines 139-148

---

### 6. ✅ Better Error Logging
**Problem:** All HTTP responses logged as success, even errors.

**Fix:** Enhanced `proxyPost()` to differentiate between success, 304 Not Modified, and errors in log output.

**Location:** `index.html` lines 160-166

---

### 7. ✅ Documentation Fix
**Problem:** README.md incorrectly showed `Hash::make()` instead of `hash('sha256', ...)` for token hashing.

**Fix:** Corrected the token hashing example to match what `VerifyDeviceToken` middleware expects.

**Location:** `README.md` line 49

**Backend Reference:** `app/Http/Middleware/VerifyDeviceToken.php` line 25

---

## Testing the Simulator

### Prerequisites
1. **Start Laravel backend:**
   ```bash
   php artisan serve
   ```

2. **Start MySQL database** (if not running)

3. **Seed hardware devices with matching tokens:**
   ```php
   // In Laravel Tinker or a seeder
   DB::table('hardware_devices')->insert([
       ['device_id' => 'SIM_MOBILE_001', 'device_type' => 'mobile_unit', 'tank_id' => 1, 
        'status' => 'active', 'api_token_hash' => hash('sha256', 'sfdgsdfefad')],
       ['device_id' => 'SIM_FILL_001', 'device_type' => 'fill_line', 'tank_id' => null, 
        'status' => 'active', 'api_token_hash' => hash('sha256', 'g4h45g6hryh46t')],
       ['device_id' => 'SIM_DISPENSE_001', 'device_type' => 'dispense_line', 'tank_id' => null, 
        'status' => 'active', 'api_token_hash' => hash('sha256', 'sdff43gfe43')],
       ['device_id' => 'SIM_ATG_001', 'device_type' => 'main_tank_atg', 'tank_id' => 1, 
        'status' => 'active', 'api_token_hash' => hash('sha256', 'e5g34tg4tg4tg5rg4t')],
   ]);
   ```

4. **Ensure tanks exist:**
   ```php
   DB::table('tanks')->insert([
       ['tank_id' => 1, 'site_id' => 1, 'name' => 'Main Tank', 'tank_type' => 'main_tank', 
        'capacity_liters' => 50000, 'min_threshold' => 5000],
   ]);
   ```

5. **Ensure Site exists:**
   ```bash
   php artisan db:seed --class=SiteSeeder
   ```

### Start the Simulator
```bash
cd DummyHardSim
./start.sh
```

Then open: http://localhost:3030

### What to Test
- ✅ Mobile Unit: Drag map marker, click "Send AVL Record", click "Start Auto"
- ✅ Main Tank ATG: Change level, click "Send ATG Batch", click "Start Auto"  
- ✅ Fill Line: Change liters, click "Send Fill", click "Sync RFID"
- ✅ Dispense Line: Change liters, click "Send Dispense", click "Sync RFID"
- ✅ Check terminal logs for API responses

---

## API Endpoints Called

| Device | Endpoint | Method | Auth |
|--------|----------|--------|------|
| Mobile Unit | `/api/internal/ingest/avl` | POST | None (internal) |
| Fill/Dispense | `/api/v1/transactions` | POST | Bearer + X-Device-ID |
| Fill/Dispense | `/api/v1/sync/rfid` | GET | Bearer + X-Device-ID + X-RFID-Version |
| Main Tank ATG | `/api/v1/site/readings` | POST | Bearer + X-Device-ID |

---

## Files Modified
1. `DummyHardSim/index.html` - Fixed HTML structure, added ATG auto handler, fixed field names, enhanced proxy function
2. `DummyHardSim/simulator.py` - Added method parameter support to proxy handler
3. `DummyHardSim/README.md` - Corrected token hashing documentation

All fixes are backward-compatible and non-breaking.
