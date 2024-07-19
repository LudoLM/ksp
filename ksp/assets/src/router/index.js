import {createApp} from 'vue'
import {createPinia} from "pinia";
import {createRouter, createWebHistory} from 'vue-router'

import Home from '../views/Home.vue'
import About from '../views/About.vue'
import Schoolback from '../views/Schoolback.vue'
import OthersClasses from '../views/OthersClasses.vue'
import Inscriptions from '../views/Inscriptions.vue'
import Schedule from '../views/Schedule.vue'
import Contact from '../views/Contact.vue'
import CoursDetail from '../views/CoursDetails.vue'
import App from '../App.vue'



const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', name: 'Accueil', component: Home },
        { path: '/schoolback', name: 'Ecole du dos', component: Schoolback },
        { path: '/othersClasses', name: 'Autres cours', component: OthersClasses },
        { path: '/inscriptions', name: 'Inscriptions', component: Inscriptions },
        { path: '/schedule', name: 'Programme', component: Schedule },
        { path: '/contact', name: 'Contact', component: Contact },
        { path: '/about', name: 'A propos', component: About },
        { path: '/coursDetails/:id', name: 'CoursDetail', component: CoursDetail },
    ],
})

const appPinia = createPinia()
const app = createApp(App).use(router).use(appPinia)
app.mount('#app')
