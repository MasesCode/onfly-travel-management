<script setup lang="ts">
import OnflyLogo from '@/Components/OnflyLogo.vue';
</script>

<template>
    <div class="min-h-screen bg-blue-500 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-sm">
            <div class="flex justify-center mb-8">
                <OnflyLogo />
            </div>
            <slot />
        </div>
    </div>
</template>

<script lang="ts">
export default {
    name: 'GuestLayout'
};
</script>
