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
import App from '../App.vue'


const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'Accueil',
            component: Home
        },
        {
            path: '/schoolback',
            name: 'Ecole du dos',
            component: Schoolback
        },
        {
            path: '/othersClasses',
            name: 'Autres cours',
            // route level code-splitting
            // this generates a separate chunk (about.[hash].js) for this route
            // which is lazy-loaded when the route is visited.
            component: OthersClasses
        },
        {
            path: '/inscriptions',
            name: 'Inscriptions',
            // route level code-splitting
            // this generates a separate chunk (about.[hash].js) for this route
            // which is lazy-loaded when the route is visited.
            component: Inscriptions
        },
        {
            path: '/schedule',
            name: 'Programme',
            // route level code-splitting
            // this generates a separate chunk (about.[hash].js) for this route
            // which is lazy-loaded when the route is visited.
            component: Schedule
        },
        {
            path: '/contact',
            name: 'Contact',
            // route level code-splitting
            // this generates a separate chunk (about.[hash].js) for this route
            // which is lazy-loaded when the route is visited.
            component: Contact
        },
        {
            path: '/about',
            name: 'A propos',
            // route level code-splitting
            // this generates a separate chunk (about.[hash].js) for this route
            // which is lazy-loaded when the route is visited.
            component: About
        },
    ]
})

const appPinia = createPinia()
const app = createApp(App).use(router).use(appPinia)
app.mount('#app')
