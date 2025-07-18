<script setup lang="ts">
import OnflyLogo from '@/components/OnflyLogo.vue'
</script>

<template>
  <div class="min-h-screen bg-blue-500 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
      <div class="flex justify-center mb-8">
        <OnflyLogo />
      </div>
      <slot />
    </div>
  </div>
</template>