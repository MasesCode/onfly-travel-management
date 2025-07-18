<script setup lang="ts">
import { onMounted, ref } from 'vue'

const model = defineModel<string>({ required: true })

const input = ref<HTMLInputElement | null>(null)

onMounted(() => {
  if (input.value?.hasAttribute('autofocus')) {
    input.value?.focus()
  }
})

defineExpose({ focus: () => input.value?.focus() })
</script>

<template>
  <input
    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black placeholder-gray-400 bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:outline-none transition duration-150 ease-in-out disabled:bg-gray-50 disabled:text-gray-500"
    v-model="model"
    ref="input"
  />
</template>
