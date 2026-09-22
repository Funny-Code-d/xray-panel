<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const githubUrl = 'https://github.com/Funny-Code-d/xray-panel'

function goBack() {
  if (auth.isAuthenticated) {
    router.push({ name: 'dashboard' })
  } else {
    router.push({ name: 'login' })
  }
}
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-[#1a0b2e] px-4 py-12">
    <!-- Иконка GitFlic в углу -->
    <a
      :href="githubUrl"
      target="_blank"
      rel="noopener noreferrer"
      class="fixed top-4 right-4 z-10 w-10 h-10 flex items-center justify-center border-2 border-black dark:border-white bg-white dark:bg-[#2a1548] hover:shadow-brutal-sm transition-all"
      title="Исходный код"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
      </svg>
    </a>

    <div class="w-full max-w-2xl mx-auto space-y-6">
      <!-- Кнопка назад -->
      <button
        @click="goBack"
        class="text-sm font-bold uppercase tracking-wide opacity-70 hover:opacity-100 flex items-center gap-2"
      >
        ← Назад
      </button>

      <!-- Заголовок -->
      <div>
        <h1 class="text-4xl font-black uppercase tracking-wider">FunnyNodes</h1>
        <p class="mt-2 text-sm opacity-70">Открытая панель управления</p>
      </div>

      <!-- Что это -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-6">
        <h2 class="text-lg font-black uppercase tracking-wider mb-3">
          Что это
        </h2>
        <p class="text-sm opacity-80 leading-relaxed mb-3">
          Aegis — открытая веб-панель для централизованного управления
          сервером и клиентами. Единый личный кабинет и админ-панель
          вместо ручной правки конфигов и общения в мессенджерах.
        </p>
        <p class="text-sm opacity-80 leading-relaxed">
          Панель работает поверх Xray-core и поддерживает современный
          протокол VLESS + Reality, устойчивый к детектированию.
        </p>
      </div>

      <!-- Возможности -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-6">
        <h2 class="text-lg font-black uppercase tracking-wider mb-4">
          Возможности
        </h2>
        <ul class="space-y-3 text-sm opacity-80">
          <li class="flex gap-3">
            <span class="text-blue-600 dark:text-orange-500 font-bold shrink-0">01</span>
            <span><b>Личный кабинет</b> — создание ключей, QR-код для подключения, статистика трафика</span>
          </li>
          <li class="flex gap-3">
            <span class="text-blue-600 dark:text-orange-500 font-bold shrink-0">02</span>
            <span><b>Админ-панель</b> — модерация заявок, роли, блокировки, лимиты</span>
          </li>
          <li class="flex gap-3">
            <span class="text-blue-600 dark:text-orange-500 font-bold shrink-0">03</span>
            <span><b>Мульти-сервер</b> — управление несколькими узлами из одной панели</span>
          </li>
          <li class="flex gap-3">
            <span class="text-blue-600 dark:text-orange-500 font-bold shrink-0">04</span>
            <span><b>Статистика</b> — сбор трафика с серверов, разбивка по дням</span>
          </li>
          <li class="flex gap-3">
            <span class="text-blue-600 dark:text-orange-500 font-bold shrink-0">05</span>
            <span><b>Безопасность</b> — Sanctum-токены, модерация, блокировки</span>
          </li>
        </ul>
      </div>

      <!-- Стек -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-6">
        <h2 class="text-lg font-black uppercase tracking-wider mb-4">
          Технологии
        </h2>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div>
            <p class="text-xs opacity-60 uppercase tracking-wider mb-2">Backend</p>
            <ul class="space-y-1 opacity-80">
              <li>Laravel 12</li>
              <li>PHP 8.3+</li>
              <li>MySQL / MariaDB</li>
              <li>Laravel Sanctum</li>
            </ul>
          </div>
          <div>
            <p class="text-xs opacity-60 uppercase tracking-wider mb-2">Frontend</p>
            <ul class="space-y-1 opacity-80">
              <li>Vue 3</li>
              <li>Vite</li>
              <li>Pinia</li>
              <li>Tailwind CSS</li>
            </ul>
          </div>
          <div>
            <p class="text-xs opacity-60 uppercase tracking-wider mb-2">Инфраструктура</p>
            <ul class="space-y-1 opacity-80">
              <li>Xray-core</li>
              <li>Docker</li>
              <li>Nginx</li>
              <li>Let's Encrypt</li>
            </ul>
          </div>
          <div>
            <p class="text-xs opacity-60 uppercase tracking-wider mb-2">Протоколы</p>
            <ul class="space-y-1 opacity-80">
              <li>VLESS + Reality</li>
              <li>VMess (legacy)</li>
              <li>WebSocket</li>
              <li>TLS</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Открытый код -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-6">
        <h2 class="text-lg font-black uppercase tracking-wider mb-3">
          Открытый код
        </h2>
        <p class="text-sm opacity-80 leading-relaxed mb-4">
          Проект полностью открытый. Исходный код доступен на GitFlic —
          вы можете изучить, форкнуть или предложить улучшения.
        </p>

        <a
          :href="githubUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-[#1a0b2e] text-black dark:text-white border-2 border-black dark:border-white font-bold uppercase tracking-wide text-sm hover:shadow-brutal-hover transition-all"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
          </svg>
          Открыть исходный код
        </a>
      </div>

      <!-- Поддержать проект -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-6">
        <h2 class="text-lg font-black uppercase tracking-wider mb-3">
          Поддержать проект
        </h2>
        <p class="text-sm opacity-80 leading-relaxed mb-5">
          Проект развивается в свободное время. Если хотите помочь —
          можно поддержать добровольным пожертвованием. Средства идут
          на серверы и развитие.
        </p>

        <div class="flex justify-center">
          <iframe
            src="https://yoomoney.ru/quickpay/fundraise/button?billNumber=1KBU1HKLTL1.260917&"
            width="330"
            height="50"
            frameborder="0"
            allowtransparency="true"
            scrolling="no"
          ></iframe>
        </div>

        <p class="text-center text-xs opacity-50 mt-4">
          Спасибо за поддержку ❤️
        </p>
      </div>
    </div>
  </div>
</template>