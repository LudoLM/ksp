import type { RouteRecordRaw } from 'vue-router'
import coursRoutes from './cours.routes'
import userRoutes from './user.routes'

const adminRoutes: RouteRecordRaw = {
  path: '/admin',
  component: () => import('../../layouts/DefaultLayout.vue'),
  meta: { requiresAdmin: true },
  children: [
    {
      path: '',
      name: 'AdminDashboard',
      component: () => import('../../views/admin/DataStats.vue'),
      meta: { title: 'Dashboard' },
    },
    ...coursRoutes,
    ...userRoutes,
  ],
}

export default adminRoutes
