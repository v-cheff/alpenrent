<script setup>
import { onMounted, reactive, watch } from 'vue'
import { CheckCircle2, X } from 'lucide-vue-next'
import SiteHeader from './components/SiteHeader.vue'
import HeroSection from './components/HeroSection.vue'
import EquipmentCatalog from './components/EquipmentCatalog.vue'
import TrustBlock from './components/TrustBlock.vue'
import SizePickerDialog from './components/SizePickerDialog.vue'
import SizingGuideModal from './components/SizingGuideModal.vue'
import MobileStickyBar from './components/MobileStickyBar.vue'
import SiteFooter from './components/SiteFooter.vue'
import AuthModal from './components/AuthModal.vue'
import { useAuth } from './composables/useAuth'
import { useBooking } from './composables/useBooking'

const { checkAuth, user } = useAuth()
const { state, selectedItem } = useBooking()

const toast = reactive({
  message: '',
  timer: null,
})

function notify(message) {
  toast.message = message
  if (toast.timer) clearTimeout(toast.timer)
  toast.timer = setTimeout(() => {
    toast.message = ''
  }, 4000)
}

// Notify on booking success
watch(
  () => state.bookingSuccess,
  (val) => {
    if (val) {
      notify(
        `Booking confirmed! "${selectedItem.value?.name || 'Equipment'}" has been successfully reserved.`
      )
      state.bookingSuccess = false
    }
  }
)

onMounted(() => {
  checkAuth()
})
</script>

<template>
  <div class="min-h-screen">
    <SiteHeader />
    <main>
      <HeroSection />
      <EquipmentCatalog />
      <TrustBlock />
    </main>
    <SiteFooter />
    <SizePickerDialog />
    <SizingGuideModal />
    <MobileStickyBar />
    <AuthModal />

    <!-- Toast Notifications -->
    <transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="toast.message"
        class="fixed bottom-6 right-6 z-50 flex max-w-md items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-sm font-semibold text-emerald-900 shadow-xl"
        role="status"
      >
        <CheckCircle2 class="h-5 w-5 text-emerald-600 shrink-0" />
        <span class="flex-1">{{ toast.message }}</span>
        <button
          type="button"
          class="rounded-lg p-1 text-emerald-600 transition hover:bg-emerald-100"
          aria-label="Close notification"
          @click="toast.message = ''"
        >
          <X class="h-4 w-4" />
        </button>
      </div>
    </transition>
  </div>
</template>
