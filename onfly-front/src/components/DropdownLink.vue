<script setup lang="ts">
const props = defineProps<{
  to?: string
  as?: string
}>()

const emit = defineEmits<{
  click: []
}>()

const handleClick = () => {
  emit('click')
}
</script>

<template>
  <router-link
    v-if="to && as !== 'button'"
    :to="to"
    class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
  >
    <slot />
  </router-link>
  <button
    v-else
    class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
    @click="handleClick"
  >
    <slot />
  </button>
</template>
