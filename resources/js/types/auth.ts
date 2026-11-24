/** Merepresentasikan data akun pengguna yang dibagikan oleh server. */
export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

/** Membungkus informasi pengguna yang sedang terautentikasi. */
export type Auth = {
    user: User;
};

/** Menyediakan informasi passkey untuk ditampilkan di pengaturan keamanan. */
export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};

/** Menampung QR code dan URL yang diperlukan untuk konfigurasi 2FA. */
export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

/** Menampung kunci rahasia manual untuk konfigurasi authenticator. */
export type TwoFactorSecretKey = {
    secretKey: string;
};
