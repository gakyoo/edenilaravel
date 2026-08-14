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
            // Edenire brand palette (from Godluck's brand colors, 2026-08-13)
            // #232126 dark charcoal | #EBEBEB light gray | #A8E46A lime | #A7DFF8 sky blue
            colors: {
                brand: {
                    dark: '#232126',
                    light: '#EBEBEB',
                    green: '#A8E46A',
                    blue: '#A7DFF8',
                },
                // map onto the emerald scale so existing emerald-* classes pick it up
                emerald: {
                    50: '#F6FBF0',
                    100: '#EDF8DF',
                    200: '#DBF1C0',
                    300: '#C3E899',
                    400: '#B4E67F',
                    500: '#A8E46A', // brand lime
                    600: '#8CC84F',
                    700: '#70A83C',
                    800: '#56852F',
                    900: '#406625',
                    950: '#2B4519',
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
