<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import HomePage from './HomePage.vue'
import MyEquipmentPage from './my-equipment/MyEquipmentPage.vue'
import MyBookingsPage from './my-bookings/MyBookingsPage.vue'

// Renters manage bookings on /my-bookings, owners manage rental points on /my-equipment.
const pages = {
  '/my-equipment': MyEquipmentPage,
  '/my-bookings': MyBookingsPage,
}

const currentPath = ref(window.location.pathname || '/')

const currentPage = computed(() => pages[currentPath.value.replace(/\/+$/, '')] ?? HomePage)

function syncPath() {
  currentPath.value = window.location.pathname || '/'
}

onMounted(() => {
  window.addEventListener('popstate', syncPath)
})

onUnmounted(() => {
  window.removeEventListener('popstate', syncPath)
})
</script>

<template>
  <component :is="currentPage" />
</template>
