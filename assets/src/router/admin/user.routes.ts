import type { RouteRecordRaw } from 'vue-router'

const userRoutes: RouteRecordRaw[] = [
  {
    path: 'users',
    name: 'AdminUsers',
    meta: { navLabel: 'Utilisateurs', navGroup: 'users', isCategory: true, requiresAdmin: true },
    children: [
      {
        path: 'controlUser',
        name: 'ControlUser',
        component: () => import('../../views/admin/ControlUser.vue'),
        meta: { navLabel: 'Gestion utilisateurs', navGroup: 'users', displayInNav: true },
      },
      {
        path: 'controlCertificate',
        name: 'controlCertificate',
        component: () => import('../../views/admin/ControlCertificate.vue'),
        meta: { navLabel: 'Gestion certificats', navGroup: 'users', displayInNav: true },
      },
      {
        path: 'profile/:id?',
        name: 'AdminProfile',
        component: () => import('../../views/Profile.vue'),
        meta: { requiresAdmin: true, title: 'Profil utilisateur' },
      },
      { path: 'usersActivities',
        name: 'UsersActivities',
        component: () => import('../../views/admin/UsersActivities.vue'),
        meta: { requiresAdmin: true, title: "Dernières actions utilisateurs" }}
    ],
  },
]

export default userRoutes
