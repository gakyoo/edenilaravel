import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            // WhatsApp-style green palette (mapped onto the emerald scale
            // so all existing emerald-* classes pick it up automatically)
            colors: {
                emerald: {
                    50: '#E8F9EF',
                    100: '#D0F1DE',
                    200: '#A6E8C3',
                    300: '#6FDC9E',
                    400: '#3DDC84',
                    500: '#25D366', // WhatsApp green
                    600: '#1EBE5B',
                    700: '#128C7E', // WhatsApp teal
                    800: '#0E7569',
                    900: '#075E54', // WhatsApp dark teal
                    950: '#053F38',
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
