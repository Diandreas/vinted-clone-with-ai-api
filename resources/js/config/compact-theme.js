// Configuration pour un design compact mobile-first
export const COMPACT_THEME = {
  // Espacements compacts
  spacing: {
    // Espacement très réduit pour mobile
    xs: '0.25rem',  // 4px
    sm: '0.5rem',   // 8px
    md: '0.75rem',  // 12px
    lg: '1rem',     // 16px
    xl: '1.25rem',  // 20px
    
    // Marges et paddings pour mobile
    mobile: {
      container: 'px-3 py-2',
      card: 'p-3',
      section: 'py-4',
      gap: 'gap-3',
      header: 'py-3',
    },
    
    // Marges et paddings pour desktop (plus d'espace)
    desktop: {
      container: 'sm:px-6 sm:py-4',
      card: 'sm:p-6',
      section: 'sm:py-8',
      gap: 'sm:gap-6',
      header: 'sm:py-6',
    }
  },

  // Typographie compacte
  typography: {
    // Titres réduits
    title: {
      h1: 'text-xl sm:text-2xl lg:text-3xl',
      h2: 'text-lg sm:text-xl lg:text-2xl',
      h3: 'text-base sm:text-lg lg:text-xl',
      h4: 'text-sm sm:text-base lg:text-lg',
    },
    
    // Corps de texte optimisé
    body: {
      base: 'text-sm sm:text-base',
      small: 'text-xs sm:text-sm',
      large: 'text-base sm:text-lg',
    },
    
    // Boutons compacts
    button: {
      small: 'text-xs px-2 py-1 sm:text-sm sm:px-3 sm:py-2',
      medium: 'text-sm px-3 py-2 sm:text-base sm:px-4 sm:py-2',
      large: 'text-sm px-4 py-2 sm:text-base sm:px-6 sm:py-3',
    }
  },

  // Composants compacts
  components: {
    // Cartes très compactes
    card: {
      base: 'rounded-lg border bg-white shadow-sm',
      padding: 'p-3 sm:p-4 lg:p-6',
      spacing: 'space-y-2 sm:space-y-3',
    },
    
    // Navigation mobile compacte
    nav: {
      height: 'h-12 sm:h-14',
      padding: 'px-3 sm:px-4',
      gap: 'space-x-2 sm:space-x-4',
    },
    
    // Formulaires compacts
    form: {
      spacing: 'space-y-3 sm:space-y-4',
      input: 'px-3 py-2 text-sm sm:text-base',
      label: 'text-sm font-medium mb-1',
    },
    
    // Listes compactes
    list: {
      spacing: 'space-y-2 sm:space-y-3',
      item: 'py-2 sm:py-3',
      gap: 'gap-2 sm:gap-3',
    }
  },

  // Grilles adaptatives compactes
  grid: {
    // Produits
    products: 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-4',
    // Conversations
    conversations: 'space-y-1 sm:space-y-2',
    // Cartes de statistiques
    stats: 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4',
  },

  // Hauteurs compactes
  heights: {
    // Navigation mobile
    mobileTab: 'h-16 pb-safe',
    // Headers compacts
    header: 'h-12 sm:h-16',
    // Cartes produits compactes
    productCard: 'h-48 sm:h-56 lg:h-64',
    // Avatars compacts
    avatar: {
      small: 'w-6 h-6 sm:w-8 sm:h-8',
      medium: 'w-8 h-8 sm:w-10 sm:h-10',
      large: 'w-12 h-12 sm:w-16 sm:h-16',
    }
  }
}

// Classes utilitaires pour l'application compacte
export const COMPACT_CLASSES = {
  // Container principal compact
  container: 'min-h-screen bg-gray-50 pb-16 sm:pb-0',
  
  // Page wrapper compact
  page: 'max-w-7xl mx-auto px-3 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8',
  
  // Section wrapper compact
  section: 'bg-white rounded-lg border shadow-sm p-3 sm:p-6',
  
  // Header compact
  header: 'flex items-center justify-between py-3 sm:py-4 mb-4 sm:mb-6',
  
  // Liste compacte
  list: 'divide-y divide-gray-100',
  listItem: 'py-2 sm:py-3 px-3 sm:px-4 hover:bg-gray-50 transition-colors',
  
  // Boutons compacts
  btnPrimary: 'bg-primary-600 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors',
  btnSecondary: 'bg-gray-100 text-gray-900 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors',
  
  // Navigation compacte
  nav: 'sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-200 h-12 sm:h-16',
  
  // Cartes compactes
  card: 'bg-white rounded-lg border shadow-sm p-3 sm:p-4 hover:shadow-md transition-shadow',
  
  // Grilles responsives compactes
  gridProducts: 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-4',
  gridStats: 'grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4',
  
  // Messages et chat compact
  messageList: 'space-y-1 sm:space-y-2 max-h-96 overflow-y-auto',
  messageItem: 'p-2 sm:p-3 rounded-lg max-w-xs lg:max-w-md',
  
  // Formulaires compacts
  formGroup: 'space-y-3 sm:space-y-4',
  input: 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
  label: 'block text-sm font-medium text-gray-700 mb-1',
}

// Helpers pour les breakpoints
export const BREAKPOINTS = {
  mobile: '(max-width: 639px)',
  tablet: '(min-width: 640px) and (max-width: 1023px)',
  desktop: '(min-width: 1024px)',
}

export default COMPACT_THEME
