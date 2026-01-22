const routes = [
  {
    path: '/',
    component: () => import('layouts/AuthLayout.vue'),
    children: [{ path: '', component: () => import('pages/LoginPage.vue') }],
  },
  {
    path: '/app',
    component: () => import('layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') },
      { path: '/group', component: () => import('pages/GroupPage.vue') },
      { path: '/user', component: () => import('pages/UserPage.vue') },
      { path: '/khatma-tilawa', component: () => import('pages/KhatmaTilawaPage.vue') },
      { path: '/khatma-tilawa/:id/assign', component: () => import('pages/AssignPage.vue') },
      { path: '/group-reading', component: () => import('pages/GroupReadingPage.vue') },
      { path: '/change-password', component: () => import('pages/ChangePasswordPage.vue') }
    ],
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue'),
  },
]

export default routes
