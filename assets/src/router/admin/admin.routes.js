
import coursRoutes from './cours.routes'
import userRoutes from './user.routes'

export default {
  path: '/admin',
  component: () => import('../../layouts/DefaultLayout.vue'),
  meta: { requiresAdmin: true },
  children: [
    {
      path: '',
      name: 'AdminDashboard',
      component: () => import('../../views/admin/DataStats.vue'),
      meta: { title: "Dashboard" }
    },
    ...coursRoutes,
    ...userRoutes
  ]
}

