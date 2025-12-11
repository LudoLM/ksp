export default [
  {
    path: 'cours',
    name: 'AdminCours',
    meta: { navLabel: 'Cours', navGroup: 'cours', isCategory: true  },
    children: [
      { path: 'coursList',
        name: 'CoursAdmin',
        label: 'Liste de cours',
        component: () => import('../../views/admin/CoursListTableAdmin.vue'),
        meta: { navLabel: "Liste des cours", navGroup: 'cours', requiresAdmin: true, displayInNav: true, order: 1}
      },
      { path: 'add',
        name: 'CreateCours',
        label: 'Créer cours',
        component: () => import('../../views/CoursForm.vue'),
        meta: { navLabel: "Créer un cours", navGroup: 'cours', displayInNav: true, order: 2}
      },
      { path: 'edit/:id', name: 'EditCours',
        component: () => import('../../views/CoursForm.vue'),
        meta: { navLabel: "Modifier un cours", navGroup: 'cours', displayInNav: true, order: 3}
      },
      { path: 'coursType/add', name: 'CreateTypeCours',
        label: 'Créer Type de cours',
        component: () => import('../../views/TypeCoursForm.vue'),
        meta: { navLabel: "Créer un type de cours", navGroup: 'cours', displayInNav: true, order: 4}
      },
      { path: 'coursType/edit', name: 'EditTypeCours',
        label: 'Modifier Type de cours',
        component: () => import('../../views/TypeCoursForm.vue'),
        meta: { navLabel: "Modifier un type de cours", navGroup: 'cours', displayInNav: true, order: 5}
      },
      { path: 'coursDetails/:id', name: 'AdminCoursDetails',
        component: () => import('../../views/CoursDetails.vue'),
        meta: { navLabel: "Détails", navGroup: 'cours', displayInNav: false}
      },
      { path: 'createWeekType', name: 'CreateWeekType',
        label: 'Gérer semaine type',
        component: () => import('../../views/admin/CreateWeekType.vue'),
        meta: { navLabel: "Gérer une semaine type", navGroup: 'cours', displayInNav: true}
      },
    ],
  }
]
