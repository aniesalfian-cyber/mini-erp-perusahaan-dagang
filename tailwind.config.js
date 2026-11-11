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
                // Remapped to Luxury Crystal White & Slate Glass
                cream: {
                    50: '#FFFFFF',
                    100: '#F8FAFC',
                    200: '#F1F5F9',
                    300: '#E2E8F0',
                    400: '#CBD5E1',
                    500: '#64748B',
                    600: '#475569',
                    700: '#334155',
                    800: '#1E293B',
                    900: '#0F172A',
                },
                glass: {
                    white: 'rgba(255, 255, 255, 0.82)',
                    pure: 'rgba(255, 255, 255, 0.95)',
                    subtle: 'rgba(255, 255, 255, 0.65)',
                    border: 'rgba(255, 255, 255, 0.85)',
                    'border-muted': 'rgba(226, 232, 240, 0.8)',
                    surface: 'rgba(248, 250, 252, 0.75)',
                },
                forest: {
                    DEFAULT: '#0F2E22',
                    50: '#F0FDF4',
                    100: '#DCFCE7',
                    500: '#10B981',
                    600: '#059669',
                    700: '#047857',
                    800: '#0F2E22',
                    900: '#061A13',
                },
                terracotta: {
                    DEFAULT: '#B45309',
                    50: '#FFFBEB',
                    100: '#FEF3C7',
                    500: '#F59E0B',
                    600: '#D97706',
                    700: '#B45309',
                    800: '#92400E',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            boxShadow: {
                // Luxury White Glassmorphism Specular Shadows
                'glass': '0 10px 30px -5px rgba(15, 23, 42, 0.05), 0 20px 40px -15px rgba(15, 23, 42, 0.04), inset 0 1px 1px 0 rgba(255, 255, 255, 0.95)',
                'glass-sm': '0 4px 16px -2px rgba(15, 23, 42, 0.03), inset 0 1px 1px 0 rgba(255, 255, 255, 0.95)',
                'glass-lg': '0 25px 50px -12px rgba(15, 23, 42, 0.08), 0 12px 24px -8px rgba(15, 23, 42, 0.04), inset 0 1px 1.5px 0 rgba(255, 255, 255, 1)',
                'glass-inset': 'inset 0 2px 4px rgba(15, 23, 42, 0.04), inset 0 -1px 1px rgba(255, 255, 255, 0.9)',
                'neo': '0 10px 30px -5px rgba(15, 23, 42, 0.05), 0 20px 40px -15px rgba(15, 23, 42, 0.04), inset 0 1px 1px 0 rgba(255, 255, 255, 0.95)',
                'neo-sm': '0 4px 16px -2px rgba(15, 23, 42, 0.03), inset 0 1px 1px 0 rgba(255, 255, 255, 0.95)',
                'neo-lg': '0 25px 50px -12px rgba(15, 23, 42, 0.08), inset 0 1px 1.5px 0 rgba(255, 255, 255, 1)',
                'neo-inset': 'inset 0 2px 4px rgba(15, 23, 42, 0.04), inset 0 -1px 1px rgba(255, 255, 255, 0.9)',
            },
        },
    },
    plugins: [],
};
