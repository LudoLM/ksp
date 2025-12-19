import { createApp, watch } from 'vue'
import { createRouter, createWebHistory, RouteRecordRaw, Router } from 'vue-router'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import App from '../App.vue'
import { useUserStore } from '../store/user'
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import { createPinia, storeToRefs } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import adminRoutes from './admin/admin.routes'

const router: Router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      component: () => import('../layouts/DefaultLayout.vue'),
      children: [
        {
          path: '',
          name: 'Accueil',
          component: () => import('../views/Home.vue'),
          meta: { title: 'Kiné Sport Santé', displayInNav: true },
        },
        {
          path: '/coursDescriptions',
          name: 'Les cours',
          component: () => import('../views/CoursDescriptions.vue'),
          meta: { title: 'Nos cours', displayInNav: true },
        },
        {
          path: '/calendar',
          name: 'Calendrier',
          component: () => import('../views/Calendar.vue'),
          meta: { title: 'Calendrier des cours', displayInNav: true },
        },
        {
          path: '/packs',
          name: 'Packs',
          component: () => import('../views/Pricing.vue'),
          meta: { title: 'Packs proposés', displayInNav: true },
        },
        {
          path: '/pratique',
          name: 'Pratique',
          component: () => import('../views/Pratique.vue'),
          meta: { title: 'Infos pratiques', displayInNav: true },
        },
        {
          path: '/coursDetails/:id',
          name: 'CoursDetails',
          component: () => import('../views/CoursDetails.vue'),
          meta: { title: 'Détails', displayInNav: true },
        },
        {
          path: '/merci',
          name: 'Merci',
          component: () => import('../views/Merci.vue'),
          meta: { title: 'Crédits', requiresAuth: true, displayInNav: false },
        },
        {
          path: '/profile',
          name: 'Profile',
          component: () => import('../views/Profile.vue'),
          meta: { title: 'Mon profil', requiresAuth: true, displayInNav: false },
        },
      ],
    },
    {
      path: '/',
      component: () => import('../layouts/LoginLayout.vue'),
      children: [
        {
          path: '/login',
          name: 'Login',
          component: () => import('../views/Signin.vue'),
          meta: { title: 'Authentification', displayInNav: false },
        },
        {
          path: '/register',
          name: 'Register',
          component: () => import('../views/Signup.vue'),
          meta: { title: 'Création de compte', displayInNav: false },
        },
        {
          path: '/editProfile',
          name: 'EditProfile',
          component: () => import('../views/Signup.vue'),
          meta: { title: 'Modifier son profil', displayInNav: false },
        },
        {
          path: '/resetPassword/:id/:token',
          name: 'ResetPassword',
          component: () => import('../views/ResetPassword.vue'),
          meta: { title: 'Réinitialiser mot de passe', displayInNav: false },
        },
        {
          path: '/editProfile/:id',
          name: 'AdminEditProfile',
          component: () => import('../views/Signup.vue'),
          meta: { title: 'Modifier son profil', requiresAdmin: true, displayInNav: false },
        },
      ],
    },
    adminRoutes as RouteRecordRaw,
  ],
  scrollBehavior() {
    const appElement = document.getElementById('app')
    if (appElement) {
      appElement.scrollIntoView({ behavior: 'smooth' })
    }
  },
})

// Vuetify configuration
const vuetify = createVuetify({
  theme: {
    defaultTheme: 'light',
  },
  components,
  directives,
})

// Global navigation guard
// @ts-ignore - User store has typing issues with persist plugin
router.beforeEach(async (to, _, next) => {
  // @ts-ignore
  const userStore = useUserStore()
  // @ts-ignore
  const { isAuthenticated, isAdmin } = storeToRefs(userStore)

  // 1. Check if route requires authentication
  if (to.matched.some((record) => record.meta?.requiresAuth)) {
    if (!isAuthenticated.value) {
      return next({ path: '/' })
    }
  }

  // 2. Check if route requires admin role
  if (to.matched.some((record) => record.meta?.requiresAdmin)) {
    if (!isAdmin.value) {
      return next({ path: '/' })
    }
  }

  // 3. If all checks pass, continue navigation
  next()
})

const appPinia = createPinia()
appPinia.use(piniaPluginPersistedstate)
const app = createApp(App)
  .use(appPinia)
  .use(router)
  .use(vuetify)
  .component('VueDatePicker', VueDatePicker)

watch(
  () => router.currentRoute.value,
  (currentRoute) => {
    if (currentRoute.meta && currentRoute.meta.title) {
      document.title = currentRoute.meta.title as string
    } else {
      document.title = 'Default Title'
    }
  },
  { immediate: true }
)

app.mount('#app')

export default router
