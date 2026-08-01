/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./app/Livewire/**/*.php",
    ],
    theme: {
        extend: {
            colors: {
                // 'pantau-navy': '#0B2A4A',
                // 'pantau-teal': '#0F7B8A',
                // 'pantau-gold': '#E2A63B',
                'pantau-navy': '#005B96',
                'pantau-teal': '#FF6600',
                'pantau-gold': '#333333',
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
                display: ['Space Grotesk', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
    ],
}
