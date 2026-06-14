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
                // Paleta CoinScope "Dark Exchange"
                night: '#0B0E14',       // fondo principal
                surface: '#141925',     // tarjetas / paneles
                'surface-2': '#1C2230', // elementos elevados
                edge: '#232A39',        // bordes sutiles
                neon: {
                    DEFAULT: '#00E599',  // verde neón (acento principal)
                    dark: '#00B377',
                    soft: 'rgba(0,229,153,0.12)',
                },
                ice: '#22D3EE',          // cian (acento secundario)
                loss: '#FF5C6C',         // rojo (ventas / bajadas)
            },
            boxShadow: {
                neon: '0 0 0 1px rgba(0,229,153,0.35), 0 10px 40px -12px rgba(0,229,153,0.45)',
                card: '0 10px 30px -15px rgba(0,0,0,0.6)',
            },
            backgroundImage: {
                'grid-faint':
                    'linear-gradient(rgba(255,255,255,0.035) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.035) 1px, transparent 1px)',
            },
        },
    },

    plugins: [forms],
};
