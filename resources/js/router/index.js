import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Import views
const Dashboard = () => import('@/views/Dashboard.vue')
const Home = () => import('@/views/Home.vue')
const Login = () => import('@/views/auth/Login.vue')
const Register = () => import('@/views/auth/Register.vue')
const ForgotPassword = () => import('@/views/auth/ForgotPassword.vue')
const Profile = () => import('@/views/Profile.vue')
const EditProfile = () => import('@/views/EditProfile.vue')
const Products = () => import('@/views/Products.vue')
const MyProducts = () => import('@/views/MyProducts.vue')
const PublicProducts = () => import('@/views/PublicProducts.vue')
const TestProducts = () => import('@/views/TestProducts.vue')
const ProductDetail = () => import('@/views/ProductDetail.vue')
const CreateProduct = () => import('@/views/CreateProduct.vue')
const EditProduct = () => import('@/views/EditProduct.vue')

const Messages = () => import('@/views/Messages.vue')
const ProductDiscussions = () => import('@/views/ProductDiscussions.vue')
const SellerProductConversations = () => import('@/views/SellerProductConversations.vue')
const Orders = () => import('@/views/Orders.vue')
const Notifications = () => import('@/views/Notifications.vue')
const Search = () => import('@/views/Search.vue')
const ImageSearch = () => import('@/views/ImageSearch.vue')
const UserProfile = () => import('@/views/UserProfile.vue')
const Wallet = () => import('@/views/Wallet.vue')
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
    path: '/profile/:id',
    name: 'profile-redirect',
    redirect: to => ({ name: 'user-profile', params: { id: to.params.id } })
  },
  {
    path: '/profile/edit',
    name: 'edit-profile',
    component: EditProfile,
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
    path: '/test-products',
    name: 'test-products',
    component: TestProducts,
    meta: { requiresAuth: false }
  },
  {
    path: '/my-products',
    name: 'my-products',
    component: MyProducts,
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
    path: '/products/:id/edit',
    name: 'edit-product',
    component: EditProduct,
    props: true,
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
    path: '/messages',
    name: 'messages',
    component: Messages,
    meta: { requiresAuth: true }
  },
  {
    path: '/discussions',
    name: 'product-discussions',
    component: () => import('@/views/ChatHub.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/discussions/old',
    name: 'product-discussions-old',
    component: ProductDiscussions,
    meta: { requiresAuth: true }
  },
  {
    path: '/my-sales-conversations',
    name: 'seller-conversations',
    component: SellerProductConversations,
    meta: { requiresAuth: true }
  },
  {
    path: '/conversations/:id',
    name: 'conversation-detail',
    component: () => import('@/views/ConversationDetail.vue'),
    props: true,
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
    path: '/wallet',
    name: 'wallet',
    component: Wallet,
    meta: { requiresAuth: true }
  },
  {
    path: '/search',
    name: 'search',
    component: Search,
    meta: { requiresAuth: false }
  },
  {
    path: '/search/image',
    name: 'image-search',
    component: ImageSearch,
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