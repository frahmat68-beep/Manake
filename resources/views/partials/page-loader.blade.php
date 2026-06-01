<div id="manake-page-loader" class="manake-page-loader is-hidden" aria-hidden="true" role="status" aria-live="polite">
    <div class="manake-simple-loader">
        <span class="manake-concentric-loader">
            <span class="manake-concentric-loader__ring manake-concentric-loader__ring--outer"></span>
            <span class="manake-concentric-loader__ring manake-concentric-loader__ring--middle"></span>
            <span class="manake-concentric-loader__ring manake-concentric-loader__ring--inner"></span>
            <span class="manake-concentric-loader__dot"></span>
        </span>
        <span class="manake-simple-text">Memuat...</span>
    </div>
</div>

<style>
    :root {
        --loader-size: 52px;
        --loader-accent: #2563EB;
        --loader-muted: rgba(37, 99, 235, 0.18);
        --loader-overlay-bg: rgba(255, 255, 255, 0.78);
        --loader-overlay-text: #111827;
        --loader-overlay-backdrop: blur(8px);
    }

    html[data-theme-resolved="dark"] {
        --loader-accent: #D4A843;
        --loader-muted: rgba(212, 168, 67, 0.18);
        --loader-overlay-bg: rgba(10, 10, 11, 0.78);
        --loader-overlay-text: #E8E8EC;
        --loader-overlay-backdrop: blur(10px);
    }

    .manake-page-loader {
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: var(--loader-overlay-bg);
        backdrop-filter: var(--loader-overlay-backdrop);
        -webkit-backdrop-filter: var(--loader-overlay-backdrop);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 1;
        visibility: visible;
        transition: opacity .25s cubic-bezier(0.4, 0, 0.2, 1), visibility .25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .manake-page-loader.is-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .manake-simple-loader {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        color: var(--loader-overlay-text);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    /* Concentric Loader CSS classes globally reusable */
    .manake-concentric-loader {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        width: var(--loader-size, 48px);
        height: var(--loader-size, 48px);
    }

    .manake-concentric-loader__ring {
        position: absolute;
        border-radius: 999px;
        border: 2px solid transparent;
    }

    .manake-concentric-loader__ring--outer {
        inset: 0;
        border-top-color: var(--loader-accent);
        border-bottom-color: var(--loader-accent);
        animation: manakeLoaderSpin 1.6s cubic-bezier(0.53, 0.21, 0.29, 0.87) infinite;
    }

    .manake-concentric-loader__ring--middle {
        inset: 6px;
        border-left-color: var(--loader-accent);
        border-right-color: var(--loader-muted);
        animation: manakeLoaderSpinReverse 1.2s cubic-bezier(0.53, 0.21, 0.29, 0.87) infinite;
        opacity: 0.85;
    }

    .manake-concentric-loader__ring--inner {
        inset: 12px;
        border-top-color: var(--loader-muted);
        border-bottom-color: var(--loader-accent);
        animation: manakeLoaderSpin 0.9s cubic-bezier(0.53, 0.21, 0.29, 0.87) infinite;
        opacity: 0.7;
    }

    .manake-concentric-loader__dot {
        width: 6px;
        height: 6px;
        background-color: var(--loader-accent);
        border-radius: 999px;
        animation: manakeLoaderPulse 1.2s ease-in-out infinite;
    }

    @keyframes manakeLoaderSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes manakeLoaderSpinReverse {
        0% { transform: rotate(360deg); }
        100% { transform: rotate(0deg); }
    }

    @keyframes manakeLoaderPulse {
        0%, 100% { transform: scale(0.7); opacity: 0.5; }
        50% { transform: scale(1.2); opacity: 1; }
    }

    @media (prefers-reduced-motion: reduce) {
        .manake-concentric-loader__ring--outer,
        .manake-concentric-loader__ring--middle,
        .manake-concentric-loader__ring--inner {
            animation-duration: 3.5s;
        }
        .manake-concentric-loader__dot {
            animation: none;
        }
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
