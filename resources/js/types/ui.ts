import type { ReactNode } from 'react';
import type { BreadcrumbItem } from '@/types/navigation';

/** Menentukan properti bersama untuk layout aplikasi dan breadcrumb. */
export type AppLayoutProps = {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
};

/** Memilih bentuk navigasi utama pada layout aplikasi. */
export type AppVariant = 'header' | 'sidebar';

/** Menentukan bentuk pesan flash yang ditampilkan sebagai notifikasi. */
export type FlashToast = {
    type: 'success' | 'info' | 'warning' | 'error';
    message: string;
};

/** Menentukan properti judul dan konten pada layout autentikasi. */
export type AuthLayoutProps = {
    children?: ReactNode;
    name?: string;
    title?: string;
    description?: string;
};
