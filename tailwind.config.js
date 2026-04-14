/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './templates/**/*.html.twig',
        './assets/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                'navy': {
                    950: '#0d0f1a',
                    900: '#131628',
                    800: '#1a1e35',
                    700: '#1e2440',
                    600: '#252b4a',
                },
            },
            fontFamily: {
                'serif': ['Playfair Display', 'Georgia', 'ui-serif', 'serif'],
                'sans': ['Inter', 'system-ui', 'ui-sans-serif', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
