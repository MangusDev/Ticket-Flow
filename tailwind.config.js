import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ink: {
                    950: '#172B4D',
                    900: '#1D3557',
                    700: '#42526E',
                    500: '#6B778C',
                    300: '#B7C3D0',
                    200: '#DFE1E6',
                    100: '#F4F5F7',
                    50: '#FAFBFC',
                },
                brand: {
                    700: '#0747A6',
                    600: '#0052CC',
                    50: '#DEEBFF',
                },
            },
            boxShadow: {
                card: '0 1px 3px rgba(9, 30, 66, 0.12), 0 1px 2px rgba(9, 30, 66, 0.08)',
                float: '0 8px 24px rgba(9, 30, 66, 0.14)',
            },
            borderRadius: {
                card: '10px',
                control: '6px',
            },
        },
    },

    plugins: [forms],
};
