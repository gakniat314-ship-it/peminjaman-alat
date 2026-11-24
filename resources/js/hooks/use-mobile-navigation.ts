import { useCallback } from 'react';

export type CleanupFn = () => void;

/** Menyediakan aksi pembersihan gaya navigasi seluler setelah interaksi. */
export function useMobileNavigation(): CleanupFn {
    return useCallback(() => {
        // Remove pointer-events style from body...
        document.body.style.removeProperty('pointer-events');
    }, []);
}
