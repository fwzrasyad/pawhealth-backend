import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: 'var(--color-primary)',
                'primary-dark': 'var(--color-primary-dark)',
                'dark-bg': 'var(--color-dark-bg)',
                'sidebar-bg': 'var(--color-sidebar-bg)',
                surface: 'var(--color-surface)',
                white: 'var(--color-white)',
                'card-border': 'var(--color-card-border)',
                'chip-bg': 'var(--color-chip-bg)',
                'muted-text': 'var(--color-muted-text)',
                'meta-text': 'var(--color-meta-text)',
                'nav-inactive': 'var(--color-nav-inactive)',
                'dark-text': 'var(--color-dark-text)',
                'pending-bg': 'var(--color-pending-bg)',
                'pending-text': 'var(--color-pending-text)',
                'confirmed-bg': 'var(--color-confirmed-bg)',
                'confirmed-text': 'var(--color-confirmed-text)',
                'completed-bg': 'var(--color-completed-bg)',
                'completed-text': 'var(--color-completed-text)',
            },
        },
    },

    plugins: [forms],
};
