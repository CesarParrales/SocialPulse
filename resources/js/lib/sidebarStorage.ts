const STORAGE_KEY = 'sp.sidebar.collapsed';

export function readSidebarCollapsed(): boolean {
    try {
        return localStorage.getItem(STORAGE_KEY) === '1';
    } catch {
        return false;
    }
}

export function writeSidebarCollapsed(collapsed: boolean): void {
    try {
        localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
    } catch {
        // localStorage unavailable (private mode, etc.)
    }
}
