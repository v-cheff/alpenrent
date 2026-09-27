<script setup>
import { CheckCircle2, Shield, Snowflake, Star, RefreshCcw } from 'lucide-vue-next'
import { reviews } from '../data/catalog'

const badges = [
  {
    icon: Shield,
    title: 'ISO 11088 Safety Binding Adjustment Included',
    text: 'DIN settings verified by certified technicians before every handover.',
  },
  {
    icon: RefreshCcw,
    title: 'Free Exchange on Slopes',
    text: 'Swap length, flex or category at any AlpenRent partner station in Tyrol.',
  },
  {
    icon: Snowflake,
    title: 'Damages & Theft Insurance Option (+€3/day)',
    text: 'Optional coverage for breakage, theft and third-party ski damage.',
  },
]
</script>

<template>
  <section id="trust" class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="max-w-2xl">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-alpine-700">Austria · DACH trust</p>
        <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slateink sm:text-4xl">
          Built for Austrian rental law & slope safety
        </h2>
      </div>

      <div class="mt-10 grid gap-4 md:grid-cols-3">
        <article
          v-for="badge in badges"
          :key="badge.title"
          class="rounded-3xl border border-slate-200 bg-slate-50 p-6"
        >
          <component :is="badge.icon" class="h-6 w-6 text-alpine-600" />
          <h3 class="mt-4 text-base font-extrabold text-slateink">{{ badge.title }}</h3>
          <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ badge.text }}</p>
        </article>
      </div>

      <div
        class="mt-8 flex flex-col gap-3 rounded-3xl border border-sky-100 bg-alpine-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
      >
        <div class="flex items-start gap-3">
          <CheckCircle2 class="mt-0.5 h-5 w-5 shrink-0 text-alpine-700" />
          <div>
            <p class="font-extrabold text-slateink">Clear price breakdown</p>
            <p class="text-sm text-slate-600">
              All advertised daily rates are inclusive of 20% Austrian VAT / MwSt. Deposits are listed separately and
              released after return inspection.
            </p>
          </div>
        </div>
        <p class="whitespace-nowrap text-sm font-bold text-alpine-800">UID ATU12345678</p>
      </div>

      <div id="reviews" class="mt-14">
        <div class="mb-6 flex items-end justify-between gap-4">
          <div>
            <h3 class="text-2xl font-extrabold text-slateink">Riders from the DACH region</h3>
            <p class="mt-1 flex items-center gap-2 text-sm text-slate-500">
              <span class="inline-flex text-amber-500">
                <Star v-for="n in 5" :key="n" class="h-4 w-4 fill-current" />
              </span>
              4.8 average · 1,240 verified rentals
            </p>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
          <blockquote
            v-for="review in reviews"
            :key="review.name"
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
          >
            <div class="mb-3 flex text-amber-500">
              <Star v-for="n in review.rating" :key="n" class="h-4 w-4 fill-current" />
            </div>
            <p class="text-sm leading-relaxed text-slate-700">“{{ review.text }}”</p>
            <footer class="mt-4 text-sm font-bold text-slateink">
              {{ review.name }}
              <span class="font-medium text-slate-500">· {{ review.city }}</span>
            </footer>
          </blockquote>
        </div>
      </div>
    </div>
  </section>
</template>
