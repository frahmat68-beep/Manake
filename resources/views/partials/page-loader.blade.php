<div id="manake-page-loader" class="manake-page-loader is-hidden" aria-hidden="true">
    <div class="manake-simple-loader">
        <span class="manake-simple-spinner"></span>
        <span class="manake-simple-text">Memuat...</span>
    </div>
</div>

<style>
    .manake-page-loader {
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(5, 5, 7, 0.58);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 1;
        visibility: visible;
        transition: opacity .18s ease, visibility .18s ease;
    }

    .manake-page-loader.is-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .manake-simple-loader {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        border: 1px solid rgba(255,255,255,.12);
        background: rgba(17,17,19,.88);
        border-radius: 999px;
        padding: 10px 14px;
        color: #E8E8EC;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }

    .manake-simple-spinner {
        width: 16px;
        height: 16px;
        border-radius: 999px;
        border: 2px solid rgba(255,255,255,.25);
        border-top-color: #D4A843;
        animation: manake-spin .75s linear infinite;
    }

    @keyframes manake-spin {
        to { transform: rotate(360deg); }
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
        let safetyTimer = null;

        const hideLoader = () => {
            const element = loader();
            if (!element) {
                return;
            }

            if (navigationTimer) {
                window.clearTimeout(navigationTimer);
                navigationTimer = null;
            }
            if (safetyTimer) {
                window.clearTimeout(safetyTimer);
                safetyTimer = null;
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

            // Safety timeout: Auto hide after 8 seconds to prevent getting stuck
            if (safetyTimer) {
                window.clearTimeout(safetyTimer);
            }
            safetyTimer = window.setTimeout(hideLoader, 8000);
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
            const hrefLower = href.toLowerCase();

            // Skip loader for specific conditions
            if (
                href === ''
                || href.startsWith('#')
                || href.startsWith('javascript:')
                || href.startsWith('mailto:')
                || href.startsWith('tel:')
                || link.target === '_blank'
                || link.dataset.skipLoader === 'true'
                || link.getAttribute('data-skip-loader') === 'true'
                || link.hasAttribute('download')
                || href.includes('/invoice.pdf')
                || hrefLower.includes('.pdf')
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
            if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
                return;
            }

            if (form.target && form.target !== '_self') {
                return;
            }

            if (form.dataset.skipLoader === 'true' || form.hasAttribute('data-skip-loader')) {
                return;
            }

            showLoaderDeferred();
        });
    })();
</script>
