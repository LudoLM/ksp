import {createApp, watch} from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import App from '../App.vue';
import { useUserStore } from '../store/user';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import {createPinia, storeToRefs} from "pinia";
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import adminRoutes from './admin/admin.routes.js'


const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      component: () => import('../layouts/DefaultLayout.vue'),
      children: [
        { path: '', name: 'Accueil', component: () => import('../views/Home.vue'), meta: {title: 'Kiné Sport Santé', displayInNav: true} },
        { path: '/coursDescriptions', name: 'Les cours', meta: {title: 'Nos cours', displayInNav: true} ,component: () => import('../views/CoursDescriptions.vue') },
        { path: '/calendar', name: 'Calendrier', meta: {title: 'Calendrier des cours', displayInNav: true} ,component: () => import('../views/Calendar.vue') },
        { path: '/packs', name: 'Packs', meta: {title: 'Packs proposés', displayInNav: true} ,component: () => import('../views/Pricing.vue') },
        { path: '/pratique', name: 'Pratique', meta: {title: 'Infos pratiques', displayInNav: true} ,component: () => import('../views/Pratique.vue') },
        { path: '/coursDetails/:id', name: 'CoursDetails', meta: {title: 'Détails', displayInNav: true} ,component: () => import('../views/CoursDetails.vue') },
        { path: '/merci', name: 'Merci', meta: {title: 'Crédits', requiresAuth: true, displayInNav: false} , component: () => import('../views/Merci.vue') },
        { path: '/profile', name: 'Profile', meta: {title: 'Mon profil', requiresAuth: true, displayInNav: false} , component: import('../views/Profile.vue') },
      ],
    },
    {
      path: '/',
      component: () => import('../layouts/LoginLayout.vue'),
      children: [
        { path: '/login', name: 'Login', meta: {title: 'Authentification', displayInNav: false} ,component: () => import('../views/Signin.vue') },
        { path: '/register', name: 'Register', meta: {title: 'Création de compte', displayInNav: false } ,component: () => import('../views/Signup.vue') },
        { path: '/editProfile', name: 'EditProfile', meta: {title: 'Modifier son profil', displayInNav: false} ,component: () => import('../views/Signup.vue') },
        { path: '/resetPassword/:id/:token', name: 'ResetPassword', meta: {title: 'Réinitialiser mot de passe', displayInNav: false} ,component: () => import('../views/ResetPassword.vue') },
        { path: '/editProfile/:id', name: 'AdminEditProfile', meta: {title: 'Modifier son profil', requiresAdmin: true, displayInNav: false} ,component: () => import('../views/Signup.vue') },
      ]
    },
    adminRoutes,
  ],
  scrollBehavior() {
    document.getElementById('app').scrollIntoView({ behavior: 'smooth' });
  },
});

// Vuetify configuration
const vuetify = createVuetify({
  theme: {
    defaultTheme: 'light',
  },
  components,
  directives,
});

// Garde de navigation globale
router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore();
  const {isAuthenticated, isAdmin} = storeToRefs(userStore);
  // 1. Vérifie si la route nécessite une authentification
  if (to.matched.some(record => record.meta.requiresAuth)) {
    // Si l'utilisateur n'est pas authentifié, on le redirige la page d'accueil
    if (!isAuthenticated.value) {
      return next({path: '/'});
    }
  }

  // 2. Vérifie si la route nécessite un rôle admin
  // Cette condition ne sera vérifiée que si l'utilisateur est déjà authentifié (grâce au point 1)
  if (to.matched.some(record => record.meta.requiresAdmin)) {
    // Si l'utilisateur n'est pas admin, on le redirige vers la page d'accueil
    if (!isAdmin.value) {
      return next({ path: '/' });
    }
  }

  // 3. Si toutes les vérifications passent, on continue la navigation
  next();
});

const appPinia = createPinia();
appPinia.use(piniaPluginPersistedstate)
const app = createApp(App)
  .use(appPinia)
  .use(router)
  .use(vuetify)
  .component('VueDatePicker', VueDatePicker);

watch(
  () => router.currentRoute.value,
  (currentRoute) => {
    if (currentRoute.meta && currentRoute.meta.title) {
      document.title = currentRoute.meta.title;
    } else {
      document.title = 'Default Title';
    }
  },
  { immediate: true }
);

app.mount('#app');

export default router;
