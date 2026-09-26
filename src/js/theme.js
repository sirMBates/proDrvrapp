const THEME_MODES = Object.freeze({
    AUTO: 'auto',
    LIGHT: 'light',
    DARK: 'dark'
});

const VALID_THEME_MODES = Object.values(THEME_MODES);
const THEME_MODE_STORAGE_KEY = 'prodriverThemeMode';
const LEGACY_OVERRIDE_KEY = 'userThemeOverride';
const LEGACY_DARK_MODE_KEY = 'isDarkMode';

export class ThemeManager {
    constructor({ themeButton = null, themeButtonText = null, modeIndicator = null, driverMenu = null, currentView = '', autoCheckInterval = 60000 } = {}) {
        this.themeButton = themeButton;
        this.themeButtonText = themeButtonText;
        this.modeIndicator = modeIndicator;
        this.driverMenu = driverMenu;
        this.currentView = currentView;
        this.autoCheckInterval = autoCheckInterval;

        this.mode = THEME_MODES.AUTO;
        this.activeTheme = THEME_MODES.LIGHT;
        this.autoIntervalId = null;
        this.initialized = false;

        this.handleThemeButtonClick = this.handleThemeButtonClick.bind(this);
        this.refreshAutoTheme = this.refreshAutoTheme.bind(this);
    }

    init() {
        if (this.initialized) {
            return;
        }

        this.mode = this.loadSavedMode();
        this.applyMode();
        this.themeButton?.addEventListener('click', this.handleThemeButtonClick);

        this.startAutoThemeCheck();
        this.initialized = true;
    }

    setMode(mode, persist = true) {
        if (!VALID_THEME_MODES.includes(mode)) {
            throw new TypeError(`Invalid theme mode: ${mode}`);
        }

        this.mode = mode;

        if (persist) {
            this.saveMode(mode);
        }

        this.applyMode();
        this.updateModeIndicator();
    }

    applyMode() {
        const theme = this.mode === THEME_MODES.AUTO ? this.resolveAutomaticTheme() : this.mode;
        this.applyTheme(theme);
    }

    applyTheme(theme) {
        if (theme !== THEME_MODES.LIGHT && theme !== THEME_MODES.DARK) {
            throw new TypeError(`Invalid resolved theme: ${theme}`);
        }

        const isDark = theme === THEME_MODES.DARK;
        const page = document.documentElement;
        const body = document.body;
        const header = document.querySelector('header');
        const textareas = document.querySelectorAll('textarea');

        page.dataset.bsTheme = theme;
        body?.classList.toggle('niteMode', isDark);
        header?.classList.toggle('nightMode', isDark);
        this.driverMenu?.classList.toggle('niteMode', isDark);

        textareas.forEach(textarea => {
            textarea.classList.toggle('bg-btd-textarea-clr', !isDark);
            textarea.classList.toggle('text-dark', !isDark);
        });

        this.updateHelpImage(isDark);
        this.updateThemeButton(isDark);

        this.activeTheme = theme;

        /*
         * Keep this temporarily for backward
         * compatibility with existing code and
         * the early theme initializer.
         */
        localStorage.setItem(LEGACY_DARK_MODE_KEY, String(isDark));
        window.dispatchEvent(
            new CustomEvent('theme-changed', {
                detail: {
                    mode: this.mode,
                    theme: this.activeTheme
                }
            })
        );
    }

    toggleTheme() {
        const nextTheme = this.activeTheme === THEME_MODES.DARK ? THEME_MODES.LIGHT : THEME_MODES.DARK;

        /*
         * A manual button click changes the mode
         * from Auto to an explicit preference.
         */
        this.setMode(nextTheme);
    }

    refreshAutoTheme() {
        if (this.mode !== THEME_MODES.AUTO) {
            return;
        }

        const resolvedTheme = this.resolveAutomaticTheme();
        if (resolvedTheme === this.activeTheme) {
            return;
        }

        this.applyTheme(resolvedTheme);
    }

    resolveAutomaticTheme() {
        const hour = new Date().getHours();
        return hour >= 20 || hour <= 6 ? THEME_MODES.DARK : THEME_MODES.LIGHT;
    }

    handleThemeButtonClick(event) {
        event.preventDefault();
        this.toggleTheme();
    }

    updateThemeButton(isDark) {
        if (!this.themeButton || !this.themeButtonText) {
            return;
        }

        const icon = this.themeButton.querySelector('i');
        this.themeButtonText.textContent = isDark ? 'Light theme' : 'Dark theme';
        if (!icon) {
            return;
        }

        icon.classList.toggle('fa-sun', isDark);
        icon.classList.toggle('fa-moon', !isDark);
        icon.classList.toggle('text-btd-white-floral', isDark);
        icon.classList.toggle('text-dark', !isDark);
    }

    updateModeIndicator() {
        if (!this.modeIndicator) {
            return;
        }

        const isAuto = this.mode === THEME_MODES.AUTO;

        this.modeIndicator.textContent = isAuto ? 'Auto' : 'Manual';
        this.modeIndicator.classList.toggle('theme-auto', isAuto);
        this.modeIndicator.classList.toggle('theme-manual', !isAuto);
    }

    updateHelpImage(isDark) {
        if (this.currentView !== '/help') {
            return;
        }

        const cardImage = document.querySelector('#card-img');

        if (!cardImage) {
            return;
        }

        cardImage.src = isDark ? '../../dist/images-videos/busnitepics/drvr-area-nite.jpg' : '../../dist/images-videos/drvrarea1.jpg';
    }

    startAutoThemeCheck() {
        this.stopAutoThemeCheck();

        this.autoIntervalId =window.setInterval(
            this.refreshAutoTheme,
            this.autoCheckInterval
        );
    }

    stopAutoThemeCheck() {
        if (this.autoIntervalId === null) {
            return;
        }

        window.clearInterval(this.autoIntervalId);
        this.autoIntervalId = null;
    }

    loadSavedMode() {
        const savedMode = localStorage.getItem(THEME_MODE_STORAGE_KEY);
        if (VALID_THEME_MODES.includes(savedMode)) {
            return savedMode;
        }

        /*
         * Migrate the current storage format.
         */
        const legacyOverride = localStorage.getItem(LEGACY_OVERRIDE_KEY);
        if (legacyOverride === THEME_MODES.LIGHT || legacyOverride === THEME_MODES.DARK) {
            localStorage.setItem(THEME_MODE_STORAGE_KEY, legacyOverride);
            return legacyOverride;
        }

        localStorage.setItem(THEME_MODE_STORAGE_KEY, THEME_MODES.AUTO);
        return THEME_MODES.AUTO;
    }

    saveMode(mode) {
        localStorage.setItem(THEME_MODE_STORAGE_KEY, mode);

        /*
         * Maintain compatibility during the
         * transition to the new preference key.
         */
        if (mode === THEME_MODES.AUTO) {
            localStorage.removeItem(LEGACY_OVERRIDE_KEY);
            return;
        }

        localStorage.setItem(LEGACY_OVERRIDE_KEY, mode);
    }

    getMode() {
        return this.mode;
    }

    getActiveTheme() {
        return this.activeTheme;
    }

    destroy() {
        this.themeButton?.removeEventListener('click', this.handleThemeButtonClick);
        this.stopAutoThemeCheck();
        this.initialized = false;
    }
};

export { THEME_MODES };