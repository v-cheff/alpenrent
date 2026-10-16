<script setup>
import SiteHeader from './components/SiteHeader.vue'
import HeroSection from './components/HeroSection.vue'
import EquipmentCatalog from './components/EquipmentCatalog.vue'
import TrustBlock from './components/TrustBlock.vue'
import SizePickerDialog from './components/SizePickerDialog.vue'
import SizingGuideModal from './components/SizingGuideModal.vue'
import MobileStickyBar from './components/MobileStickyBar.vue'
import SiteFooter from './components/SiteFooter.vue'
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
  </div>
</template>
