<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import Button from '@/components/ui/Button.vue'
import ServerMiniCard from '@/components/server/ServerMiniCard.vue'
import FaqItem from '@/components/FaqItem.vue'

const faq = [
  {
    question: 'Это бесплатно?',
    answer: 'Панель FunnyNodes — да, полностью open-source под MIT License. Серверы для подключения предоставляются администратором по договорённости — обычно после одобрения заявки.',
  },
  {
    question: 'Какие VPN-клиенты поддерживаются?',
    answer: 'VLESS + Reality работает в OneXray, v2rayTun, v2rayNG, WINGS, Streisand, Hiddify и других клиентах с поддержкой Xray-core. После создания ключа вы получите QR-код и ссылку vless:// для импорта.',
  },
  {
    question: 'Чем VLESS + Reality лучше OpenVPN или WireGuard?',
    answer: 'VLESS + Reality маскирует трафик под обычный HTTPS к реальному сайту (например, dl.google.com) и устойчив к DPI-детектированию. OpenVPN и WireGuard имеют узнаваемые сигнатуры и легко блокируются в ряде стран.',
  },
  {
    question: 'Сколько устройств можно подключить?',
    answer: 'Количество устройств зависит от созданных ключей. Один ключ = одно устройство (или один клиент). Лимит ключей на аккаунт настраивается администратором.',
  },
  {
    question: 'Есть ли лимит трафика?',
    answer: 'Да, лимит настраивается администратором индивидуально. Статистика трафика отображается в личном кабинете, а при превышении лимита ключи автоматически деактивируются.',
  },
  {
    question: 'Сколько ждать одобрения заявки?',
    answer: 'Обычно несколько часов. Заявка рассматривается вручную администратором. До одобрения доступна только страница ожидания.',
  },
  {
    question: 'Это безопасно?',
    answer: 'Да. Вход по паролю на серверах отключён — только SSH-ключи Ed25519. UFW настроен на минимально необходимые порты. API Xray и агент доступны только с IP панели. Все токены — Sanctum, хранятся отдельно для каждого сервера.',
  },
  {
    question: 'Куда уходят мои данные?',
    answer: 'Никуда. FunnyNodes — self-hosted панель. Вся инфраструктура развёрнута на собственных серверах, данные не передаются третьим лицам.',
  },
]

const stack = [
  'Laravel 12',
  'PHP 8.3',
  'Vue 3',
  'Vite',
  'Pinia',
  'Tailwind v4',
  'MySQL / MariaDB',
  'Laravel Sanctum',
  'Xray-core',
  'VLESS + Reality',
  'Docker',
  'Nginx',
  'Let\'s Encrypt',
  'GitHub Actions',
  'MIT License',
]

const roadmap = [
  {
    title: 'Xray-интеграция',
    color: 'bg-[#FFD700]',
    items: [
      'gRPC-дельты (AddUser / RemoveUser) без перезапуска',
      'Автодеактивация ключей при превышении лимита',
      'Сброс лимита трафика по расписанию',
      'Учёт онлайн-сессий и аптайма',
      'Node Agent — heartbeat и метрики с узлов',
    ],
  },
  {
    title: 'Коммуникация',
    color: 'bg-[#FF4911] text-white',
    items: [
      'Рассылка при публикации постов',
      'Уведомления в Telegram',
      'Telegram-бот для дублирования новостей',
      'Событийные уведомления (одобрение, блокировка)',
    ],
  },
  {
    title: 'Безопасность',
    color: 'bg-[#00F0FF]',
    items: [
      'Двухфакторная аутентификация (2FA)',
      'Логи действий администраторов',
      'fail2ban — защита от SSH brute-force',
      'Rate limiting на чувствительные endpoint\'ы',
    ],
  },
  {
    title: 'UI / UX',
    color: 'bg-[#FF00FF] text-white',
    items: [
      'OG-image для соцсетей',
      'Графики трафика (дни / недели / месяцы)',
      'Экспорт данных (CSV, JSON)',
      'Скриншоты интерфейса на лендинге',
    ],
  },
]

