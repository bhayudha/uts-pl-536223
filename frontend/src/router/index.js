import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import BukuView from '../views/BukuView.vue';

const routes = [
    { path: '/', redirect: '/login' },
    { path: '/login', component: LoginView },
    {
        path: '/buku',
        component: BukuView,
        beforeEnter: (to, from, next) => {
            if (!localStorage.getItem('token')) {
                next('/login');
            } else {
                next();
            }
        }
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;