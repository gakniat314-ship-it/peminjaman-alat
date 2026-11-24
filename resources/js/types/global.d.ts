import type { Auth } from '@/types/auth';

// Menambahkan atribut passwordrules pada elemen input React.
declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

// Mendefinisikan bentuk properti bersama yang diterima oleh halaman Inertia.
declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
