import { computed, reactive } from 'vue'
import { createEquipment, updateEquipment } from '../../api/equipment.js'
import {
  IMAGE_MIME_TYPES,
  MAX_IMAGE_BYTES,
  MAX_SPECS,
} from '../equipmentConstants.js'

function paddedList(values) {
  return values?.length ? [...values] : ['']
}

function uniqueTrimmed(values) {
  return [...new Set(values.map((value) => value.trim()).filter(Boolean))]
}

function blankForm(rentalPointId = null) {
  return {
    rentalPointId,
    name: '',
    category: 'ski',
    categoryLabel: '',
    badge: '',
    badgeTone: 'new',
    image: '',
    specs: [''],
    dailyRate: 0,
    halfDayRate: '',
    deposit: 0,
    sizes: [''],
    availableNow: true,
    availableDates: [''],
  }
}

function formFromItem(item) {
  return {
    rentalPointId: item.rentalPointId ?? null,
    name: item.name ?? '',
    category: item.category ?? 'ski',
    categoryLabel: item.categoryLabel ?? '',
    badge: item.badge ?? '',
    badgeTone: item.badgeTone ?? 'new',
    image: item.image ?? '',
    specs: paddedList(item.specs),
    dailyRate: item.dailyRate ?? 0,
    halfDayRate: item.halfDayRate ?? '',
    deposit: item.deposit ?? 0,
    sizes: paddedList(item.sizes),
    availableNow: Boolean(item.availableNow),
    availableDates: paddedList(item.availableDates),
  }
}

function revokePreview(url) {
  if (url.startsWith('blob:')) {
    URL.revokeObjectURL(url)
  }
}

export function useEquipmentForm({ onCreated, onUpdated } = {}) {
  const editor = reactive({
    isOpen: false,
    mode: 'create',
    editingId: null,
    form: blankForm(),
    imagePreview: '',
    imageFile: null,
    formError: '',
    isSaving: false,
  })

  const isCreate = computed(() => editor.mode === 'create')
  const title = computed(() => (isCreate.value ? 'Add equipment' : 'Edit equipment'))
  const submitLabel = computed(() => {
    if (editor.isSaving) {
      return 'Saving…'
    }

    return isCreate.value ? 'Add equipment' : 'Save changes'
  })

  function resetImage() {
    revokePreview(editor.imagePreview)
    editor.imagePreview = ''
    editor.imageFile = null
  }

  function reset() {
    resetImage()
    editor.mode = 'create'
    editor.editingId = null
    editor.form = blankForm()
    editor.formError = ''
  }

  function openCreate(rentalPointId = null) {
    reset()
    editor.form.rentalPointId = rentalPointId
    editor.isOpen = true
  }

  function openEdit(item) {
    resetImage()
    editor.mode = 'edit'
    editor.editingId = item.id
    editor.form = formFromItem(item)
    editor.imagePreview = item.image ?? ''
    editor.formError = ''
    editor.isOpen = true
  }

  function close() {
    editor.isOpen = false
    reset()
  }

  function handleImageUpload(event) {
    const file = event.target.files?.[0]
    if (!file) {
      return
    }

    if (!IMAGE_MIME_TYPES.includes(file.type)) {
      editor.formError = 'Choose a JPG, PNG, or WEBP image.'
      event.target.value = ''
      return
    }

    if (file.size > MAX_IMAGE_BYTES) {
      editor.formError = 'Image must be 5 MB or smaller.'
      event.target.value = ''
      return
    }

    editor.formError = ''
    editor.imageFile = file
    editor.form.image = ''
    revokePreview(editor.imagePreview)
    editor.imagePreview = URL.createObjectURL(file)
  }

  function handleImageUrlInput() {
    editor.imageFile = null
    revokePreview(editor.imagePreview)
    editor.imagePreview = editor.form.image
  }

  function validate() {
    if (!editor.form.rentalPointId) {
      return 'Choose the rental point where this equipment is available.'
    }

    if (!editor.form.name.trim()) {
      return 'Enter an equipment name.'
    }

    const hasImage = Boolean(editor.imageFile || editor.form.image.trim())
    if (!hasImage) {
      return 'Add an image file or image URL.'
    }

    if (uniqueTrimmed(editor.form.specs).length > MAX_SPECS) {
      return 'Add up to 3 short details.'
    }

    return ''
  }

  function buildFormData() {
    const formData = new FormData()
    const fields = {
      rentalPointId: String(editor.form.rentalPointId),
      name: editor.form.name.trim(),
      category: editor.form.category,
      categoryLabel: editor.form.categoryLabel.trim(),
      badge: editor.form.badge.trim(),
      badgeTone: editor.form.badgeTone,
      dailyRate: String(editor.form.dailyRate),
      // Empty = no half-day rental for this item
      halfDayRate: editor.form.halfDayRate === '' || editor.form.halfDayRate === null ? '' : String(editor.form.halfDayRate),
      deposit: String(editor.form.deposit),
      availableNow: editor.form.availableNow ? '1' : '0',
    }

    Object.entries(fields).forEach(([key, value]) => formData.append(key, value))

    for (const [field, values] of Object.entries({
      specs: editor.form.specs,
      sizes: editor.form.sizes,
      availableDates: editor.form.availableDates,
    })) {
      uniqueTrimmed(values).forEach((value) => formData.append(`${field}[]`, value))
    }

    if (editor.imageFile) {
      formData.append('imageFile', editor.imageFile)
    } else if (editor.form.image.trim()) {
      formData.append('image', editor.form.image.trim())
    }

    return formData
  }

  async function submit() {
    const error = validate()
    if (error) {
      editor.formError = error
      return
    }

    editor.isSaving = true
    editor.formError = ''

    try {
      if (editor.mode === 'edit') {
        const formData = buildFormData()
        formData.append('_method', 'PUT')
        const equipment = await updateEquipment(editor.editingId, formData)
        onUpdated?.(equipment)
        close()
        return
      }

      const equipment = await createEquipment(buildFormData())
      onCreated?.(equipment)
      close()
    } catch (error) {
      editor.formError = error.message || 'Could not save equipment.'
    } finally {
      editor.isSaving = false
    }
  }

  return {
    editor,
    isCreate,
    title,
    submitLabel,
    openCreate,
    openEdit,
    close,
    handleImageUpload,
    handleImageUrlInput,
    submit,
  }
}
