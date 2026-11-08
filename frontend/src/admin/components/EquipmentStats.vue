<script setup>
defineProps({
  stats: { type: Object, required: true },
})
</script>

<template>
  <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <p class="text-sm text-slate-500">Total equipment</p>
      <div class="mt-3 flex items-end justify-between">
        <strong class="text-3xl font-bold text-slate-900">{{ stats.total }}</strong>
        <span class="rounded-full bg-cyan-50 px-2 py-1 text-xs font-semibold text-cyan-700">Live</span>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <p class="text-sm text-slate-500">Available now</p>
      <div class="mt-3 flex items-end justify-between">
        <strong class="text-3xl font-bold text-slate-900">{{ stats.availableNow }}</strong>
        <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Open</span>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <p class="text-sm text-slate-500">Selected date</p>
      <div class="mt-3 flex items-end justify-between">
        <strong class="text-3xl font-bold text-slate-900">{{ stats.selectedDateCount }}</strong>
        <span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Slots</span>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <p class="text-sm text-slate-500">Categories</p>
      <div class="mt-3 flex items-end justify-between">
        <strong class="text-3xl font-bold text-slate-900">{{ stats.categories }}</strong>
        <span class="rounded-full bg-violet-50 px-2 py-1 text-xs font-semibold text-violet-700">Groups</span>
      </div>
    </div>
  </section>
</template>
