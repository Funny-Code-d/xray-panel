<script setup>
import { watch } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  open: { type: Boolean, default: false },
  links: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])

const route = useRoute()

function close() {
  emit('close')
}

// Блокируем скролл body, когда меню открыто
watch(() => props.open, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})
</script>

<template>
  <!-- Оверлей -->
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0"
    leave-active-class="transition-opacity duration-200"
    leave-to-class="opacity-0"
  >
    <div
      v-if="open"
      class="fixed inset-0 z-40 bg-black/60"
      @click="close"
    ></div>
  </Transition>

  <!-- Сайдбар -->
  <aside
    class="fixed top-0 left-0 z-50 h-screen w-[280px] bg-[#FFD700] border-r-[3px] border-black dark:border-white transition-transform duration-300 ease-out flex flex-col"
    :class="open ? 'translate-x-0' : '-translate-x-full'"
  >
    <!-- Шапка сайдбара -->
    <div class="flex items-center justify-between p-4 border-b-[3px] border-black dark:border-white shrink-0">
      <RouterLink to="/" class="flex items-center gap-3" @click="close">
        <img src="/logo-icon.svg" alt="FunnyNodes" class="h-8 w-8" />
        <span class="text-lg font-black uppercase tracking-wider">FunnyNodes</span>
      </RouterLink>

      <!-- Кнопка-бургер с анимацией -->
      <button
        type="button"
        class="burger-btn"
        :class="{ 'is-open': open }"
        aria-label="Закрыть меню"
        @click="close"
      >
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>

    <!-- Пункты меню -->
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        <RouterLink
            v-for="link in links"
            :key="link.name"
            :to="{ name: link.name }"
            class="flex items-center gap-3 px-4 py-3 border-[3px] border-black dark:border-white font-black uppercase tracking-wider text-sm"
            :class="
                route.name === link.name
                ? 'bg-black text-white'
                : 'bg-white text-black hover:bg-[#FFD700] hover:text-black menu-tilt'
            "
            @click="close"
            >
            <span>{{ link.label }}</span>
            </RouterLink>
    </nav>

    <!-- Футер сайдбара -->
    <div class="p-4 border-t-[3px] border-black dark:border-white shrink-0">
      <a
        href="https://github.com/Funny-Code-d/xray-panel"
        target="_blank"
        rel="noopener"
        class="flex items-center justify-center gap-2 text-xs font-black uppercase tracking-wider opacity-70 hover:opacity-100"
      >
        GitHub · MIT License
      </a>
    </div>
  </aside>
</template>

<style scoped>
/* === Анимированный бургер === */
.burger-btn {
  position: relative;
  width: 40px;
  height: 40px;
  padding: 0;
  background: #fff;
  border: 3px solid #000;
  cursor: pointer;
  transition: background-color 0.2s;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 5px;
}

.burger-btn:hover {
  background: #000;
}

.burger-btn span {
  display: block;
  width: 20px;
  height: 3px;
  background: #000;
  transition: transform 0.3s ease, opacity 0.2s ease, background-color 0.2s;
  transform-origin: center;
}

.burger-btn:hover span {
  background: #fff;
}

/* Открытое состояние — крестик */
.burger-btn.is-open span:nth-child(1) {
  transform: translateY(8px) rotate(45deg);
}

.burger-btn.is-open span:nth-child(2) {
  opacity: 0;
}

.burger-btn.is-open span:nth-child(3) {
  transform: translateY(-8px) rotate(-45deg);
}

/* Тёмная тема */
:global(.dark) .burger-btn {
  background: #1a1a1a;
  border-color: #fff;
}

:global(.dark) .burger-btn span {
  background: #fff;
}

:global(.dark) .burger-btn:hover {
  background: #fff;
}

:global(.dark) .burger-btn:hover span {
  background: #000;
}
</style>