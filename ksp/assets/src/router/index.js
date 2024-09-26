import {createApp} from 'vue'
import {createPinia} from "pinia";
import {createRouter, createWebHistory} from 'vue-router'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import Home from '../views/Home.vue'
import Schoolback from '../views/Schoolback.vue'
import Inscriptions from '../views/Inscriptions.vue'
import Schedule from '../views/Schedule.vue'
import Contact from '../views/Contact.vue'
import CoursDetail from '../views/CoursDetails.vue'
import App from '../App.vue'
import CoursForm from "../views/CoursForm.vue";
import LoginForm from "../views/LoginForm.vue";
import Register from "../views/Register.vue";
import useGetElementsToken from "../utils/useGetElementsToken";




const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', name: 'Accueil', component: Home },
        { path: '/schoolback', name: 'Ecole du dos', component: Schoolback },
        { path: '/inscriptions', name: 'Inscriptions', component: Inscriptions },
        { path: '/schedule', name: 'Programme', component: Schedule },
        { path: '/contact', name: 'Contact', component: Contact },
        { path: '/coursDetails/:id', name: 'CoursDetail', component: CoursDetail },
        { path: '/cours/add', name: 'createCours', component: CoursForm, meta: {requiresAdmin: true}},
        { path: '/login', name: 'login', component: LoginForm},
        { path: '/register', name: 'register', component: Register},

    ],
})

const vuetify = createVuetify({
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
const app = createApp(App).use(router).use(appPinia).use(vuetify)
app.mount('#app')
