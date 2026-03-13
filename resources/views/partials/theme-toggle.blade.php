<script>
    (() => {
        const allowedThemes = ['system', 'dark', 'light'];
        const allowedLocales = ['id', 'en'];
        const resolveCurrentLocale = () => document.documentElement.getAttribute('lang') || 'id';

        const syncThemeOptions = () => {
            const currentTheme = document.documentElement.dataset.themePreference || 'light';
            document.querySelectorAll('[data-theme-option]').forEach((link) => {
                const isActive = link.dataset.themeOption === currentTheme;
                link.dataset.uiActive = isActive ? 'true' : 'false';
                link.classList.toggle('is-active', isActive);
            });
        };

        const syncLocaleOptions = () => {
            const currentLocale = resolveCurrentLocale();
            document.querySelectorAll('[data-locale-option]').forEach((link) => {
                const isActive = link.dataset.localeOption === currentLocale;
                link.dataset.uiActive = isActive ? 'true' : 'false';
                link.classList.toggle('is-active', isActive);
            });
        };

        const syncPreferenceUi = () => {
            syncThemeOptions();
            syncLocaleOptions();
        };

        const rememberTheme = (theme) => {
            if (!allowedThemes.includes(theme)) {
                return;
            }

            try {
                localStorage.setItem('manake.theme', theme);
            } catch (error) {
                // Ignore localStorage errors.
            }

            if (window.ManakeTheme && typeof window.ManakeTheme.applyTheme === 'function') {
                window.ManakeTheme.applyTheme(theme);
            }

            syncThemeOptions();
        };

        const rememberLocale = (locale) => {
            if (!allowedLocales.includes(locale)) {
                return;
            }

            try {
                localStorage.setItem('manake.locale', locale);
            } catch (error) {
                // Ignore localStorage errors.
            }

            document.documentElement.setAttribute('lang', locale);
            syncLocaleOptions();
        };

        document.addEventListener('click', async (event) => {
            const themeLink = event.target.closest('a[data-theme-option]');
            if (themeLink) {
                event.preventDefault();

                const nextTheme = themeLink.dataset.themeOption || '';
                const resolvedTheme = window.ManakeTheme?.resolveTheme
                    ? window.ManakeTheme.resolveTheme(nextTheme)
                    : (nextTheme === 'dark' ? 'dark' : 'light');

                rememberTheme(nextTheme);

                try {
                    await fetch(`${themeLink.href}${themeLink.href.includes('?') ? '&' : '?'}resolved=${encodeURIComponent(resolvedTheme)}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });
                } catch (error) {
                    window.location.assign(themeLink.href);
                }

                return;
            }

            const localeLink = event.target.closest('a[data-locale-option]');
            if (localeLink) {
                event.preventDefault();
                rememberLocale(localeLink.dataset.localeOption || '');
                window.location.assign(localeLink.href);
            }
        });

        document.addEventListener('DOMContentLoaded', syncPreferenceUi);
        document.addEventListener('manake:theme-applied', syncThemeOptions);

        window.ManakePreferences = {
            rememberTheme,
            rememberLocale,
            syncPreferenceUi,
        };
    })();
</script>
