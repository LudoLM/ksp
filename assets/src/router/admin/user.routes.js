// src/router/admin/user.routes.js
export default [
  {
    path: 'users',
    name: 'AdminUsers',
    meta: { navLabel: 'Utilisateurs', navGroup: 'users', isCategory: true, requiresAdmin: true },
    children: [
      {
        path: 'controlUser',
        name: 'ControlUser',
        component: () => import('../../views/admin/ControlUser.vue'),
        meta: { navLabel: 'Gestion utilisateurs', navGroup: 'users',  displayInNav: true},
      },
      {
        path: 'profile/:id?',
        name: 'AdminProfile',
        label: 'Profil utilisateur',
        component: () => import('../../views/Profile.vue'),
        meta: { requiresAdmin: true, title: "Profil utilisateur" }
      }
    ]
  }
];
