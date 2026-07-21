export type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
};

export type AdminUserListItem = {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    roles: string[];
    school_name: string;
    country_name: string;
    province_name: string;
};

export type AdminRoleListItem = {
    id: number;
    name: string;
    users_count: number;
    permissions: string[];
    is_protected: boolean;
};

export type AdminLocationListItem = {
    id: number;
    country: string;
    province: string;
    school_name: string;
    created_at: string | null;
    updated_at: string | null;
};
