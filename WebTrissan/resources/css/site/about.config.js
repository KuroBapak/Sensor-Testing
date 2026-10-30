// Tailwind theme that was inlined in abtus.blade.php for the Play CDN.
export default {
    theme: {
        extend: {
            fontSize: {
                lg: ['1rem', { lineHeight: '1.6' }],
                xl: ['1.125rem', { lineHeight: '1.5' }],
                '2xl': ['1.25rem', { lineHeight: '1.4' }],
                '3xl': ['1.5rem', { lineHeight: '1.3' }],
                '4xl': ['1.75rem', { lineHeight: '1.25' }],
                '5xl': ['2.125rem', { lineHeight: '1.15' }],
                '6xl': ['2.5rem', { lineHeight: '1.1' }],
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
                heading: ['Montserrat', 'sans-serif'],
                mono: ['JetBrains Mono', 'monospace'],
            },
            colors: {
                brand: {
                    navy: '#0d2137',
                    navyDark: '#071321',
                    blue: '#1e40af',
                    accent: '#2563eb',
                    lightBlue: '#eff4ff',
                    surface: '#f8f9ff',
                    border: '#dbeafe',
                    grayMuted: '#64748b',
                },
            },
        },
    },
};
