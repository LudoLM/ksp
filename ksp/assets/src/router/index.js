import {createApp} from 'vue'
import {createPinia} from "pinia";
import {createRouter, createWebHistory} from 'vue-router'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import Home from '../views/Home.vue'
import App from '../App.vue'
import CoursForm from "../views/CoursForm.vue";
import useGetElementsToken from "../utils/useGetElementsToken";
import Profile from '../views/Profile.vue';
import TypeCoursForm from "../views/TypeCoursForm.vue";
import DefaultLayout from "../layouts/DefaultLayout.vue";
import {useUserStore} from "../store/user";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: DefaultLayout,
            children: [
                { path: '', name: 'Accueil', component: Home },
                { path: '/coursDescriptions', name: 'Les cours', component: () => import('../views/CoursDescriptions.vue')},
                { path: '/calendar', name: 'Calendrier', component: () => import('../views/Calendar.vue')},
                { path: '/formules', name: 'Packs', component: () => import('../views/Pricing.vue')},
                { path: '/pratique', name: 'Pratique', component: () => import('../views/Pratique.vue') },
                { path: '/coursDetails/:id', name: 'CoursDetails', component:() => import('../views/CoursDetails.vue')},
                { path: '/login', name: 'Login', component: () => import('../views/LoginForm.vue')},
                { path: '/register', name: 'Register', component: () => import('../views/Register.vue')},
                { path: '/merci', name: 'Merci', component: () => import('../views/Merci.vue')},
                { path: '/profile', name: 'Profile', component: Profile},

            ]

        },
        {
            path: '/admin',
            component: DefaultLayout,
            meta: { requiresAdmin: true },
            name: 'admin',
            children: [
                { path: '', name: 'Statistiques', component: () => import('../views/admin/DataStats.vue'), meta: {requiresAdmin: true}},
                { path: 'profile', name: 'AdminProfile', component: Profile, meta: {requiresAdmin: true}},
                {
                  path: 'cours',
                  name: 'Cours',
                  meta: {requiresAdmin: true},
                  children: [
                      { path: 'coursList', name: 'CoursAdmin', label: "Liste de cours", component: () => import('../views/CoursListAdmin.vue'), meta: {requiresAdmin: true}},
                      { path: 'add', name: 'CreateCours', label: "Créer cours", component: CoursForm, meta: {requiresAdmin: true}},
                      { path: 'edit/:id', name: 'EditCours', component: CoursForm, meta: {requiresAdmin: true}},
                      { path: 'coursType/add', name: 'CreateTypeCours', label: "Créer Type de cours", component: TypeCoursForm, meta: {requiresAdmin: true}},
                      { path: 'coursType/edit', name: 'EditTypeCours', label: "Modifier Type de cours", component: TypeCoursForm, meta: {requiresAdmin: true}},
                      { path: 'coursDetails/:id', name: 'AdminCoursDetails', component: () => import('../views/CoursDetails.vue'), meta: {requiresAdmin: true}}
                  ]
                },
            ]
        },
    ],
})

const vuetify = createVuetify({
    theme: {
        defaultTheme: 'light',
    },
  components,
  directives,
})


// Garde de navigation globale
router.beforeEach((to, from, next) => {

  const tokenExpiration = useGetElementsToken()?.exp;
  const userStore = useUserStore();

  // 1. Vérifie si le token est expiré
  if (tokenExpiration <= Date.now() / 1000) {
    userStore.logout();
    return next({
      path: '/',
      query: {
        alertMessage: 'Votre session a expiré. Veuillez vous reconnecter.',
        alertType: 'info',
        alertVisible: true,
      },
    });
  }

  // 2. Vérifie si la route nécessite un rôle admin
  if (to.matched.some(record => record.meta.requiresAdmin)) {
    const userRole = localStorage.getItem('token')
      ? useGetElementsToken().roles[0]
      : null;

    if (!userRole) {
      // Pas authentifié, redirige vers la page de connexion
      return next({ path: '/' });
    }

    if (userRole !== 'ROLE_ADMIN') {
      // Non admin, redirige avec un message d'erreur
      return next({
        path: '/',
        query: {
          alertMessage: "Vous n'avez pas les droits pour accéder à cette page.",
          alertType: 'error',
          alertVisible: true,
        },
      });
    }
  }

  // 3. Si aucune condition ne bloque, continue la navigation
  next();
});




const appPinia = createPinia()
const app = createApp(App).use(appPinia).use(router).use(vuetify)
app.mount('#app')
