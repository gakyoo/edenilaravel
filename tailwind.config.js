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
            // Ronas IT "Real Estate Web Property Platform" palette
            // (light neutral base, deep navy text, blue primary, soft blue/green tints)
            colors: {
                emerald: {
                    50: '#F3F6FA',
                    100: '#E3EBFD',
                    200: '#CEDBE4',
                    300: '#98A7A7',
                    400: '#4F88A7',
                    500: '#0076FF', // primary blue
                    600: '#1A73E8',
                    700: '#00639B', // deep blue
                    800: '#39485C', // slate navy
                    900: '#131314', // near-black navy
                    950: '#0D0E0F', // dark navy
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
