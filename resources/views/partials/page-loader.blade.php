<div id="manake-page-loader" class="manake-page-loader is-hidden" aria-hidden="true">
    <div class="manake-page-loader__inner">
        <div class="manake-loader-card" role="presentation">
            <x-brand.image
                light="MANAKE-FAV-M.png"
                dark="MANAKE-FAV-M.png"
                :alt="site_setting('brand.name', 'Manake')"
                img-class="manake-loader-mark"
            />
            <div class="manake-loader-text">
                <span class="manake-loader-brand">{{ site_setting('brand.name', 'Manake') }}</span>
                <span class="manake-loader-helper">Menyiapkan katalog alat...</span>
            </div>
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
            radial-gradient(circle at 50% 42%, rgba(212, 168, 67, 0.18), transparent 18rem),
            radial-gradient(circle at 50% 58%, rgba(255, 255, 255, 0.05), transparent 20rem),
            rgba(5, 5, 7, 0.96);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 1;
        visibility: visible;
        transition: opacity 0.35s ease, visibility 0.35s ease;
    }
    .manake-page-loader.is-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
    .manake-loader-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1.25rem;
        width: 100%;
        max-width: 20rem;
        padding: 2rem;
        border-radius: 1.5rem; /* rounded-3xl equivalent */
        border: 1px solid rgba(255, 255, 255, 0.10);
        background: rgba(17, 17, 19, 0.80);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        box-shadow: 0 24px 60px -15px rgba(0, 0, 0, 0.5);
        text-align: center;
    }
    .manake-loader-mark {
        width: 4.5rem;
        height: auto;
        aspect-ratio: 493/512;
        object-fit: contain;
        filter: drop-shadow(0 8px 24px rgba(212, 168, 67, 0.25));
        animation: manake-loader-breathe 1.8s ease-in-out infinite;
    }
    .manake-loader-text {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .manake-loader-brand {
        font-size: 1.125rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #E8E8EC;
    }
    .manake-loader-helper {
        font-size: 0.75rem;
        font-weight: 500;
        color: #A0A0A8;
    }
    .manake-loader-progress {
        position: relative;
        display: block;
        height: 3px;
        width: 12rem;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.10);
    }
    .manake-loader-progress::after {
        content: '';
        position: absolute;
        inset-block: 0;
        width: 45%;
        border-radius: inherit;
        background: linear-gradient(90deg, transparent, #D4A843, transparent);
        animation: manake-loader-track 1.2s ease-in-out infinite;
    }
    @keyframes manake-loader-breathe {
        0%, 100% {
            opacity: 0.86;
            transform: scale(0.96) translateY(0);
        }
        50% {
            opacity: 1;
            transform: scale(1) translateY(-2px);
        }
    }
    @keyframes manake-loader-track {
        from { transform: translateX(-120%); }
        to { transform: translateX(240%); }
    }
    @media (prefers-reduced-motion: reduce) {
        .manake-loader-mark,
        .manake-loader-progress::after {
            animation: none !important;
        }
        .manake-loader-mark {
            opacity: 1 !important;
            transform: none !important;
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
