<script setup>
import { X } from 'lucide-vue-next'
import { sizingGuide } from '../data/catalog'
import { useBooking } from '../composables/useBooking'

const { state } = useBooking()

function close() {
  state.sizingOpen = false
}
</script>

<template>
  <Teleport to="body">
    <div v-if="state.sizingOpen" class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center">
      <button class="absolute inset-0 bg-slateink/50" type="button" aria-label="Close sizing guide" @click="close" />
      <div
        role="dialog"
        aria-labelledby="sizing-title"
        aria-modal="true"
        class="relative z-10 max-h-[88vh] w-full max-w-2xl overflow-y-auto rounded-t-3xl bg-white p-6 shadow-float sm:rounded-3xl"
      >
        <div class="mb-5 flex items-start justify-between">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-alpine-700">Sizing guide</p>
            <h3 id="sizing-title" class="mt-1 text-2xl font-extrabold text-slateink">
              Rider height vs. ski & board length
            </h3>
            <p class="mt-2 text-sm text-slate-600">
              Beginners: stay toward the shorter end. Pro / powder: go longer. Technicians still verify ISO 11088 DIN
              on every pair.
            </p>
          </div>
          <button type="button" class="rounded-full p-2 hover:bg-slate-100" aria-label="Close" @click="close">
            <X class="h-5 w-5" />
          </button>
        </div>

        <table class="w-full overflow-hidden rounded-2xl text-left text-sm">
          <thead class="bg-slateink text-white">
            <tr>
              <th class="px-4 py-3 font-semibold">Rider height</th>
              <th class="px-4 py-3 font-semibold">Ski length</th>
              <th class="px-4 py-3 font-semibold">Snowboard</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, i) in sizingGuide"
              :key="row.height"
              class="border-b border-slate-100"
              :class="i % 2 === 0 ? 'bg-slate-50' : 'bg-white'"
            >
              <td class="px-4 py-3 font-semibold text-slateink">{{ row.height }}</td>
              <td class="px-4 py-3 text-slate-600">{{ row.ski }}</td>
              <td class="px-4 py-3 text-slate-600">{{ row.board }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </Teleport>
</template>
