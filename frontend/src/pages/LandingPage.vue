<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import Button from '@/components/ui/Button.vue'
import ServerMiniCard from '@/components/server/ServerMiniCard.vue'
import FaqItem from '@/components/FaqItem.vue'

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

// === FAQ ===
const faq = [
  {
    question: 'Как подключиться?',
    answer: 'Зарегистрируйтесь, дождитесь одобрения заявки, создайте ключ в личном кабинете — и отсканируйте QR-код в приложении. Всё, можно подключаться.',
  },
  {
    question: 'Какие приложения поддерживаются?',
    answer: 'VLESS + Reality работает в OneXray, v2rayTun, v2rayNG, WINGS, Streisand, Hiddify и других клиентах с поддержкой Xray-core. Подходит для iOS, Android, Windows, macOS и Linux.',
  },
  {
    question: 'Это бесплатно?',
    answer: 'Да. FunnyNodes — открытый проект, серверы предоставляются администратором после одобрения заявки. Никаких скрытых платежей и рекламы.',
  },
  {
    question: 'Будет ли работать в моей сети?',
    answer: 'VLESS + Reality маскирует трафик под обычный HTTPS к реальному сайту, поэтому работает даже там, где другие VPN блокируются. Устойчив к DPI.',
  },
  {
    question: 'Сколько устройств можно подключить?',
    answer: 'Один ключ — одно устройство. Лимит ключей на аккаунт настраивается администратором. Для телефона, ноутбука и планшета можно создать отдельные ключи.',
  },
  {
    question: 'Есть ли лимит трафика?',
    answer: 'Да, лимит настраивается индивидуально. Статистику трафика видно в личном кабинете. При превышении лимита ключи автоматически отключаются.',
  },
  {
    question: 'Сколько ждать одобрения?',
    answer: 'Обычно несколько часов. Заявки рассматриваются вручную. До одобрения доступна только страница ожидания.',
  },
  {
    question: 'Что делать, если не работает?',
    answer: 'Напишите в Telegram или на почту — контакты ниже. Обычно отвечаю в течение дня. Опишите проблему и приложите скриншот, если возможно.',
  },
  {
    question: 'Куда уходят мои данные?',
    answer: 'Никуда. FunnyNodes — self-hosted проект. Вся инфраструктура на собственных серверах, данные не передаются третьим лицам.',
  },
  {
    question: 'Это легально?',
    answer: 'VPN для личного использования в России не запрещён. Сервис предоставляется «как есть» для доступа к открытым ресурсам.',
  },
]

// === Стек (для разработчиков) ===
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
  "Let's Encrypt",
  'GitHub Actions',
  'MIT License',
]

