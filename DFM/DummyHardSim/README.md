# DummyHardSim - Hardware Device Simulator

Complete simulator for testing all DFM backend endpoints without physical hardware.

## ✅ Current Status (October 9, 2026)

All 4 device types fully functional and aligned with backend API:
- ✅ Mobile Unit (FMC225) - AVL ingestion working
- ✅ Main Tank ATG (RUT956) - Site readings working  
- ✅ Fill Line (RUT956) - Transactions working
- ✅ Dispense Line (RUT956) - Transactions working
- ✅ RFID Sync - All devices syncing properly

## Quick Start

```bash
# 1. Seed the database with simulator hardware
php artisan db:seed --class=HardwareSimulatorSeeder

# 2. Start the simulator
cd DummyHardSim
python3 simulator.py

# 3. Open browser
# Navigate to http://localhost:3030
```

## Features

✅ **Interactive UI** - Web-based control panel for all devices  
✅ **Live Map** - Drag marker to simulate GPS movement  
✅ **Auto Mode** - Automated data generation for continuous testing  
✅ **Real-time Logs** - See all API requests/responses  
✅ **Data Overview Table** - Shows what each device transmits  
✅ **CORS Proxy** - Built-in proxy to avoid browser CORS issues

## Device Configuration

All devices are pre-configured and match the seeded database:

| Device Type | Device ID | Tank | Auth Token |
|-------------|-----------|------|------------|
| Mobile Unit | SIM_MOBILE_001 | Fuel Tanker FT-001 (ID: 2) | sfdgsdfefad |
| Main Tank ATG | SIM_ATG_001 | Main Tank 1 (ID: 1) | e5g34tg4tg4tg5rg4t |
| Fill Line | SIM_FILL_001 | N/A | g4h45g6hryh46t |
| Dispense Line | SIM_DISPENSE_001 | N/A | sdff43gfe43 |

### Tanks in Database

- **Tank 1:** Main Tank 1 (main_tank, 30,000 L)
- **Tank 2:** Fuel Tanker FT-001 (fuel_tanker, 10,000 L)
- **Tank 3:** Fuel Tanker FT-002 (fuel_tanker, 10,000 L)
- **Tank 4:** Browser Tank BT-001 (browser_tank, 2,000 L)
- **Tank 5:** Browser Tank BT-002 (browser_tank, 2,000 L)
- **Tank 6:** Browser Tank BT-003 (browser_tank, 2,000 L)

### RFID Tags

- **RFID_FT_001** → Tank 2 (Sector 1)
- **RFID_FT_002** → Tank 3 (Sector 2)
- **RFID_BT_001** → Tank 4 (Mining Area A)
- **RFID_BT_002** → Tank 5 (Mining Area B)
- **RFID_BT_003** → Tank 6 (Mining Area C)

## API Endpoints

### Mobile Unit: POST /api/internal/ingest/avl
**Data:** IMEI, GPS (lat/lng), satellites, fuel level, timestamp

### Main Tank ATG: POST /api/v1/site/readings
**Auth:** Bearer token + X-Device-ID header  
**Data:** Device ID, tank ID, level (liters), timestamp (batch: 2 readings)

### Fill/Dispense Line: POST /api/v1/transactions
**Auth:** Bearer token + X-Device-ID header  
**Data:** Transaction UUID, device ID, RFID tag, tank IDs, liters, start/end timestamps

### All RUT956: GET /api/v1/sync/rfid
**Auth:** Bearer token + X-Device-ID + X-RFID-Version headers  
**Returns:** Updated RFID list or 304 if unchanged

## How to Use

### Manual Testing
1. **Mobile Unit:** Drag map marker, adjust level/satellites, click "Send AVL Record"
2. **Main Tank ATG:** Adjust level, click "Send ATG Batch"
3. **Fill Line:** Set liters, click "Send Fill Transaction"
4. **Dispense Line:** Set liters, click "Send Dispense Transaction"
5. **RFID Sync:** Click "Sync RFID" on any RUT956 device

### Automated Testing
- Click "Start Auto" on Mobile Unit for continuous GPS + level data every 30s
- Click "Start Auto" on Main Tank ATG for continuous level readings every 30s

## Testing Full Demo

**Scenario 1: Mobile Tracking**
1. Start Mobile Unit auto mode
2. Open DFM app → Fleet Map
3. Watch tank move in real-time

**Scenario 2: Fill Transaction**
1. Click "Send Fill Transaction"
2. Open DFM app → Transactions
3. Verify transaction with RFID_FT_001

**Scenario 3: Dispense Transaction**
1. Click "Send Dispense Transaction"
2. Open DFM app → Transactions
3. Verify browser tank refuel

**Scenario 4: RFID Sync**
1. Add new RFID in web app
2. Click "Sync RFID" in simulator
3. Check logs show updated version

## Troubleshooting

**401 Invalid token?** Run: `php artisan db:seed --class=HardwareSimulatorSeeder`  
**422 Validation errors?** Check log output for specific field errors  
**Connection refused?** Ensure Laravel is running: `php artisan serve`  
**Port 3030 in use?** Kill existing: `pkill -f simulator.py`

## To Remove

```bash
rm -rf DummyHardSim
```

No system dependencies affected. To remove seeded data: `php artisan migrate:fresh --seed`
