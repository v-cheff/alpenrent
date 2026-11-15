import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../HomePage.vue'
import MyEquipmentPage from '../my-equipment/MyEquipmentPage.vue'
import MyBookingsPage from '../my-bookings/MyBookingsPage.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomePage,
  },
  {
    // Owner: rental points, equipment and incoming bookings
    path: '/my-equipment',
    name: 'my-equipment',
    component: MyEquipmentPage,
  },
  {
    // Renter: bookings the user made
    path: '/my-bookings',
    name: 'my-bookings',
    component: MyBookingsPage,
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
