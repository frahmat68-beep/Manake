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
        background:
            radial-gradient(circle at 50% 42%, rgba(212, 168, 67, 0.16), transparent 22rem),
            rgba(2, 6, 23, 0.96);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 1;
        visibility: visible;
        transition: opacity 0.42s ease, visibility 0.42s ease;
    }
    .manake-page-loader.is-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
    .manake-loader-card {
        display: grid;
        place-items: center;
        gap: 1rem;
        min-width: 8rem;
    }
    .manake-loader-mark {
        width: 4rem;
        height: auto;
        aspect-ratio: 493/512;
        object-fit: contain;
        filter: drop-shadow(0 16px 30px rgba(212, 168, 67, 0.22));
        animation: manake-loader-float 1.7s cubic-bezier(0.45, 0, 0.2, 1) infinite;
    }
    .manake-loader-progress {
        position: relative;
        display: block;
        height: 0.18rem;
        width: 7.5rem;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(232, 232, 236, 0.12);
    }
    .manake-loader-progress::after {
        content: '';
        position: absolute;
        inset-block: 0;
        width: 48%;
        border-radius: inherit;
        background: linear-gradient(90deg, transparent, #D4A843, transparent);
        animation: manake-loader-track 1.15s ease-in-out infinite;
    }
    @keyframes manake-loader-float {
        0%, 100% { opacity: 0.98; transform: translateY(0) scale(1); }
        50% { opacity: 0.84; transform: translateY(-0.32rem) scale(0.985); }
    }
    @keyframes manake-loader-track {
        from { transform: translateX(-120%); }
        to { transform: translateX(240%); }
    }
    @media (prefers-reduced-motion: reduce) {
        .manake-loader-mark,
        .manake-loader-progress::after {
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
