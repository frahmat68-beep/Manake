@php
    $initialThemePreference = $themePreference ?? request()->attributes->get('theme_preference', 'light');
    $initialThemeResolved = $themeResolved ?? request()->attributes->get(
        'theme_resolved',
        $initialThemePreference === 'dark' ? 'dark' : 'light'
    );
    $initialThemePreferenceExplicit = (bool) ($themePreferenceExplicit ?? request()->attributes->get('theme_preference_explicit', false));
@endphp
<script>
    window.tailwind = window.tailwind || {};
    window.tailwind.config = {
        darkMode: 'class',
    };

    (() => {
        const allowed = ['system', 'dark', 'light'];
        let preference = @json($initialThemePreference);
        const initialResolvedTheme = @json($initialThemeResolved);
        const hasExplicitPreference = @json($initialThemePreferenceExplicit);

        if (!allowed.includes(preference)) {
            preference = 'light';
        }

        if (!hasExplicitPreference) {
            try {
                const localTheme = localStorage.getItem('manake.theme');
                if (allowed.includes(localTheme)) {
                    preference = localTheme;
                }
            } catch (error) {
                // Ignore localStorage access errors.
            }

            try {
                const cookieTheme = decodeURIComponent(
                    (document.cookie.split('; ').find((row) => row.startsWith('theme=')) || '').split('=')[1] || ''
                );
                if (allowed.includes(cookieTheme)) {
                    preference = cookieTheme;
                }
            } catch (error) {
                // Ignore cookie parsing errors.
            }
        }

        const resolveTheme = (themePreference) => {
            const canMatchMedia = typeof window.matchMedia === 'function';
            if (themePreference !== 'system') {
                return themePreference;
            }

            if (!canMatchMedia) {
                return 'light';
            }

            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        };

        const syncThemedImages = (resolvedTheme) => {
            document.querySelectorAll('[data-manake-themed-image]').forEach((image) => {
                if (!(image instanceof HTMLImageElement)) {
                    return;
                }

                const lightSrc = image.dataset.lightSrc || image.getAttribute('src') || '';
                const darkSrc = image.dataset.darkSrc || lightSrc;
                const swapInDark = image.dataset.swapDark === 'true';
                const nextSrc = resolvedTheme === 'dark' && swapInDark ? darkSrc : lightSrc;

                if (nextSrc && image.getAttribute('src') !== nextSrc) {
                    image.setAttribute('src', nextSrc);
                }
            });
        };

        const applyTheme = (themePreference) => {
            const resolvedTheme = themePreference !== 'system' && themePreference === preference && hasExplicitPreference
                ? initialResolvedTheme
                : resolveTheme(themePreference);
            const root = document.documentElement;
            root.classList.toggle('dark', resolvedTheme === 'dark');
            root.dataset.theme = 'manake-brand';
            root.dataset.themePreference = themePreference;
            root.dataset.themeResolved = resolvedTheme;
            syncThemedImages(resolvedTheme);

            try {
                document.cookie = `theme_resolved=${encodeURIComponent(resolvedTheme)}; path=/; max-age=${60 * 60 * 24 * 30}; SameSite=Lax`;
            } catch (error) {
                // Ignore cookie write errors.
            }

            document.dispatchEvent(new CustomEvent('manake:theme-applied', {
                detail: {
                    preference: themePreference,
                    resolved: resolvedTheme,
                },
            }));
        };

        applyTheme(preference);

        document.addEventListener('DOMContentLoaded', () => {
            syncThemedImages(document.documentElement.dataset.themeResolved || resolveTheme(preference));
        });

        try {
            localStorage.setItem('manake.theme', preference);
        } catch (error) {
            // Ignore localStorage access errors.
        }

        window.ManakeTheme = {
            allowed,
            getPreference: () => document.documentElement.dataset.themePreference || preference,
            applyTheme,
            resolveTheme,
            syncThemedImages,
        };

        if (typeof window.matchMedia === 'function') {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            const onSystemThemeChange = () => {
                const currentPreference = document.documentElement.dataset.themePreference || preference;
                if (currentPreference === 'system') {
                    applyTheme('system');
                }
            };

            if (typeof mediaQuery.addEventListener === 'function') {
                mediaQuery.addEventListener('change', onSystemThemeChange);
            } else if (typeof mediaQuery.addListener === 'function') {
                mediaQuery.addListener(onSystemThemeChange);
            }
        }
    })();
</script>
<style>
{!! file_get_contents(resource_path('css/theme.css')) !!}
</style>