const router = useRouter()

const servers = ref([])
const loadingServers = ref(true)

async function fetchServers() {
  try {
    const { data } = await api.get('/servers/public')
    servers.value = data
  } catch (e) {
    console.error(e)
  } finally {
    loadingServers.value = false
  }
}

onMounted(fetchServers)
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-[#121212]">
    <!-- Шапка -->
    <header class="border-b-[3px] border-black dark:border-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
          <img src="/logo-icon.svg" alt="FunnyNodes" class="h-9 w-9" />
          <span class="text-xl font-black uppercase tracking-wider">FunnyNodes</span>
        </div>
        <div class="flex gap-2">
          <Button variant="secondary" size="sm" @click="router.push({ name: 'login' })">
            Войти
          </Button>
          <Button variant="primary" size="sm" @click="router.push({ name: 'register' })">
            Регистрация
          </Button>
        </div>
      </div>
    </header>

    <!-- Hero -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
      <div class="text-center max-w-3xl mx-auto">
        <div class="inline-block px-3 py-1 mb-6 bg-[#FFD700] border-[3px] border-black dark:border-white shadow-brutal-sm">
          <span class="text-xs font-black uppercase tracking-wider">Открытый код</span>
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black uppercase tracking-wider mb-6 leading-tight">
          Управление VPN<br />
          <span class="text-[#FF4911] dark:text-[#FF00FF]">без лишней боли</span>
        </h1>

        <p class="text-lg opacity-70 mb-8 max-w-2xl mx-auto">
          FunnyNodes — открытая панель для управления Xray-серверами.
          Личный кабинет, QR-коды для подключения, статистика трафика
          и мульти-сервер — всё в одном месте.
        </p>

        <div class="flex flex-wrap gap-4 justify-center">
          <Button variant="primary" size="lg" @click="router.push({ name: 'register' })">
            Создать аккаунт
          </Button>
          <Button variant="secondary" size="lg" @click="router.push({ name: 'login' })">
            Войти
          </Button>
        </div>
      </div>
    </section>

    <!-- Что это -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="bg-[#FFD700] border-[3px] border-black dark:border-white shadow-brutal-lg p-8 lg:p-12">
        <h2 class="text-3xl font-black uppercase tracking-wider mb-6">
          Что это
        </h2>
        <p class="text-lg opacity-80 leading-relaxed max-w-3xl">
          FunnyNodes — self-hosted панель для централизованного управления
          VPN-инфраструктурой на базе <b>Xray-core</b>. Вместо ручного
          редактирования конфигов, запуска команд через SSH и общения
          в мессенджерах — единый веб-интерфейс с личным кабинетом
          и админ-панелью.
        </p>
      </div>
    </section>

    <!-- Возможности -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h2 class="text-3xl font-black uppercase tracking-wider mb-8 text-center">
        Возможности
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Личный кабинет -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="w-12 h-12 flex items-center justify-center bg-[#FFD700] border-[3px] border-black dark:border-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </div>
          <h3 class="font-black uppercase tracking-wider mb-2">Личный кабинет</h3>
          <p class="text-sm opacity-70">Создание ключей, QR-код для подключения, статистика трафика.</p>
        </div>

        <!-- Мульти-сервер -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="w-12 h-12 flex items-center justify-center bg-[#FF4911] text-white border-[3px] border-black dark:border-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <rect x="2" y="2" width="20" height="8" rx="2"/>
              <rect x="2" y="14" width="20" height="8" rx="2"/>
              <line x1="6" y1="6" x2="6.01" y2="6"/>
              <line x1="6" y1="18" x2="6.01" y2="18"/>
            </svg>
          </div>
          <h3 class="font-black uppercase tracking-wider mb-2">Мульти-сервер</h3>
          <p class="text-sm opacity-70">Управление несколькими Xray-узлами из одной панели.</p>
        </div>

        <!-- Безопасность -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="w-12 h-12 flex items-center justify-center bg-[#00F0FF] border-[3px] border-black dark:border-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
          </div>
          <h3 class="font-black uppercase tracking-wider mb-2">VLESS + Reality</h3>
          <p class="text-sm opacity-70">Современный протокол, устойчивый к детектированию.</p>
        </div>

        <!-- Статистика -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="w-12 h-12 flex items-center justify-center bg-[#00FF00] border-[3px] border-black dark:border-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <line x1="18" y1="20" x2="18" y2="10"/>
              <line x1="12" y1="20" x2="12" y2="4"/>
              <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
          </div>
          <h3 class="font-black uppercase tracking-wider mb-2">Статистика</h3>
          <p class="text-sm opacity-70">Трафик по дням, лимиты, автодеактивация ключей.</p>
        </div>

        <!-- Открытый код -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="w-12 h-12 flex items-center justify-center bg-[#FF00FF] text-white border-[3px] border-black dark:border-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <polyline points="16 18 22 12 16 6"/>
              <polyline points="8 6 2 12 8 18"/>
            </svg>
          </div>
          <h3 class="font-black uppercase tracking-wider mb-2">Открытый код</h3>
          <p class="text-sm opacity-70">Полностью открытый проект на GitHub. MIT License.</p>
        </div>

        <!-- Новости -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="w-12 h-12 flex items-center justify-center bg-[#FFD700] border-[3px] border-black dark:border-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
              <line x1="18" y1="14" x2="12" y2="14"/>
              <line x1="18" y1="18" x2="12" y2="18"/>
              <line x1="18" y1="10" x2="12" y2="10"/>
            </svg>
          </div>
          <h3 class="font-black uppercase tracking-wider mb-2">Лента новостей</h3>
          <p class="text-sm opacity-70">Обновления, инструкции и важные объявления.</p>
        </div>
      </div>
    </section>

    <!-- Серверы -->
    <section v-if="servers.length > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h2 class="text-3xl font-black uppercase tracking-wider mb-8 text-center">
        Доступные серверы
      </h2>

      <div v-if="loadingServers" class="text-center py-12 opacity-70">
        Загрузка...
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
        <ServerMiniCard
          v-for="server in servers"
          :key="server.id"
          :server="server"
        />
      </div>
    </section>

    <!-- Как подключиться -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h2 class="text-3xl font-black uppercase tracking-wider mb-8 text-center">
        Как подключиться
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="text-5xl font-black text-[#FFD700] mb-4">01</div>
          <h3 class="font-black uppercase tracking-wider mb-2">Регистрация</h3>
          <p class="text-sm opacity-70">Создайте аккаунт на сайте. Заявка уйдёт на одобрение.</p>
        </div>

        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="text-5xl font-black text-[#FFD700] mb-4">02</div>
          <h3 class="font-black uppercase tracking-wider mb-2">Одобрение</h3>
          <p class="text-sm opacity-70">Админ одобрит заявку. Обычно это занимает несколько часов.</p>
        </div>

        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="text-5xl font-black text-[#FFD700] mb-4">03</div>
          <h3 class="font-black uppercase tracking-wider mb-2">Ключ</h3>
          <p class="text-sm opacity-70">Создайте ключ в личном кабинете. Выберите сервер.</p>
        </div>

        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="text-5xl font-black text-[#FFD700] mb-4">04</div>
          <h3 class="font-black uppercase tracking-wider mb-2">Подключение</h3>
          <p class="text-sm opacity-70">Импортируйте QR в VPN-клиент и подключайтесь.</p>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h2 class="text-3xl font-black uppercase tracking-wider mb-8 text-center">
        Частые вопросы
    </h2>

    <div class="max-w-3xl mx-auto space-y-4">
        <FaqItem
        v-for="(item, i) in faq"
        :key="i"
        :question="item.question"
        :answer="item.answer"
        :default-open="i === 0"
        />
    </div>
    </section>


    <!-- Стек -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h2 class="text-3xl font-black uppercase tracking-wider mb-8 text-center">
        Под капотом
    </h2>

    <div class="flex flex-wrap gap-3 justify-center max-w-3xl mx-auto">
        <span
        v-for="tech in stack"
        :key="tech"
        class="px-4 py-2 bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal-sm font-mono text-sm font-bold uppercase tracking-wider"
        >
        {{ tech }}
        </span>
    </div>
    </section>

    <!-- Roadmap -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h2 class="text-3xl font-black uppercase tracking-wider mb-8 text-center">
        Что дальше
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
        <div
        v-for="(group, i) in roadmap"
        :key="i"
        class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6"
        >
        <div class="flex items-center gap-3 mb-4">
            <div
            class="w-10 h-10 flex items-center justify-center border-[3px] border-black dark:border-white font-black"
            :class="group.color"
            >
            {{ String(i + 1).padStart(2, '0') }}
            </div>
            <h3 class="font-black uppercase tracking-wider">{{ group.title }}</h3>
        </div>

        <ul class="space-y-2">
            <li
            v-for="(item, j) in group.items"
            :key="j"
            class="flex items-start gap-2 text-sm opacity-70"
            >
            <span class="mt-1.5 w-1.5 h-1.5 bg-black dark:bg-white shrink-0"></span>
            <span>{{ item }}</span>
            </li>
        </ul>
        </div>
    </div>
    </section>

    <!-- Контакты -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal-lg p-8 lg:p-12 text-center max-w-3xl mx-auto">
        <h2 class="text-3xl font-black uppercase tracking-wider mb-4">
          Нужна помощь?
        </h2>
        <p class="text-lg opacity-70 mb-8">
          Пишите напрямую — отвечаю в течение дня.
        </p>

        <div class="flex flex-wrap gap-4 justify-center">
          <a
            href="https://t.me/sosnin_451"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2 px-6 py-3 border-[3px] border-black dark:border-white font-black uppercase tracking-wide hover:shadow-brutal-hover transition-all"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
            </svg>
            Telegram
          </a>

          <a
            href="mailto:sosnin.dienis.2000@gmail.com"
            class="inline-flex items-center gap-2 px-6 py-3 border-[3px] border-black dark:border-white font-black uppercase tracking-wide hover:shadow-brutal-hover transition-all"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
            Email
          </a>
        </div>
      </div>
    </section>

    <!-- Поддержать -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
      <div class="bg-[#FFD700] border-[3px] border-black dark:border-white shadow-brutal-lg p-8 lg:p-12 text-center max-w-3xl mx-auto">
        <h2 class="text-3xl font-black uppercase tracking-wider mb-4">
          Поддержать проект
        </h2>
        <p class="text-lg opacity-80 mb-8">
          Проект развивается в свободное время. Если хотите помочь —
          можно сделать добровольное пожертвование. Средства идут на серверы и развитие.
        </p>

        <div class="flex justify-center">
            <iframe
                src="https://yoomoney.ru/quickpay/fundraise/widget?billNumber=1KBU1HKLTL1.260917&"
                width="500"
                height="480"
                frameborder="0"
                allowtransparency="true"
                scrolling="no"
            ></iframe>
        </div>
      </div>
    </section>

    <!-- Футер -->
    <footer class="border-t-[3px] border-black dark:border-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center">
        <p class="text-sm opacity-60 mb-2">
          FunnyNodes — открытая панель управления Xray-серверами
        </p>
        <p class="text-xs opacity-40">
          MIT License ·
          <a href="https://github.com/Funny-Code-d/xray-panel" target="_blank" rel="noopener" class="hover:opacity-100">
            GitHub
          </a>
        </p>
      </div>
    </footer>
  </div>
</template>