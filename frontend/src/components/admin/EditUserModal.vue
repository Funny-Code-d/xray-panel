<script setup>
import { ref, watch } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps({
  modelValue: Boolean,
  user: Object,
  loading: Boolean,
})
const emit = defineEmits(['update:modelValue', 'save'])

// Лимит в ГБ (для отображения)
const limitGb = ref('')
// Выбранные роли (коды)
const selectedRoles = ref([])

watch(() => props.modelValue, (open) => {
  if (open && props.user) {
    // Байты → ГБ
    limitGb.value = props.user.traffic_limit
      ? (props.user.traffic_limit / 1073741824).toFixed(2)
      : ''
    // Коды ролей
    selectedRoles.value = props.user.roles?.map(r => r.code) || []
  }
})

function handleSave() {
  // ГБ → байты (или null, если пусто)
  const limitBytes = limitGb.value === '' || limitGb.value === null
    ? null
    : Math.round(parseFloat(limitGb.value) * 1073741824)

  emit('save', {
    traffic_limit: limitBytes,
    roles: selectedRoles.value,
  })
}
</script>

<template>
  <Modal
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    title="Редактировать пользователя"
  >
    <div class="space-y-5">
      <!-- Лимит трафика -->
      <div>
        <label class="block text-sm font-bold uppercase tracking-wide mb-2">
          Лимит трафика (ГБ)
        </label>
        <input
          v-model="limitGb"
          type="number"
          min="0"
          step="0.5"
          placeholder="Оставьте пустым для безлимита"
          class="w-full px-3 py-2 bg-white dark:bg-[#1a0b2e] text-black dark:text-white border-2 border-black dark:border-white focus:outline-none focus:shadow-brutal-sm transition-all"
        />
        <p class="mt-1 text-xs opacity-60">
          Пусто = безлимит. Можно дробные значения (например, 10.5).
        </p>
      </div>

      <!-- Роли -->
      <div>
        <label class="block text-sm font-bold uppercase tracking-wide mb-2">
          Роли
        </label>

        <div v-if="!user?.all_roles || user.all_roles.length === 0" class="text-sm opacity-60">
          Роли не загружены
        </div>

        <div v-else class="space-y-2">
          <label
            v-for="role in user.all_roles"
            :key="role.code"
            class="flex items-center gap-3 p-3 border-2 border-black dark:border-white cursor-pointer hover:shadow-brutal-sm transition-all"
          >
            <input
              type="checkbox"
              :value="role.code"
              v-model="selectedRoles"
              class="w-4 h-4 accent-blue-600 dark:accent-orange-500"
            />
            <div>
              <p class="font-bold text-sm">{{ role.name }}</p>
              <p class="text-xs font-mono opacity-60">{{ role.code }}</p>
            </div>
          </label>
        </div>
      </div>

      <!-- Кнопки -->
      <div class="flex gap-3 pt-2">
        <Button
          variant="secondary"
          class="flex-1"
          @click="$emit('update:modelValue', false)"
        >
          Отмена
        </Button>
        <Button
          variant="primary"
          :loading="loading"
          class="flex-1"
          @click="handleSave"
        >
          Сохранить
        </Button>
      </div>
    </div>
  </Modal>
</template>