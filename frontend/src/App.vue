<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import HomePage from './HomePage.vue'
import AdminEquipmentPage from './admin/AdminEquipmentPage.vue'

const currentPath = ref(window.location.pathname || '/')

const currentView = computed(() => (currentPath.value === '/admin' ? 'admin' : 'home'))

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
  <HomePage v-if="currentView === 'home'" />
  <AdminEquipmentPage v-else />
</template>
