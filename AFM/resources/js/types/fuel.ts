export type Category = 'internal' | 'external';
export type ResetPeriod = 'day' | 'week' | 'month';

/** Settings every screen formats with (shared by the server). */
export type Site = {
    name: string | null;
    timezone: string;
    resetPeriod: ResetPeriod;
    resetUnit: string;
};

/** The name and logo shown in the sidebar and on the login page. */
export type Branding = {
    name: string;
    logoUrl: string | null;
};

export type ImportError = {
    sheet: string;
    row: number;
    column: string;
    message: string;
};

export type Reading = {
    read_at: string;
    total_pct: number;
    fuel_pct: number;
    water_pct: number;
    empty_pct: number;
    total_liters: number;
    fuel_liters: number;
    water_liters: number;
};

export type TankPanel = {
    id: number;
    name: string;
    capacity: number;
    latest: Reading | null;
    chart: Reading[];
    recent: Reading[];
};

export type UsageRow = {
    id: number;
    plate_number: string;
    vehicle_type: string;
    used: number;
    quota: number;
    remaining: number | null;
    reset_enabled: boolean;
    deleted: boolean;
};

export type UsageGroup = {
    vehicle_type: string;
    subtotal: number;
    rows: UsageRow[];
};

export type SeriesPoint = { start: string; label: string; liters: number };

export type UsageDetail = {
    card: {
        id: number;
        plate_number: string;
        category: Category;
        vehicle_type: string;
        deleted: boolean;
    };
    series: Record<ResetPeriod, SeriesPoint[]>;
    fuelings: { id: number; dispensed_at: string; liters: number }[];
};

export type VehicleTypeRow = {
    id: number;
    name: string;
    max_capacity_liters: number;
    quota_liters: number;
    cards_count: number;
};

export type QrCardRow = {
    id: number;
    token: string;
    plate_number: string;
    category: Category;
    category_label: string;
    vehicle_type_id: number;
    vehicle_type: string;
    validity: 'lifetime' | 'temporary';
    validity_label: string;
    valid_days: number | null;
    issued_at: string;
    expires_at: string | null;
    reset_enabled: boolean;
    quota: number;
    max_capacity: number;
    remaining: number;
    expired: boolean;
    depleted: boolean;
    low: boolean;
    next_reset: string | null;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

export type QrFilters = {
    validity?: string;
    state?: string;
    reset?: string;
    category?: string;
    vehicle_type?: string;
    search?: string;
    sort: string;
    dir: 'asc' | 'desc';
};

export type Counters = {
    active: number;
    lifetime: number;
    expired: number;
    depleted: number;
};

export type VehicleOption = { id: number; name: string; quota_liters: number };

export type GlobalSettings = {
    reset_period: ResetPeriod;
    reset_time: string;
    week_start_day: number;
    month_reset_day: number;
    timezone: string;
    low_quota_percent: number;
    site_name: string;
    logo_path: string;
};

export type TankSetting = { id: number; name: string; capacity_liters: number };

export type AccountRow = {
    id: number;
    name: string;
    email: string;
    created_at: string | null;
};
