import { useSyncExternalStore } from 'react';

const MOBILE_BREAKPOINT = 768;

// Menyiapkan media query ukuran layar seluler hanya ketika berjalan di browser.
const mql =
    typeof window === 'undefined'
        ? undefined
        : window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT - 1}px)`);

// Mendaftarkan listener perubahan ukuran layar dan mengembalikan fungsi pelepasnya.
function mediaQueryListener(callback: (event: MediaQueryListEvent) => void) {
    if (!mql) {
        return () => {};
    }

    mql.addEventListener('change', callback);

    return () => {
        mql.removeEventListener('change', callback);
    };
}

// Mengembalikan status apakah lebar layar berada di bawah breakpoint seluler.
function isSmallerThanBreakpoint(): boolean {
    return mql?.matches ?? false;
}

// Memberikan nilai awal deterministik ketika hook dirender pada server.
function getServerSnapshot(): boolean {
    return false;
}

/** Mengamati media query dan melaporkan apakah tampilan termasuk perangkat seluler. */
export function useIsMobile(): boolean {
    return useSyncExternalStore(
        mediaQueryListener,
        isSmallerThanBreakpoint,
        getServerSnapshot,
    );
}
