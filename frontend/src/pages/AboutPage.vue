<script setup>
import AppLayout from '@/components/AppLayout.vue'
import Card from '@/components/ui/Card.vue'

const githubUrl = 'https://github.com/Funny-Code-d/xray-panel'

const features = [
  { num: '01', title: 'Личный кабинет', text: 'Создание ключей, QR-код для подключения, статистика трафика.' },
  { num: '02', title: 'Админ-панель', text: 'Модерация заявок, роли, блокировки, лимиты.' },
  { num: '03', title: 'Мульти-сервер', text: 'Управление несколькими Xray-узлами из одной панели.' },
  { num: '04', title: 'Статистика', text: 'Сбор трафика с серверов, разбивка по дням.' },
  { num: '05', title: 'Безопасность', text: 'Sanctum-токены, модерация, блокировки, SSH-ключи.' },
  { num: '06', title: 'Открытый код', text: 'MIT License, исходники на GitHub.' },
]

const stack = [
  { group: 'Backend', items: ['Laravel 12', 'PHP 8.3+', 'MySQL / MariaDB', 'Laravel Sanctum'] },
  { group: 'Frontend', items: ['Vue 3', 'Vite', 'Pinia', 'Tailwind CSS'] },
  { group: 'Infrastructure', items: ['Xray-core', 'Docker', 'Nginx', "Let's Encrypt"] },
  { group: 'Protocols', items: ['VLESS + Reality', 'VMess (legacy)', 'WebSocket', 'TLS'] },
]
</script>

<template>
  <AppLayout>
    <!-- Заголовок -->
    <div class="mb-8">
      <h1 class="text-5xl sm:text-6xl font-black uppercase tracking-wider leading-none">
        About
      </h1>
      <p class="mt-3 text-base opacity-70">
        Открытая панель для управления Xray-серверами
      </p>
    </div>

    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Логотип -->
      <div class="flex justify-center">
        <div class="bg-[#FFD700] border-[3px] border-black dark:border-white shadow-brutal-lg p-6 max-w-sm w-full">
          <img
            src="/logo-full.jpg"
            alt="FunnyNodes"
            class="w-full"
          />
        </div>
      </div>

      <!-- Что это -->
      <Card dense accent="yellow">
        <h2 class="text-2xl font-black uppercase tracking-wider mb-3">What is it</h2>
        <div class="space-y-3 text-base opacity-80 leading-relaxed">
          <p>
            FunnyNodes — открытая веб-панель для управления Xray-серверами
            и клиентами. Единый личный кабинет и админ-панель вместо ручной
            правки конфигов и общения в мессенджерах.
          </p>
          <p>
            Работает поверх Xray-core с современным протоколом
            <b>VLESS + Reality</b>, устойчивым к детектированию.
          </p>
        </div>
      </Card>

      <!-- Возможности -->
      <Card dense accent="black">
        <h2 class="text-2xl font-black uppercase tracking-wider mb-4">Features</h2>
        <ul class="space-y-3">
          <li
            v-for="f in features"
            :key="f.num"
            class="flex gap-3 text-sm"
          >
            <span class="font-black text-[#FF4911] dark:text-[#FF00FF] shrink-0 w-7">
              {{ f.num }}
            </span>
            <span class="opacity-80">
              <b>{{ f.title }}</b> — {{ f.text }}
            </span>
          </li>
        </ul>
      </Card>

      <!-- Технологии -->
      <Card dense accent="yellow">
        <h2 class="text-2xl font-black uppercase tracking-wider mb-4">Tech stack</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="s in stack" :key="s.group">
            <p class="text-xs font-black uppercase tracking-wider opacity-60 mb-2">
              {{ s.group }}
            </p>
            <ul class="space-y-1 text-sm opacity-80">
              <li v-for="item in s.items" :key="item">{{ item }}</li>
            </ul>
          </div>
        </div>
      </Card>

      <!-- Открытый код -->
      <Card dense accent="black">
        <h2 class="text-2xl font-black uppercase tracking-wider mb-3">Open source</h2>
        <p class="text-base opacity-80 leading-relaxed mb-5">
          Проект полностью открытый. Исходный код доступен на GitHub —
          можно изучить, форкнуть или предложить улучшения.
        </p>

        <a
          :href="githubUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-2 px-4 py-3 bg-black text-white border-[3px] border-black dark:border-white font-black uppercase tracking-wider text-xs hover:bg-[#FFD700] hover:text-black transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
          </svg>
          View on GitHub →
        </a>
      </Card>

      <!-- Поддержать -->
      <Card dense accent="yellow">
        <h2 class="text-2xl font-black uppercase tracking-wider mb-3">Support the project</h2>
        <p class="text-base opacity-80 leading-relaxed mb-5">
          Проект развивается в свободное время. Если хотите помочь —
          можно сделать добровольное пожертвование. Средства идут
          на серверы и развитие.
        </p>

        <div class="flex justify-center">
          <a
            href="https://yoomoney.ru/quickpay/fundraise/button?billNumber=1KBU1HKLTL1.260917&"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2 px-8 py-4 bg-[#FF4911] text-white border-[3px] border-black dark:border-white font-black uppercase tracking-wider shadow-brutal hover:shadow-brutal-hover transition-all"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
            Donate
          </a>
        </div>

        <p class="text-center text-xs opacity-50 mt-4">
          Thanks for your support ❤️
        </p>
      </Card>
    </div>
  </AppLayout>
</template>