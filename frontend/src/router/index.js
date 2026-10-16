import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../HomePage.vue'
import AdminEquipmentPage from '../admin/AdminEquipmentPage.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomePage,
  },
  {
    path: '/admin',
    name: 'admin',
    component: AdminEquipmentPage,
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
