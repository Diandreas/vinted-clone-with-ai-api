/**
 * Configuration de branding pour RIKEAA
 * Couleurs, polices et styles cohérents pour toute l'application
 */

export const RIKEAA_BRAND = {
  // Informations de base
  name: 'RIKEAA',
  slogan: 'Donnez une seconde vie à vos objets',
  description: 'Plateforme de vente et d\'achat d\'articles de seconde main',
  currency: 'XAF',

  // Couleurs principales
  colors: {
    primary: {
      50: '#f0fdf4',
      100: '#dcfce7',
      200: '#bbf7d0',
      300: '#86efac',
      400: '#4ade80',
      500: '#22c55e',
      600: '#16a34a',
      700: '#15803d',
      800: '#166534',
      900: '#14532d',
    },
    // Couleurs de support
    gray: {
      50: '#f8fafc',
      100: '#f1f5f9',
      500: '#64748b',
      600: '#475569',
      700: '#334155',
      800: '#1e293b',
      900: '#0f172a',
    }
  },

  // Typographie
  fonts: {
    primary: ['Inter', 'system-ui', 'sans-serif'],
    secondary: ['Instrument Sans', 'system-ui', 'sans-serif'],
  },

  // Valeurs de design
  design: {
    borderRadius: {
      sm: '0.5rem',
      md: '0.75rem',
      lg: '1rem',
      xl: '1.5rem',
    },
    shadows: {
      soft: '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
      medium: '0 4px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
      strong: '0 10px 40px -10px rgba(0, 0, 0, 0.15)',
    },
    spacing: {
      mobile: {
        xs: '0.75rem',
        sm: '1rem',
        md: '1.5rem',
        lg: '2rem',
      },
      desktop: {
        xs: '1rem',
        sm: '1.5rem',
        md: '2rem',
        lg: '3rem',
      }
    }
  },

  // Breakpoints responsive
  breakpoints: {
    mobile: '0px',
    tablet: '768px',
    desktop: '1024px',
    wide: '1280px',
  }
}

// Classes utilitaires pour les composants
export const RIKEAA_CLASSES = {
  // Boutons
  button: {
    primary: 'bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-xl transition-all duration-200 shadow-soft hover:shadow-medium',
    secondary: 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 font-medium px-4 py-2 rounded-xl transition-all duration-200 shadow-soft hover:shadow-medium',
    outline: 'border-2 border-primary-500 text-primary-600 hover:bg-primary-50 font-medium px-4 py-2 rounded-xl transition-all duration-200',
  },

  // Cartes
  card: {
    default: 'bg-white rounded-xl shadow-soft hover:shadow-medium transition-all duration-200 border border-gray-100',
    elevated: 'bg-white rounded-xl shadow-medium hover:shadow-strong transition-all duration-200 border border-gray-100',
  },

  // Formulaires
  input: {
    default: 'w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 bg-white',
    error: 'w-full px-4 py-3 border border-red-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200 bg-red-50',
  },

  // Texte
  text: {
    heading: 'font-bold text-gray-900',
    subheading: 'font-semibold text-gray-800',
    body: 'text-gray-600',
    caption: 'text-sm text-gray-500',
    link: 'text-primary-600 hover:text-primary-700 transition-colors duration-200',
  }
}

// Fonction helper pour générer des classes responsives
export const responsive = (mobile, tablet = null, desktop = null) => {
  let classes = mobile
  if (tablet) classes += ` md:${tablet}`
  if (desktop) classes += ` lg:${desktop}`
  return classes
}
