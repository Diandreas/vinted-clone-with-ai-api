/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        // Palette monochrome verte inspirée de Vinted
        primary: {
          50: '#f0f9f4',
          100: '#dcf2e3',
          200: '#b8e4c8',
          300: '#8dd3a8',
          400: '#5bbd85',
          500: '#3da066',  // Vert principal Vinted
          600: '#2f7f52',
          700: '#276543',
          800: '#225138',
          900: '#1e4330',
          950: '#0f2418',
        },
        // Gris neutres pour le contenu
        gray: {
          50: '#fafafa',
          100: '#f5f5f5',
          200: '#e5e5e5',
          300: '#d4d4d4',
          400: '#a3a3a3',
          500: '#737373',
          600: '#525252',
          700: '#404040',
          800: '#262626',
          900: '#171717',
        },
        // Couleurs d'accent minimalistes (monochrome)
        success: {
          50: '#f0f9f4',
          500: '#3da066',
          600: '#2f7f52',
        },
        warning: {
          50: '#f5f5f5',
          500: '#737373',
          600: '#525252',
        },
        error: {
          50: '#f5f5f5',
          500: '#525252',
          600: '#404040',
        },
        // Couleurs neutres pour les éléments UI
        neutral: {
          50: '#fafafa',
          100: '#f5f5f5',
          200: '#e5e5e5',
          300: '#d4d4d4',
          400: '#a3a3a3',
          500: '#737373',
          600: '#525252',
          700: '#404040',
          800: '#262626',
          900: '#171717',
        }
      },
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
      },
      borderRadius: {
        'xl': '0.75rem',
        '2xl': '1rem',
        '3xl': '1.5rem',
      },
      boxShadow: {
        'soft': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
        'medium': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
        'strong': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
      },
    },
  },
  plugins: [],
}

