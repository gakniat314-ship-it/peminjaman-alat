import type { InertiaLinkProps } from '@inertiajs/react';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/** Menggabungkan nama kelas CSS sambil menghapus konflik kelas Tailwind. */
export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/** Mengubah tujuan tautan Inertia menjadi URL string. */
export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}
