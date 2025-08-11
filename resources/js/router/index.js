import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Import views
const Dashboard = () => import('@/views/Dashboard.vue')
const Home = () => import('@/views/Home.vue')
const Login = () => import('@/views/auth/Login.vue')
const Register = () => import('@/views/auth/Register.vue')
const ForgotPassword = () => import('@/views/auth/ForgotPassword.vue')
const Profile = () => import('@/views/Profile.vue')
const Products = () => import('@/views/Products.vue')
const PublicProducts = () => import('@/views/PublicProducts.vue')
const ProductDetail = () => import('@/views/ProductDetail.vue')
const CreateProduct = () => import('@/views/CreateProduct.vue')
const Lives = () => import('@/views/Lives.vue')
const LiveDetail = () => import('@/views/LiveDetail.vue')
const CreateLive = () => import('@/views/CreateLive.vue')
const Stories = () => import('@/views/Stories.vue')
const Messages = () => import('@/views/Messages.vue')
const Orders = () => import('@/views/Orders.vue')
const Notifications = () => import('@/views/Notifications.vue')
const Search = () => import('@/views/Search.vue')
const UserProfile = () => import('@/views/UserProfile.vue')
const CategoryManagement = () => import('@/views/admin/CategoryManagement.vue')

const routes = [
  {
    path: '/admin/dashboard',
    name: 'admin-dashboard',
    component: () => import('@/views/admin/AdminDashboard.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/users',
    name: 'admin-users',
    component: () => import('@/views/admin/UserManagement.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, permission: 'users:manage' }
  },
  {
    path: '/admin/products',
    name: 'admin-products',
    component: () => import('@/views/admin/ProductManagement.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, permission: 'products:moderate' }
  },
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: { requiresAuth: false }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { requiresAuth: false, guestOnly: true }
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { requiresAuth: false, guestOnly: true }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPassword,
    meta: { requiresAuth: false, guestOnly: true }
  },
  {
    path: '/profile',
    name: 'profile',
    component: Profile,
    meta: { requiresAuth: true }
  },
  {
    path: '/users/:id',
    name: 'user-profile',
    component: UserProfile,
    props: true,
    meta: { requiresAuth: false }
  },
  {
    path: '/products',
    name: 'products',
    component: PublicProducts,
    meta: { requiresAuth: false }
  },
  {
    path: '/my-products',
    name: 'my-products',
    component: Products,
    meta: { requiresAuth: true }
  },
  {
    path: '/products/:id',
    name: 'product-detail',
    component: ProductDetail,
    props: true,
    meta: { requiresAuth: false }
  },
  {
    path: '/products/create',
    name: 'create-product',
    component: CreateProduct,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/products',
    name: 'product-management',
    component: () => import('@/views/admin/ProductManagement.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/users',
    name: 'user-management',
    component: () => import('@/views/admin/UserManagement.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/categories',
    name: 'category-management',
    component: CategoryManagement,
    meta: { requiresAuth: true }
  },
  {
    path: '/lives',
    name: 'lives',
    component: Lives,
    meta: { requiresAuth: false }
  },
  {
    path: '/lives/:id',
    name: 'live-detail',
    component: LiveDetail,
    props: true,
    meta: { requiresAuth: false }
  },
  {
    path: '/lives/create',
    name: 'create-live',
    component: CreateLive,
    meta: { requiresAuth: true }
  },
  {
    path: '/stories',
    name: 'stories',
    component: Stories,
    meta: { requiresAuth: true }
  },
  {
    path: '/messages',
    name: 'messages',
    component: Messages,
    meta: { requiresAuth: true }
  },
  {
    path: '/orders',
    name: 'orders',
    component: Orders,
    meta: { requiresAuth: true }
  },
  {
    path: '/notifications',
    name: 'notifications',
    component: Notifications,
    meta: { requiresAuth: true }
  },
  {
    path: '/search',
    name: 'search',
    component: Search,
    meta: { requiresAuth: false }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFound.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Initialize auth state if not already done
  if (!authStore.initialized) {
    await authStore.initialize()
  }
  
  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  // Admin / permissions guard
  if (to.meta.requiresAdmin) {
    const canView = authStore.isAdmin || authStore.hasPermission('dashboard:view')
    if (!canView) {
      next({ name: 'dashboard' })
      return
    }
    
    // Check specific permission if required
    if (to.meta.permission) {
      const hasSpecificPermission = authStore.isAdmin || authStore.hasPermission(to.meta.permission)
      if (!hasSpecificPermission) {
        next({ name: 'admin-dashboard' })
        return
      }
    }
  }
  
  // Redirect authenticated users away from guest-only pages
  if (to.meta.guestOnly && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
    return
  }
  
  next()
})

export default router