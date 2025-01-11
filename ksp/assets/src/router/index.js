import {createApp} from 'vue'
import {createPinia} from "pinia";
import {createRouter, createWebHistory} from 'vue-router'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import Home from '../views/Home.vue'
import CoursDetail from '../views/CoursDetails.vue'
import App from '../App.vue'
import CoursForm from "../views/CoursForm.vue";
import useGetElementsToken from "../utils/useGetElementsToken";
import Profile from '../views/Profile.vue';
import TypeCoursForm from "../views/TypeCoursForm.vue";
import DefaultLayout from "../layouts/DefaultLayout.vue";
import AdminLayout from "../layouts/AdminLayout.vue";
import DataStats from "../views/admin/DataStats.vue";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: DefaultLayout,
            children: [
                { path: '', name: 'Accueil', component: Home },
                { path: '/schoolback', name: 'Ecole du dos', component: () => import('../views/Schoolback.vue')},
                { path: '/inscriptions', name: 'Inscriptions', component: () =>  import('../views/Inscriptions.vue')},
                { path: '/schedule', name: 'Programme', component: () => import('../views/Schedule.vue')},
                { path: '/contact', name: 'Contact', component: () => import('../views/Contact.vue') },
                { path: '/coursDetails/:id', name: 'CoursDetail', component: CoursDetail },
                { path: '/login', name: 'Login', component: () => import('../views/LoginForm.vue')},
                { path: '/register', name: 'Register', component: () => import('../views/Register.vue')},
                { path: '/cours/acheter', name: 'AcheterCours', component: () => import('../views/AcheterCours.vue')},
                { path: '/merci', name: 'Merci', component: () => import('../views/Merci.vue')},
                { path: '/profile', name: 'Profile', component: Profile},

            ]

        },
        {
            path: '/admin',
            component: AdminLayout,
            meta: { requiresAdmin: true },
            name: 'admin',
            children: [
                { path: '', name: 'Dashboard', component: () => import('../views/admin/Dashboard.vue') },
                { path: 'coursList', name: 'CoursAdmin', component: () => import('../views/Home.vue') },
                { path: 'cours/edit/:id', name: 'EditCours', component: CoursForm, meta: {requiresAdmin: true}},
                { path: 'coursType/add', name: 'CreateTypeCours', component: TypeCoursForm, meta: {requiresAdmin: true}},
                { path: 'coursType/edit', name: 'EditTypeCours', component: TypeCoursForm, meta: {requiresAdmin: true}},
                { path: 'cours/add', name: 'CreateCours', component: CoursForm, meta: {requiresAdmin: true}},
                { path: 'dataStats', name: 'DataStats', component: DataStats, meta: {requiresAdmin: true}},
            ]
        }
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

    // Vérifie si la route nécessite un rôle admin
    if (to.matched.some(record => record.meta.requiresAdmin)) {
        const userRole = localStorage.getItem('token') ? useGetElementsToken().roles[0] : null;
        if (userRole) {
            if (userRole === 'ROLE_ADMIN') {
                next();  // L'utilisateur est admin, continuer
            } else {
                next({ path: '/',
                    query: {
                        alertMessage: "Vous n'avez pas les droits pour accéder à cette page",
                        alertType: 'error',
                        alertVisible: true
                    }
                });  // Redirige si l'utilisateur n'est pas admin
            }
        } else {
            next({ path: '/login' });  // Redirige vers la page de login si pas authentifié
        }
    } else {
        next();  // Si pas de restriction, continuer normalement
    }
});



const appPinia = createPinia()
const app = createApp(App).use(appPinia).use(router).use(vuetify)
app.mount('#app')