// === Roadmap (для разработчиков) ===
const roadmap = [
  {
    title: 'Поддержка протоколов',
    color: 'bg-[#00F0FF]',
    items: [
        'VMess — для legacy-клиентов',
        'Hysteria2 с обфускацией — для нестабильных сетей',
        'Shadowsocks 2022 — быстрый и простой',
    ],
  },
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
      'Email-рассылки при публикации постов',
      'Подписка на конкретные теги и авторов',
      'Уведомления в Telegram о новостях',
      'Telegram-бот для дублирования в канал',
      'Событийные уведомления (одобрение, блокировка)',
      'Push-уведомления в браузере',
    ],
  },
  {
    title: 'Мониторинг и статусы',
    color: 'bg-[#00FF00]',
    items: [
      'Публичная статус-страница (uptime, инциденты)',
      'История инцидентов и плановых работ',
      'Аптайм по каждому серверу за 30/90 дней',
      'Графики нагрузки (CPU, RAM, сеть)',
      'Уведомления при падении сервера',
      'Health-check агент на каждом узле',
    ],
  },
  {
    title: 'Безопасность',
    color: 'bg-[#FF00FF] text-white',
    items: [
      'Двухфакторная аутентификация (2FA)',
      'Логи действий администраторов',
      'fail2ban — защита от SSH brute-force',
      "Rate limiting на чувствительные endpoint'ы",
      'Аудит-лог входов и смены пароля',
    ],
  },
  {
    title: 'UI / UX',
    color: 'bg-[#FFD700]',
    items: [
      'OG-image для соцсетей',
      'Графики трафика (дни / недели / месяцы)',
      'Экспорт данных (CSV, JSON)',
      'Скриншоты интерфейса на лендинге',
      'Тёмная тема с автопереключением',
      'Мобильное приложение (PWA)',
    ],
  },
]
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
          VPN, который<br />
          <span class="text-[#FF4911] dark:text-[#FF00FF]">просто работает</span>
        </h1>

        <p class="text-lg opacity-70 mb-8 max-w-2xl mx-auto">
          Быстрое подключение в один клик. QR-код, импорт в приложение — и готово.
          Без конфигов, без настроек, без объяснений.
        </p>

        <div class="flex flex-wrap gap-4 justify-center">
          <Button variant="primary" size="lg" @click="router.push({ name: 'register' })">
            Создать аккаунт
          </Button>
          <Button variant="secondary" size="lg" @click="router.push({ name: 'login' })">
            Войти
          </Button>
        </div>

        <!-- Мини-факты -->
        <div class="mt-10 flex flex-wrap gap-x-8 gap-y-3 justify-center text-sm opacity-60">
          <span class="flex items-center gap-2">
            <span class="w-2 h-2 bg-[#00FF00] border border-black dark:border-white"></span>
            Работает во всех сетях
          </span>
          <span class="flex items-center gap-2">
            <span class="w-2 h-2 bg-[#00F0FF] border border-black dark:border-white"></span>
            iOS · Android · Windows · macOS · Linux
          </span>
          <span class="flex items-center gap-2">
            <span class="w-2 h-2 bg-[#FFD700] border border-black dark:border-white"></span>
            Бесплатно
          </span>
        </div>
      </div>
    </section>

    <!-- Что это -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="bg-[#FFD700] border-[3px] border-black dark:border-white shadow-brutal-lg p-8 lg:p-12">
        <h2 class="text-3xl font-black uppercase tracking-wider mb-6">
          Что это
        </h2>
        <div class="space-y-4 text-lg opacity-80 leading-relaxed max-w-3xl">
          <p>
            <b>Быстрый и стабильный VPN.</b> FunnyNodes — открытый сервис
            на базе Xray с современным протоколом VLESS + Reality.
            Устойчив к блокировкам, не тормозит, работает в любой сети.
          </p>
          <p>
            Подключение — через один QR-код. Сканируешь в приложении,
            нажимаешь «Подключить» — готово. Никаких конфигов, терминалов
            и инструкций на 20 шагов.
          </p>
        </div>
      </div>
    </section>

    <!-- Почему FunnyNodes -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h2 class="text-3xl font-black uppercase tracking-wider mb-8 text-center">
        Почему FunnyNodes
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Один клик -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="w-12 h-12 flex items-center justify-center bg-[#FFD700] border-[3px] border-black dark:border-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
          </div>
          <h3 class="font-black uppercase tracking-wider mb-2">Один клик</h3>
          <p class="text-sm opacity-70">QR-код или ссылка — импорт в приложение, и готово. Настройка занимает меньше минуты.</p>
        </div>

        <!-- Быстро и стабильно -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="w-12 h-12 flex items-center justify-center bg-[#FF4911] text-white border-[3px] border-black dark:border-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
            </svg>
          </div>
          <h3 class="font-black uppercase tracking-wider mb-2">Быстро и стабильно</h3>
          <p class="text-sm opacity-70">VLESS + Reality — современный протокол, устойчивый к блокировкам и DPI. Не тормозит, не отваливается.</p>
        </div>

        <!-- Работает везде -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="w-12 h-12 flex items-center justify-center bg-[#00F0FF] border-[3px] border-black dark:border-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <rect x="2" y="3" width="20" height="14" rx="2"/>
              <line x1="8" y1="21" x2="16" y2="21"/>
              <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
          </div>
          <h3 class="font-black uppercase tracking-wider mb-2">Работает везде</h3>
          <p class="text-sm opacity-70">iOS, Android, Windows, macOS, Linux. Любое устройство, любой клиент с поддержкой Xray.</p>
        </div>

        <!-- Серверы рядом -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="w-12 h-12 flex items-center justify-center bg-[#00FF00] border-[3px] border-black dark:border-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <circle cx="12" cy="12" r="10"/>
              <line x1="2" y1="12" x2="22" y2="12"/>
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
          </div>
          <h3 class="font-black uppercase tracking-wider mb-2">Серверы рядом</h3>
          <p class="text-sm opacity-70">Франция, Нидерланды и другие локации. Переключайся в один клик — выбирай, где быстрее.</p>
        </div>

        <!-- Личный кабинет -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="w-12 h-12 flex items-center justify-center bg-[#FF00FF] text-white border-[3px] border-black dark:border-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </div>
          <h3 class="font-black uppercase tracking-wider mb-2">Личный кабинет</h3>
          <p class="text-sm opacity-70">Все ключи, статистика трафика, лимиты — в одном месте. Создавай и удаляй ключи сам.</p>
        </div>

        <!-- Бесплатно и открыто -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <div class="w-12 h-12 flex items-center justify-center bg-[#FFD700] border-[3px] border-black dark:border-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <polyline points="16 18 22 12 16 6"/>
              <polyline points="8 6 2 12 8 18"/>
            </svg>
          </div>
          <h3 class="font-black uppercase tracking-wider mb-2">Бесплатно и открыто</h3>
          <p class="text-sm opacity-70">Открытый код, без рекламы и трекеров. Проект живёт на пожертвования, а не на продаже данных.</p>
        </div>
      </div>
    </section>

    <!-- Серверы -->
    <section
      v-if="loadingServers || servers.length > 0"
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16"
    >
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

    <!-- CTA -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="bg-[#FF4911] text-white border-[3px] border-black dark:border-white shadow-brutal-lg p-8 lg:p-12 text-center max-w-3xl mx-auto">
        <h2 class="text-3xl font-black uppercase tracking-wider mb-4">
          Готовы подключиться?
        </h2>
        <p class="text-lg opacity-90 mb-8">
          Регистрация занимает минуту. После одобрения — доступ ко всем
          серверам и QR-код для подключения.
        </p>
        <div class="flex flex-wrap gap-4 justify-center">
          <button
            type="button"
            class="inline-flex items-center justify-center px-8 py-3 bg-white text-black border-[3px] border-black font-black uppercase tracking-wide shadow-brutal hover:shadow-brutal-hover transition-all"
            @click="router.push({ name: 'register' })"
          >
            Создать аккаунт
          </button>
          <button
            type="button"
            class="inline-flex items-center justify-center px-8 py-3 bg-transparent text-white border-[3px] border-white font-black uppercase tracking-wide hover:bg-white/10 transition-all"
            @click="router.push({ name: 'login' })"
          >
            Войти
          </button>
        </div>
      </div>
    </section>

    <!-- Разделитель -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex items-center gap-4">
        <div class="flex-1 h-[3px] bg-black dark:bg-white"></div>
        <span class="text-xs font-black uppercase tracking-wider opacity-50">
          Для разработчиков
        </span>
        <div class="flex-1 h-[3px] bg-black dark:bg-white"></div>
      </div>
    </div>

    <!-- Стек -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h2 class="text-3xl font-black uppercase tracking-wider mb-2 text-center">
        Под капотом
      </h2>
      <p class="text-sm opacity-60 mb-8 text-center">
        Технический стек проекта
      </p>

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
      <h2 class="text-3xl font-black uppercase tracking-wider mb-2 text-center">
        Что дальше
      </h2>
      <p class="text-sm opacity-60 mb-8 text-center">
        Планы по развитию панели
      </p>

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
          <iframe src="https://yoomoney.ru/quickpay/fundraise/widget?billNumber=1KBU1HKLTL1.260917&" width="500" height="480" frameborder="0" allowtransparency="true" scrolling="no"></iframe>
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