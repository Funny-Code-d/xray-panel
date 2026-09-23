<script setup>
defineProps({
  variant: {
    type: String,
    default: 'primary', // primary | secondary | danger | ghost
  },
  size: {
    type: String,
    default: 'md', // sm | md | lg
  },
  loading: Boolean,
  disabled: Boolean,
})

const variants = {
  // Жёлтый — главное действие (Сохранить, Создать, Войти)
  primary: 'bg-[#FFD700] text-black border-2 border-black dark:border-white hover:shadow-brutal-hover shadow-brutal font-heading font-bold uppercase',

  // Белый — нейтральное действие (Редактировать, Отмена, Закрыть)
  secondary: 'bg-white dark:bg-[#1A1A1A] text-black dark:text-white border-2 border-black dark:border-white hover:shadow-brutal-hover shadow-brutal font-heading font-bold uppercase',

  // Оранжевый — инфо-действие (Посмотреть дашборд, Посмотреть)
  info: 'bg-[#00F0FF] text-black border-2 border-black dark:border-white hover:shadow-brutal-hover shadow-brutal font-heading font-bold uppercase',

  // Зелёный — позитивное действие (Одобрить, Разблокировать)
  success: 'bg-green-500 text-white border-2 border-black dark:border-white hover:shadow-brutal-hover shadow-brutal font-heading font-bold uppercase',

  // Красный — опасное действие (Удалить, Заблокировать, Отклонить)
  danger: 'bg-red-500 text-white border-2 border-black dark:border-white hover:shadow-brutal-hover shadow-brutal font-heading font-bold uppercase',

  // Прозрачный — второстепенное (Сбросить)
  ghost: 'bg-transparent text-black dark:text-white border-2 border-transparent hover:border-black dark:hover:border-white font-heading font-bold uppercase',
}

const sizes = {
  sm: 'px-3 py-1.5 text-sm',
  md: 'px-4 py-2 text-sm',
  lg: 'px-6 py-3 text-base',
}
</script>

<template>
  <button
    :disabled="disabled || loading"
    :class="[
        'inline-flex items-center justify-center font-bold uppercase tracking-wide',
        'transition-all duration-100',
        'active:translate-y-0.5 active:shadow-none',  // ← добавить
        'disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none',
        variants[variant],
        sizes[size],
    ]"
    >
    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
    </svg>
    <slot />
  </button>
</template>