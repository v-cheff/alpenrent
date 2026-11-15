<script setup>
const image = defineModel('image', { type: String, required: true })
const preview = defineModel('preview', { type: String, required: true })

defineEmits(['upload', 'url-input'])
</script>

<template>
  <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 lg:col-span-2">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <p class="text-sm font-medium text-slate-700">Equipment image</p>
        <p class="text-xs text-slate-500">JPG, PNG, or WEBP; max 5 MB</p>
      </div>

      <label class="cursor-pointer rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">
        Upload image
        <input
          type="file"
          accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
          class="hidden"
          @change="$emit('upload', $event)"
        />
      </label>
    </div>

    <label class="mt-4 block">
      <span class="mb-2 block text-sm font-medium text-slate-700">Image URL</span>
      <input
        v-model="image"
        type="text"
        placeholder="https://..."
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
        @input="$emit('url-input')"
      />
    </label>

    <div v-if="preview || image" class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
      <img :src="preview || image" alt="Preview" class="h-52 w-full object-cover" />
    </div>
  </div>
</template>
