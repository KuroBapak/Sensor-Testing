// Tailwind theme that was inlined in home.blade.php for the Play CDN.
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
            colors: {
                brand: {
                    navy: '#0A192F',
                    navyDark: '#060D17',
                    navyLight: '#132845',
                    steel: '#25446B',
                    accent: '#0066CC',
                    grayBg: '#F8FAFC',
                    grayBorder: '#E2E8F0',
                    grayMuted: '#64748B',
                },
            },
            fontFamily: {
                heading: ['Montserrat', 'sans-serif'],
                body: ['Inter', 'sans-serif'],
            },
            boxShadow: {
                industrial: '0 20px 40px -15px rgba(10, 25, 47, 0.08), 0 0 1px 1px rgba(10, 25, 47, 0.05)',
                'card-hover': '0 25px 50px -12px rgba(10, 25, 47, 0.18)',
                heavy: '0 30px 60px -20px rgba(6, 13, 23, 0.35)',
            },
        },
    },
};
