<script setup>
import { Mountain } from 'lucide-vue-next'
</script>

<template>
  <footer class="border-t border-slate-200 bg-slateink pb-24 pt-12 text-slate-300 md:pb-12">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 md:grid-cols-3 lg:px-8">
      <div>
        <p class="flex items-center gap-2 text-white">
          <Mountain class="h-5 w-5 text-sky-300" />
          <span class="text-lg font-extrabold">AlpenRent</span>
        </p>
        <p class="mt-3 max-w-xs text-sm leading-relaxed">
          Premium ski & snowboard rental across Tyrol. ISO 11088 binding service, slope-side exchange, transparent
          Austrian VAT pricing.
        </p>
      </div>
      <div class="text-sm">
        <p class="font-bold text-white">Stores</p>
        <ul class="mt-3 space-y-1">
          <li>Innsbruck Central Store</li>
          <li>St. Anton Base Station</li>
          <li>Sölden Glacier Hub</li>
          <li>Kitzbühel Town Shop</li>
        </ul>
      </div>
      <div class="text-sm">
        <p class="font-bold text-white">Legal</p>
        <ul class="mt-3 space-y-1">
          <li>Prices incl. 20% MwSt.</li>
          <li>Free cancellation until 24h before pickup</li>
          <li>Impressum · Datenschutz · AGB</li>
        </ul>
      </div>
    </div>
  </footer>
</template>
