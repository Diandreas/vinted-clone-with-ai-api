/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#f0f9f4',
                    100: '#dcf2e3',
                    200: '#b8e4c8',
                    300: '#8dd3a8',
                    400: '#5bbd85',
                    500: '#3da066',
                    600: '#2f7f52',
                    700: '#276543',
                    800: '#225138',
                    900: '#1e4330',
                },
                gray: {
                    50: '#f9fafb',
                    100: '#f3f4f6',
                    200: '#e5e7eb',
                    300: '#d1d5db',
                    400: '#9ca3af',
                    500: '#6b7280',
                    600: '#4b5563',
                    700: '#374151',
                    800: '#1f2937',
                    900: '#111827',
                }
            },
            fontFamily: {
                'sans': ['Inter', 'Instrument Sans', 'system-ui', 'sans-serif'],
            },
            boxShadow: {
                'soft': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
                'medium': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                'strong': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
            },
            borderRadius: {
                'sm': '0.375rem',
                'md': '0.5rem',
                'lg': '0.75rem',
                'xl': '1rem',
            },
            spacing: {
                'xs': '0.75rem',
                'sm': '1rem',
                'md': '1.5rem',
                'lg': '2rem',
                'xl': '3rem',
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
