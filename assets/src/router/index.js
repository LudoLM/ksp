import {createApp, watch} from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import App from '../App.vue';
import Home from '../views/Home.vue';
import CoursForm from '../views/CoursForm.vue';
import Profile from '../views/Profile.vue';
import TypeCoursForm from '../views/TypeCoursForm.vue';
import DefaultLayout from '../layouts/DefaultLayout.vue';
import { useUserStore } from '../store/user';
import LoginLayout from "../layouts/LoginLayout.vue";
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import {createPinia, storeToRefs} from "pinia";
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      component: DefaultLayout,
      children: [
        { path: '', name: 'Accueil', component: Home, meta: {title: 'Kiné Sport Santé'} },
        { path: '/coursDescriptions', name: 'Les cours', meta: {title: 'Nos cours'} ,component: () => import('../views/CoursDescriptions.vue') },
        { path: '/calendar', name: 'Calendrier', meta: {title: 'Calendrier des cours'} ,component: () => import('../views/Calendar.vue') },
        { path: '/packs', name: 'Packs', meta: {title: 'Packs proposés'} ,component: () => import('../views/Pricing.vue') },
        { path: '/pratique', name: 'Pratique', meta: {title: 'Infos pratiques'} ,component: () => import('../views/Pratique.vue') },
        { path: '/coursDetails/:id', name: 'CoursDetails', meta: {title: 'Détails'} ,component: () => import('../views/CoursDetails.vue') },
        { path: '/merci', name: 'Merci', meta: {title: 'Crédits', requiresAuth: true} , component: () => import('../views/Merci.vue') },
        { path: '/profile', name: 'Profile', meta: {title: 'Mon profil', requiresAuth: true} , component: Profile },
      ],
    },
    {
      path: '/',
      component: LoginLayout,
      children: [
        { path: '/login', name: 'Login', meta: {title: 'Authentification'} ,component: () => import('../views/Signin.vue') },
        { path: '/register', name: 'Register', meta: {title: 'Création de compte'} ,component: () => import('../views/Signup.vue') },
        { path: '/editProfile', name: 'EditProfile', meta: {title: 'Modifier son profil'} ,component: () => import('../views/Signup.vue') },
        { path: '/resetPassword/:id/:token', name: 'ResetPassword', meta: {title: 'Réinitialiser mot de passe'} ,component: () => import('../views/ResetPassword.vue') },
      ]
    },
    {
      path: '/admin',
      component: DefaultLayout,
      meta: { requiresAdmin: true },
      name: 'admin',
      children: [
        { path: '', name: 'Statistiques',component: () => import('../views/admin/DataStats.vue'), meta: { requiresAdmin: true, title:"Dashboard" } },
        { path: 'profile', name: 'AdminProfile', component: Profile, meta: { requiresAdmin: true } },
        {
          path: 'cours',
          name: 'Cours',
          meta: { requiresAdmin: true },
          children: [
            /*{ path: 'coursList', name: 'CoursAdmin', label: 'Liste de cours', component: () => import('../views/CoursListTableAdmin.vue'), meta: { requiresAdmin: true } },*/
            { path: 'coursList', name: 'CoursAdmin', label: 'Liste de cours', component: () => import('../views/admin/CoursListTableAdmin.vue'), meta: { requiresAdmin: true, title:"Liste des cours" } },
            { path: 'add', name: 'CreateCours', label: 'Créer cours', component: CoursForm, meta: { requiresAdmin: true, title: "Création de cours" } },
            { path: 'edit/:id', name: 'EditCours', component: CoursForm, meta: { requiresAdmin: true, title: "Modifier un cours" } },
            { path: 'coursType/add', name: 'CreateTypeCours', label: 'Créer Type de cours', component: TypeCoursForm, meta: { requiresAdmin: true, title: "Création de type de cours" } },
            { path: 'coursType/edit', name: 'EditTypeCours', label: 'Modifier Type de cours', component: TypeCoursForm, meta: { requiresAdmin: true, title: "Modifier un type de cours" } },
            { path: 'coursDetails/:id', name: 'AdminCoursDetails', component: () => import('../views/CoursDetails.vue'), meta: { requiresAdmin: true, title: "Détails" } },
            { path: 'createWeekType', name: 'CreateWeekType', label: 'Gérer semaine type', component: () => import('../views/admin/CreateWeekType.vue'), meta: { requiresAdmin: true, title: "Gestion de semaines types" }},
          ],
        },
      ],
    },
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
