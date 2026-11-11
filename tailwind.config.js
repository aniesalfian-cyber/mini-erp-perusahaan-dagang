import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                cream: {
                    50: '#FBF9F6',
                    100: '#F5F2EC',
                    200: '#EAE6DF',
                    300: '#DDD8CF',
                    400: '#C7C0B3',
                    500: '#9E9789',
                    600: '#756F63',
                    700: '#4D483F',
                    800: '#2C2924',
                    900: '#1A1815',
                },
                forest: {
                    DEFAULT: '#1E3A2B',
                    50: '#F1F6F3',
                    100: '#DCE7E1',
                    500: '#2C553F',
                    600: '#1E3A2B',
                    700: '#15291E',
                },
                terracotta: {
                    DEFAULT: '#A85D3B',
                    50: '#FBF4F1',
                    100: '#F4E1D8',
                    500: '#C4724E',
                    600: '#A85D3B',
                    700: '#7B4228',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            boxShadow: {
                'neo': '5px 5px 12px rgba(185, 180, 171, 0.45), -5px -5px 12px rgba(255, 255, 255, 0.95)',
                'neo-sm': '3px 3px 7px rgba(185, 180, 171, 0.35), -3px -3px 7px rgba(255, 255, 255, 0.9)',
                'neo-lg': '8px 8px 18px rgba(185, 180, 171, 0.5), -8px -8px 18px rgba(255, 255, 255, 0.95)',
                'neo-inset': 'inset 2px 2px 5px rgba(185, 180, 171, 0.4), inset -2px -2px 5px rgba(255, 255, 255, 0.85)',
            },
        },
    },
    plugins: [],
};
