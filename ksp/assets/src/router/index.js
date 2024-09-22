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




const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', name: 'Accueil', component: Home },
        { path: '/schoolback', name: 'Ecole du dos', component: Schoolback },
        { path: '/inscriptions', name: 'Inscriptions', component: Inscriptions },
        { path: '/schedule', name: 'Programme', component: Schedule },
        { path: '/contact', name: 'Contact', component: Contact },
        { path: '/coursDetails/:id', name: 'CoursDetail', component: CoursDetail },
        {path: '/cours/add', name: 'createCours', component: CoursForm},
    ],
})

const vuetify = createVuetify({
    components,
    directives,
})

const appPinia = createPinia()
const app = createApp(App).use(router).use(appPinia).use(vuetify)
app.mount('#app')
