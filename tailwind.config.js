import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                sp: {
                    primary: 'rgb(var(--sp-primary) / <alpha-value>)',
                    'primary-hover': 'rgb(var(--sp-primary-hover) / <alpha-value>)',
                    accent: 'rgb(var(--sp-accent) / <alpha-value>)',
                    sidebar: 'rgb(var(--sp-sidebar) / <alpha-value>)',
                    'sidebar-hover': 'rgb(var(--sp-sidebar-hover) / <alpha-value>)',
                    surface: 'rgb(var(--sp-surface) / <alpha-value>)',
                    border: 'rgb(var(--sp-border) / <alpha-value>)',
                    ink: '#0f172a',
                    muted: '#64748b',
                },
            },
            boxShadow: {
                sp: '0 1px 3px 0 rgb(15 23 42 / 0.06), 0 1px 2px -1px rgb(15 23 42 / 0.06)',
                'sp-lg':
                    '0 10px 15px -3px rgb(109 40 217 / 0.08), 0 4px 6px -4px rgb(109 40 217 / 0.06)',
            },
        },
    },

    plugins: [forms],
};
