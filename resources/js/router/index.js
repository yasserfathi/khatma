import { createRouter, createWebHistory } from 'vue-router';
import routes from './routes';

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ left: 0, top: 0 }),
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token');

  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!token) {
      next({ path: '/' });
    } else {
      next();
    }
  } else {
    // If going to login page while logged in, redirect to app
    if (to.path === '/' && token) {
      next({ path: '/app' });
    } else {
      next();
    }
  }
});

export default router;
