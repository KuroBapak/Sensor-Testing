# Frontend Implementation Spec: Fuel Monitoring Dashboard (React MVP)

## 1. Tech Stack & Environment
*   **Core:** React (via Vite for fast HMR)
*   **Backend:** Laravel (Inertia.js SSR / props integration)
*   **Database:** MySQL with seeded dummy records for immediate full display without delay
*   **Styling:** Tailwind CSS
    *   Industrial UI with high contrast and full Light/Dark mode support.
    *   `font-mono` for all numeric data tables to ensure alignment.
*   **Theme Management:**
    *   Interactive Light/Dark Mode toggle switch accessible in the UI / navigation bar.
*   **Charting:** Recharts (Optimized for frequent real-time re-renders).
*   **Data Delivery & Hydration:**
    *   Directly loaded from MySQL database through Inertia props on initial page load.
    *   No empty initial state; all tables and charts immediately show populated datasets.
*   **Routing Structure:** Separated into 3 dedicated pages/views (accessible via sidebar navigation).

---

## 2. Page 1: Main Tank Level Monitoring Display
*   **Route:** `/fuel-monitoring/main-tank`
*   **Backend Data (`main_tank_logs` table):**
    *   Fields: `id`, `waktu` (string/time), `total_liter` (integer), `liter_masuk` (integer), `liter_keluar` (integer)
*   **Layout:** Dedicated single-page vertical flow (Chart on top, Data Grid below).
*   **Chart Component (`<MainTankChart />`):**
    *   **Type:** Recharts `<BarChart>`
    *   **X-Axis:** Hour (`waktu`)
    *   **Y-Axis:** Volume in Liters
    *   **Data Series:** Double bar comparison:
        *   `<Bar dataKey="liter_masuk" fill="#10B981" />` (Green/Inflow)
        *   `<Bar dataKey="liter_keluar" fill="#F59E0B" />` (Orange/Outflow)
*   **Table Component (`<MainTankTable />`):**
    *   **Columns:** Waktu | Total Liter | Liter Masuk | Liter Keluar
    *   **Behavior:** Auto-scroll using `useRef` to pin the view to the newest row added at the bottom.

---

## 3. Page 2: Browser Tank & Fuel Tanker Level Monitoring Display
*   **Route:** `/fuel-monitoring/mobile-tanks`
*   **Backend Data (`mobile_tank_logs` table):**
    *   Fields: `id`, `tank_type` (`fuel_tanker` | `browser`), `rfid` (string), `waktu` (string/time), `liter` (integer)
*   **Layout:** Dedicated 50/50 Split view using Tailwind grid (`grid grid-cols-1 md:grid-cols-2 gap-4`).
    *   **Left Column:** Fuel Tanker context.
    *   **Right Column:** Browser Tank context.
*   **Chart Components (`<MobileTankChart />`):**
    *   **Type:** Recharts `<AreaChart>`
    *   **Purpose:** Simulate liquid fill/drain trends over time (filled area to mimic fluid level).
*   **Table Components (`<MobileTankTable />`):**
    *   **Placement:** Directly beneath each respective area chart.
    *   **Columns:** RFID (Sektor) | Waktu | Liter
    *   **Data Logic:** Array filtered by `tank_type` prop.
    *   **Data Format (RFID):** `HEX(Sector N)` (e.g., `7E978(Sector 1)`).

---

## 4. Page 3: Alarm Log Pencurian Bahan Bakar
*   **Route:** `/fuel-monitoring/alarms`
*   **Backend Data (`alarm_logs` table):**
    *   Fields: `id`, `rfid` (string), `waktu_kejadian` (string/datetime), `jumlah_liter` (integer)
*   **Layout:** Dedicated full-width, high-priority alert data grid.
*   **Styling (Zero Mistake Tolerance):** 
    *   Aggressive visual cues for active alerts (e.g., `bg-red-600 text-white font-bold`).
    *   Tailwind `animate-pulse` on container / table header when a new payload arrives.
*   **Table Component (`<AlarmTable />`):**
    *   **Columns:** RFID (Sektor) | Waktu Kejadian | Jumlah Liter
    *   **Data Logic:** Most recent incidents ordered by descending ID / event time to immediately display at the top row.
    *   **Data Format (RFID):** `HEX(Sector N)` (e.g., `7E978(Sector 1)`).

---

## 5. Global Feature: Light / Dark Mode Toggle
*   **Placement:** Global top bar or header on each monitoring page.
*   **Behavior:** Toggle between Dark (industrial slate/zinc) and Light modes seamlessly, ensuring high contrast readability for both charts and numeric tables.


