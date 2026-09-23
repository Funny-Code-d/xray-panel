<script setup>
import ServerUnit from './ServerUnit.vue'

defineProps({
  servers: { type: Array, required: true },
})
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <ServerUnit
      v-for="(server, index) in servers"
      :key="server.id"
      :server="server"
      class="animate-list-item"
      :style="{ animationDelay: `${Math.min(index, 10) * 50}ms` }"
    />
  </div>
</template>