<div id="manake-page-loader" class="manake-page-loader is-hidden" aria-hidden="true">
    <div class="manake-page-loader__inner">
        <div class="manake-loader-card" role="presentation">
            <x-brand.image
                light="MANAKE-FAV-M.png"
                dark="MANAKE-FAV-M.png"
                :alt="site_setting('brand.name', 'Manake')"
                img-class="manake-loader-mark"
            />
            <span class="manake-loader-progress" aria-hidden="true"></span>
        </div>
    </div>
</div>

<style>
    .manake-page-loader {
        position: fixed;
        inset: 0;
        z-index: 99999;
        background-color: #020617; /* slate-950 */
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 1;
        visibility: visible;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .manake-page-loader.is-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
    .manake-loader-mark {
        height: 4rem;
        width: 4rem;
        animation: manake-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes manake-pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: .5; transform: scale(0.95); }
    }
</style>
<noscript>
    <style>
        #manake-page-loader {
            display: none !important;
        }
    </style>
</noscript>

<script>
    (() => {
        const root = document.documentElement;
        const loader = () => document.getElementById('manake-page-loader');
        let navigationTimer = null;

        const hideLoader = () => {
            const element = loader();
            if (!element) {
                return;
            }

            if (navigationTimer) {
                window.clearTimeout(navigationTimer);
                navigationTimer = null;
            }

            element.classList.add('is-hidden');
            root.classList.add('manake-loaded');
        };

        const showLoader = () => {
            const element = loader();
            if (!element) {
                return;
            }

            element.classList.remove('is-hidden');
            root.classList.remove('manake-loaded');
        };

        const showLoaderDeferred = () => {
            if (navigationTimer) {
                window.clearTimeout(navigationTimer);
            }

            navigationTimer = window.setTimeout(showLoader, 220);
        };

        root.classList.add('manake-loaded');
        hideLoader();

        window.addEventListener('pageshow', hideLoader);
        window.addEventListener('load', hideLoader, { once: true });

        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');
            if (!link || event.defaultPrevented) {
                return;
            }

            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            const href = link.getAttribute('href') || '';
            if (
                href === ''
                || href.startsWith('#')
                || href.startsWith('javascript:')
                || href.startsWith('mailto:')
                || href.startsWith('tel:')
                || link.target === '_blank'
                || link.dataset.skipLoader === 'true'
            ) {
                return;
            }

            const targetUrl = new URL(link.href, window.location.origin);
            if (targetUrl.origin !== window.location.origin) {
                return;
            }

            showLoaderDeferred();
        });

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (form.target && form.target !== '_self') {
                return;
            }

            showLoaderDeferred();
        });
    })();
</script>
