/**
 * Dark Mode Manager
 * Handles theme initialization and switching between light/dark modes
 */

export type Theme = 'light' | 'dark' | 'system';

export class DarkModeManager {
    private static readonly STORAGE_KEY = 'appearance';
    private static readonly DARK_CLASS = 'dark';

    /**
     * Initialize theme based on stored preference or system preference
     */
    static initialize(): void {
        const appearance = this.getStoredTheme();
        this.applyTheme(appearance);
    }

    /**
     * Get the currently stored theme preference
     */
    static getStoredTheme(): Theme {
        const stored = localStorage.getItem(this.STORAGE_KEY);
        return (stored as Theme) || 'system';
    }

    /**
     * Apply the given theme
     */
    static applyTheme(theme: Theme): void {
        if (theme === 'system') {
            this.applySystemTheme();
        } else {
            this.setDarkMode(theme === 'dark');
        }
    }

    /**
     * Apply theme based on system preference
     */
    private static applySystemTheme(): void {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        this.setDarkMode(prefersDark);
    }

    /**
     * Set dark mode on/off
     */
    private static setDarkMode(enabled: boolean): void {
        if (enabled) {
            document.documentElement.classList.add(this.DARK_CLASS);
        } else {
            document.documentElement.classList.remove(this.DARK_CLASS);
        }
    }

    /**
     * Save theme preference and apply it
     */
    static setTheme(theme: Theme): void {
        localStorage.setItem(this.STORAGE_KEY, theme);
        this.applyTheme(theme);
    }

    /**
     * Listen for system theme changes (when using 'system' preference)
     */
    static watchSystemTheme(callback?: () => void): void {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        mediaQuery.addEventListener('change', (e) => {
            if (this.getStoredTheme() === 'system') {
                this.setDarkMode(e.matches);
                callback?.();
            }
        });
    }
}
