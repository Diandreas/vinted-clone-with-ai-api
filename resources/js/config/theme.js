// Configuration du thème monochrome vert inspiré de RIKEAA
export const theme = {
    colors: {
        primary: {
            50: '#f0f9f4',
            100: '#dcf2e3',
            200: '#b8e4c8',
            300: '#8dd3a8',
            400: '#5bbd85',
            500: '#3da066', // Vert principal RIKEAA
            600: '#2f7f52',
            700: '#276543',
            800: '#225138',
            900: '#1e4330',
        },
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
        status: {
            success: '#3da066',
            warning: '#737373',
            error: '#525252',
            info: '#3da066',
        }
    },

    // Classes utilitaires pour le thème
    classes: {
        button: {
            primary: 'bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2.5 rounded-lg transition-all duration-150 shadow-soft hover:shadow-medium focus:ring-2 focus:ring-primary-500 focus:ring-offset-1',
            secondary: 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 font-medium px-4 py-2.5 rounded-lg transition-all duration-150 shadow-soft hover:shadow-medium focus:ring-2 focus:ring-gray-500 focus:ring-offset-1',
            outline: 'border border-primary-500 text-primary-600 hover:bg-primary-50 font-medium px-4 py-2.5 rounded-lg transition-all duration-150 focus:ring-2 focus:ring-primary-500 focus:ring-offset-1',
        },
        card: {
            base: 'bg-white rounded-lg shadow-soft hover:shadow-medium transition-all duration-150 border border-gray-100',
            elevated: 'bg-white rounded-lg shadow-medium hover:shadow-strong transition-all duration-150 border border-gray-100',
        },
        input: {
            base: 'w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-150 bg-white text-gray-900 placeholder-gray-500',
            error: 'w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-all duration-150 bg-gray-50 text-gray-900 placeholder-red-400',
        },
        badge: {
            primary: 'bg-primary-100 text-primary-800 px-2.5 py-1 rounded-md text-xs font-medium',
            neutral: 'bg-gray-100 text-gray-800 px-2.5 py-1 rounded-md text-xs font-medium',
            success: 'bg-primary-100 text-primary-800 px-2.5 py-1 rounded-md text-xs font-medium',
            warning: 'bg-gray-100 text-gray-800 px-2.5 py-1 rounded-md text-xs font-medium',
            error: 'bg-gray-100 text-gray-800 px-2.5 py-1 rounded-md text-xs font-medium',
        },
        text: {
            heading: 'font-semibold text-gray-900',
            subheading: 'font-medium text-gray-800',
            body: 'text-gray-600',
            caption: 'text-sm text-gray-500',
            link: 'text-primary-600 hover:text-primary-700 transition-colors duration-150',
        }
    },

    // Transitions et animations
    transitions: {
        fast: 'transition-all duration-150 ease-in-out',
        normal: 'transition-all duration-200 ease-in-out',
        slow: 'transition-all duration-300 ease-in-out',
    },

    // Ombres
    shadows: {
        soft: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
        medium: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
        strong: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
    },

    // Border radius
    borderRadius: {
        sm: '0.375rem',
        md: '0.5rem',
        lg: '0.75rem',
        xl: '1rem',
    }
};

// Fonction utilitaire pour obtenir une couleur du thème
export const getThemeColor = (colorPath) => {
    const keys = colorPath.split('.');
    let value = theme.colors;

    for (const key of keys) {
        if (value && typeof value === 'object' && key in value) {
            value = value[key];
        } else {
            return null;
        }
    }

    return value;
};

// Fonction utilitaire pour obtenir une classe du thème
export const getThemeClass = (classPath) => {
    const keys = classPath.split('.');
    let value = theme.classes;

    for (const key of keys) {
        if (value && typeof value === 'object' && key in value) {
            value = value[key];
        } else {
            return null;
        }
    }

    return value;
};

export default theme;
