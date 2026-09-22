export type Role = {
    id: number;
    name: string;
    is_system: boolean;
};

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    role?: Role | null;
    permissions: string[];
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
