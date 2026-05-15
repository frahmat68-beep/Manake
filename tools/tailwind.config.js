import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './resources/js/**/*.ts',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    bg: 'var(--bg)',
                    soft: 'var(--bg-soft)',
                    surface: 'var(--surface)',
                    surface2: 'var(--surface-2)',
                    surface3: 'var(--surface-3)',
                    border: 'var(--border)',
                    text: 'var(--text)',
                    muted: 'var(--text-muted)',
                    softText: 'var(--text-soft)',
                    primary: 'var(--primary)',
                    primaryStrong: 'var(--primary-strong)',
                    primarySoft: 'var(--primary-soft)',
                    success: 'var(--success)',
                    warning: 'var(--warning)',
                    danger: 'var(--danger)',
                },
            },
            borderRadius: {
                lg: 'var(--radius-lg)',
                xl: 'var(--radius-xl)',
                '2xl': 'var(--radius-2xl)',
            },
            boxShadow: {
                soft: 'var(--shadow-soft)',
                xs: 'var(--shadow-xs)',
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
