@php
    $loaderCaption = app()->getLocale() === 'id'
        ? 'Menyiapkan workspace Manake'
        : 'Preparing the Manake workspace';
@endphp

<div id="manake-page-loader" class="manake-page-loader" aria-hidden="true">
    <div class="manake-page-loader__inner">
        <div class="manake-loader-core">
            <span class="manake-loader-orb"></span>
            <span class="manake-loader-label">{{ site_setting('brand.name', 'Manake') }}</span>
        </div>
        <div class="manake-loader-bar" role="presentation">
            <span></span>
        </div>
        <p class="manake-loader-caption">{{ $loaderCaption }}</p>
    </div>
</div>

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

        const hideLoader = () => {
            const element = loader();
            if (!element) {
                return;
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

        if (document.readyState === 'complete') {
            requestAnimationFrame(hideLoader);
        } else {
            window.addEventListener('load', () => requestAnimationFrame(hideLoader), { once: true });
        }

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

            showLoader();
        });

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (form.target && form.target !== '_self') {
                return;
            }

            showLoader();
        });
    })();
</script>
