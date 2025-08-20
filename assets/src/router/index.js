import {createApp, watch} from 'vue';
import { createPinia } from 'pinia';
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
import { createAlertStore } from '../store/alert';
import useGetElementsToken from '../utils/useGetElementsToken';
import LoginLayout from "../layouts/LoginLayout.vue";
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const alertStore = createAlertStore(); // Instance unique d'alertStore

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
        { path: '/merci', name: 'Merci', meta: {title: 'Crédits'} , component: () => import('../views/Merci.vue') },
        { path: '/profile', name: 'Profile', meta: {title: 'Mon profil'} , component: Profile },
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
router.beforeEach((to, from, next) => {
  const tokenExpiration = useGetElementsToken()?.exp;
  const userStore = useUserStore();

  // 1. Vérifie si le token est expiré
  if (tokenExpiration && tokenExpiration <= Date.now() / 1000) {
    alertStore.setAlert('Votre session a expiré. Veuillez vous reconnecter.', 'info');
    userStore.logout();
    return next({ path: '/' });
  }

  // 2. Vérifie si la route nécessite un rôle admin
  if (to.matched.some(record => record.meta.requiresAdmin)) {
    const userRole = localStorage.getItem('token')
      ? useGetElementsToken().roles[0]
      : null;

    if (!userRole || userRole !== 'ROLE_ADMIN') {
      alertStore.setAlert("Vous n'avez pas les droits pour accéder à cette page.", 'error');
      return next({ path: '/' });
    }
  }

  // 3. Si aucune condition ne bloque, continue la navigation
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
