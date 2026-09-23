<p align="center">
  <img src="frontend/public/logo-full.png" alt="FunnyNodes" width="400" />
</p>

<h1 align="center">FunnyNodes</h1>

<p align="center">
  Открытая панель управления Xray-серверами
</p>

<p align="center">
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-FFD700?style=for-the-badge" alt="License"></a>
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Vue-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3">
</p>

<p align="center">
  🌐 <a href="https://funny-code.space"><b>funny-code.space</b></a>
</p>

<p align="center">
  Панель работает в продакшене. Регистрация открыта — после подтверждения администратором вы получите доступ.
</p>

---

## О проекте

**FunnyNodes** — открытая веб-панель для централизованного управления Xray-серверами. Построена на Laravel + Vue 3, работает с Xray-core через **VLESS + Reality**.

Вместо ручного редактирования `config.json`, запуска команд через SSH и общения с пользователями в мессенджерах — единый интерфейс с личным кабинетом и админ-панелью.

### Что делает панель

- **Управляет Xray-серверами** — генерирует `config.json` из БД, отдаёт серверу через API
- **Выдаёт ключи доступа** — генерирует `vless://` ссылки и QR-коды для подключения
- **Собирает статистику** — читает трафик из Xray Stats API по email клиента, хранит по дням
- **Управляет пользователями** — регистрация с модерацией, роли, блокировки, лимиты
- **Мульти-сервер** — один экземпляр панели управляет несколькими Xray-узлами
- **Лента новостей** — посты с тегами и подписками (email)
- **Админ-панель** — модерация заявок, CRUD постов и тегов, метрики системы

### Технически

- **Laravel 12** — API-бэкенд
- **Vue 3** — SPA-фронтенд (Composition API, `<script setup>`)
- **Laravel Sanctum** — API-аутентификация
- **Xray-core** — VLESS + Reality (устойчивый к DPI протокол)
- **Xray API** через CLI (`xray api statsquery`) для статистики
- **Docker** — Xray и агент перезагрузки в контейнерах
- **GitHub Actions** — CI/CD через self-hosted runner

---

## Статус проекта

### ✅ Реализовано

**Аутентификация и пользователи:**
- Регистрация с модерацией заявок (`pending` / `approved` / `rejected`)
- Логин / логаут через Laravel Sanctum
- Смена пароля + восстановление через email
- Блокировка / разблокировка пользователей (с деактивацией всех ключей)
- Роли (`admin` / `user`) через таблицу `roles` + pivot `role_user`

**VPN-ключи:**
- CRUD ключей доступа
- Автогенерация UUID и email для каждого ключа
- Привязка ключа к конкретному Xray-серверу
- Генерация `vless://` ссылок (VLESS + Reality)
- Генерация `vmess://` ссылок (legacy, для совместимости)
- QR-код для подключения
- Копирование ссылки в буфер обмена

**Xray-интеграция:**
- Мульти-серверная модель (`xray_servers`)
- Генерация полного конфига Xray из БД (`XrayConfigBuilder`)
- Endpoint `GET /api/xray/config` с Bearer-токеном для серверов
- Docker-контейнер Xray с автозагрузкой конфига
- Агент hot-reload через `SIGHUP` (без перезапуска контейнера)
- Чтение статистики через `xray api statsquery`
- Синхронизация трафика по email (`xray:sync-traffic`)
- Таблица `traffic_stats` — дневная разбивка трафика
- Развёрнуты узлы во Франции и Нидерландах

**Лента новостей:**
- Посты с HTML-контентом
- Теги с цветами и описанием (`updates`, `incidents`, `maintenance`, `news`, `guides`)
- Публичная лента `/news` с фильтром по тегам
- Подписки на email (все новости или конкретные теги)
- Эксклюзивная логика: «все новости» ↔ теги
- Tailwind Typography для красивого контента

**Админ-панель:**
- Дашборд с метриками системы
- Список заявок на одобрение
- Список всех пользователей с фильтрами и поиском
- Карточка пользователя со всей информацией
- Предпросмотр дашборда пользователя (read-only)
- CRUD постов и тегов
- CRUD серверов + кнопка «Показать токен»
- Смена ролей и лимита трафика
- Одобрение / отклонение заявок
- Блокировка / разблокировка

**Дизайн:**
- Neo-brutalism — острые углы, жирные контуры (3px), жёсткие тени
- Жёлтый акцент `#FFD700`
- Второй акцент: оранжевый `#FF4911` (light) / маджента `#FF00FF` (dark)
- Светлая и тёмная темы
- Шрифты: Space Grotesk (заголовки), Inter (текст), JetBrains Mono (код)
- Бургер-меню с цветными полосами для каждой ссылки
- Анимации переходов и появления элементов
- Адаптив под мобильные

**Безопасность:**
- SSH-ключи (Ed25519) на всех серверах
- Вход по паролю **отключён** на всех серверах
- `PermitRootLogin prohibit-password` — root только по ключу
- UFW на всех серверах: только нужные порты
- Агент и API Xray — **только для IP панели**
- Sanctum-токены для API
- `ForceJsonResponse` middleware

**Инфраструктура:**
- Деплой на VPS (Nginx + PHP-FPM + MariaDB)
- HTTPS через Let's Encrypt
- GitHub Actions CI/CD с self-hosted runner
- Docker для Xray и агента
- Cron для синхронизации трафика

### 🚧 В планах

**Xray-интеграция:**
- [ ] gRPC-дельты (`AddUser` / `RemoveUser`) без перезапуска Xray
- [ ] Автодеактивация ключей при превышении лимита трафика
- [ ] Сброс лимита трафика по расписанию
- [ ] Учёт онлайн-сессий и аптайма (`GetStatsOnline`)
- [ ] Node Agent — heartbeat и метрики с каждого узла

**Коммуникация:**
- [ ] Рассылка при публикации постов (`NewPostMail` + Job + очередь)
- [ ] Уведомления в Telegram
- [ ] Telegram-бот для дублирования новостей в канал
- [ ] Уведомления о событиях (заявка одобрена, аккаунт заблокирован, трафик на исходе)

**Безопасность:**
- [ ] Двухфакторная аутентификация (2FA)
- [ ] Логи действий администраторов
- [ ] fail2ban — защита от SSH brute-force
- [ ] Rate limiting на чувствительные endpoint'ы

**UI/UX:**
- [ ] OG-image для соцсетей
- [ ] Графики трафика (по дням, неделям, месяцам)
- [ ] Экспорт данных (CSV, JSON)

---

## Стек

### Backend
- **PHP 8.3+**
- **Laravel 12**
- **MySQL / MariaDB 10.6+**
- **Laravel Sanctum** — API-аутентификация
- **Xray-core** — VLESS + Reality

### Frontend
- **Vue 3** (Composition API)
- **Vite**
- **Vue Router**
- **Pinia**
- **Axios**
- **Tailwind CSS v4**
- **qrcode**

### Инфраструктура
- **Nginx** — reverse proxy + статика
- **PHP-FPM**
- **MariaDB**
- **Docker** — Xray в контейнере
- **Let's Encrypt** — SSL
- **GitHub Actions** — CI/CD
- **Cron** — синхронизация
- **UFW** — firewall

---

## Архитектура

```
┌─────────────────────────────────────────┐
│         Панель (Польша)                 │
│  ┌───────────────────────────────────┐  │
│  │  Laravel + Nginx + MariaDB        │  │
│  │  - XrayConfigBuilder              │  │
│  │  - XrayService                    │  │
│  │  - GET /api/xray/config           │  │
│  └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
                    │
        HTTP (Bearer token)
                    │
        ┌───────────┴───────────┐
        ↓                       ↓
┌───────────────┐       ┌───────────────┐
│ Франция       │       │ Нидерланды    │
│ Xray :443     │       │ Xray :443     │
│ Агент :8080   │       │ Агент :8080   │
│ API :10085    │       │ API :10085    │
└───────────────┘       └───────────────┘
```

---

## Безопасность

### SSH

**На всех серверах:**
- Вход по паролю **отключён** (`PasswordAuthentication no`)
- Root **только по ключу** (`PermitRootLogin prohibit-password`)
- Ключ Ed25519 с passphrase
- `KbdInteractiveAuthentication no` — обязательно, иначе PAM пустит по паролю

**Конфиг `/etc/ssh/sshd_config.d/01-hardening.conf`:**

```
PasswordAuthentication no
KbdInteractiveAuthentication no
PermitRootLogin prohibit-password
X11Forwarding no
```

⚠️ **Важно:** если есть `/etc/ssh/sshd_config.d/50-cloud-init.conf` с `PasswordAuthentication yes` — **удалить**, иначе он перебивает `01-`.

### UFW

**Панель (Польша):**

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

**VPN-узлы (Франция, Нидерланды):**

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp
sudo ufw allow 443/tcp
sudo ufw allow 443/udp
sudo ufw allow from 82.40.38.102 to any port 8080
sudo ufw allow from 82.40.38.102 to any port 10085
sudo ufw enable
```

**Что закрыто:**
- 8000 — Laravel dev
- 3306 — MySQL
- 8443 — тестовый Xray (если был)
- Всё остальное — deny by default

### API-токены

- У каждого Xray-сервера свой `api_token` (64 hex-символа)
- Токен используется для:
  - `GET /api/xray/config` — получение конфига
  - `POST /agent:8080/restart-xray` — hot-reload
- Токен **не попадает** в публичный `/api/servers`
- Токен доступен только админам через `/admin/servers` → «Показать токен»

---

## Роли и статусы

### Роли (`role_user`)
- **`admin`** — полный доступ к админ-панели
- **`user`** — обычный пользователь

### Статусы одобрения (`users.approval_status`)
- **`pending`** — заявка на рассмотрении, доступ только к `/pending`
- **`approved`** — полный доступ
- **`rejected`** — заявка отклонена

### Блокировка (`users.is_blocked`)
Независимо от статуса одобрения. При блокировке:
- Все ключи деактивируются
- Все Sanctum-токены отзываются
- Пользователь видит только `/blocked`

---

## API

### Публичные

| Метод | Роут | Описание |
| :--- | :--- | :--- |
| POST | `/api/login` | Логин |
| POST | `/api/register` | Регистрация |
| POST | `/api/forgot-password` | Запрос сброса пароля |
| POST | `/api/reset-password` | Сброс пароля |
| GET | `/api/xray/config` | Конфиг Xray для сервера (Bearer) |
| GET | `/api/posts` | Список опубликованных постов |
| GET | `/api/posts/{slug}` | Конкретный пост |
| GET | `/api/tags` | Список тегов |

### Аутентифицированные

| Метод | Роут | Описание |
| :--- | :--- | :--- |
| POST | `/api/logout` | Выход |
| GET | `/api/me` | Текущий пользователь |
| POST | `/api/change-password` | Смена пароля |
| GET | `/api/servers` | Список серверов |
| GET | `/api/subscriptions` | Список подписок |
| POST | `/api/subscriptions` | Создать подписку |
| POST | `/api/subscriptions/bulk` | Массовое обновление |
| DELETE | `/api/subscriptions/{id}` | Удалить подписку |

### Только для одобренных

| Метод | Роут | Описание |
| :--- | :--- | :--- |
| GET | `/api/clients` | Список ключей |
| POST | `/api/clients` | Создать ключ |
| GET | `/api/clients/{id}` | Конкретный ключ |
| PATCH | `/api/clients/{id}` | Обновить |
| DELETE | `/api/clients/{id}` | Удалить |
| GET | `/api/clients/{id}/config` | Конфиг + ссылка |

### Только для админов

| Метод | Роут | Описание |
| :--- | :--- | :--- |
| GET | `/api/admin/dashboard` | Метрики |
| GET | `/api/admin/users` | Список пользователей |
| GET | `/api/admin/users/{id}` | Карточка |
| GET | `/api/admin/users/{id}/dashboard` | Предпросмотр дашборда |
| PATCH | `/api/admin/users/{id}` | Обновить (роли, лимит) |
| POST | `/api/admin/users/{id}/approve` | Одобрить |
| POST | `/api/admin/users/{id}/reject` | Отклонить |
| POST | `/api/admin/users/{id}/block` | Заблокировать |
| POST | `/api/admin/users/{id}/unblock` | Разблокировать |
| GET | `/api/admin/servers` | Список серверов |
| POST | `/api/admin/servers` | Создать сервер |
| GET | `/api/admin/servers/{id}` | Карточка сервера |
| PATCH | `/api/admin/servers/{id}` | Обновить |
| DELETE | `/api/admin/servers/{id}` | Удалить |
| GET | `/api/admin/posts` | Все посты |
| POST | `/api/admin/posts` | Создать пост |
| PATCH | `/api/admin/posts/{id}` | Обновить |
| DELETE | `/api/admin/posts/{id}` | Удалить |
| GET | `/api/admin/tags` | Все теги |
| POST | `/api/admin/tags` | Создать тег |
| PATCH | `/api/admin/tags/{id}` | Обновить |
| DELETE | `/api/admin/tags/{id}` | Удалить |

---

## Генерация ссылок

### VLESS + Reality

```
vless://<UUID>@<HOST>:<PORT>?type=tcp&security=reality&flow=xtls-rprx-vision&sni=dl.google.com&fp=firefox&pbk=<PUBLIC_KEY>&sid=<SHORT_ID>&spx=%2F#Name
```

Совместим с **OneXray**, **v2rayTun**, **v2rayNG**, **WINGS**.

### VMess (legacy)

```
vmess://base64(json)
```

---

## Настройка

### Требования

- PHP 8.2+
- Composer
- Node.js 20.19+ или 22.12+
- MySQL 8.0+ / MariaDB 10.6+
- Xray-core с API (HandlerService + StatsService)
- Docker (для Xray в контейнере)
- Nginx (для прода)

### Локальная разработка

**1. Клонирование:**

```bash
git clone https://github.com/Funny-Code-d/xray-panel.git vpn-panel
cd vpn-panel
```

**2. Backend:**

```bash
composer install
cp .env.example .env
php artisan key:generate
```

**3. Настройка `.env`:**

```env
APP_NAME="FunnyNodes"
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vpn_panel
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Почта (Gmail)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"

# Xray API
XRAY_API_SERVER=127.0.0.1:10085
XRAY_BINARY=/usr/local/bin/xray
```

**4. Миграции и сидеры:**

```bash
mysql -u root -p -e "CREATE DATABASE vpn_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
```

После этого в БД:
- Роли `admin`, `user`
- Админ `admin@vpn.local` / `password`
- Теги: `updates`, `incidents`, `maintenance`, `news`, `guides`

⚠️ **Смени пароль администратора после первого входа.**

**5. Frontend:**

```bash
cd frontend
npm install
npm run dev
```

### Развёртывание на VPS

Смотри [docs/DEPLOY.md](docs/DEPLOY.md) — там пошаговая инструкция.

### Развёртывание VPN-узла

**1. Подготовка сервера:**

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y docker.io docker-compose-plugin git curl ufw
```

**2. Клонирование:**

```bash
git clone https://github.com/Funny-Code-d/xray-panel.git
cd xray-panel
```

**3. Настройка `.env`:**

```bash
nano docker/xray/.env
```

```env
XRAY_PANEL_URL=https://funny-code.space
XRAY_API_TOKEN=<токен-из-панели>
```

**4. Запуск:**

```bash
cd docker/xray
docker compose up -d --build
docker logs -f xray
```

**5. Firewall:**

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp
sudo ufw allow 443/tcp
sudo ufw allow 443/udp
sudo ufw allow from <IP-панели> to any port 8080
sudo ufw allow from <IP-панели> to any port 10085
sudo ufw enable
```

### CI/CD через GitHub Actions

Проект использует **self-hosted runner** на VPS с панелью. При пуше в `main` запускается workflow `.github/workflows/deploy.yml`.

**Workflow:**

- `git pull origin main`
- `composer install --no-dev --optimize-autoloader`
- `npm install && npm run build`
- `php artisan migrate --force`
- `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- `chown -R www-data:www-data storage bootstrap/cache`
- `systemctl restart php8.3-fpm`

---

## Разработка

### Полезные команды

```bash
php artisan optimize:clear           # Очистить весь кэш
php artisan migrate:fresh --seed     # Пересоздать БД
php artisan route:list --path=api -v # Список роутов
php artisan xray:sync-traffic        # Синхронизация трафика
php artisan xray:sync-clients        # Синхронизация клиентов
php artisan db:seed --class=TagSeeder # Сидер тегов
php artisan tinker                   # REPL
```

### Работа с Xray в Docker

```bash
cd docker/xray
docker compose up -d --build    # Запуск
docker logs -f xray             # Логи
docker restart xray             # Перезапуск
```

---

## Поддержать проект

Проект полностью открытый и развивается в свободное время. Если хотите поддержать — можно сделать добровольное пожертвование через [ЮMoney](https://yoomoney.ru/quickpay/fundraise/button?billNumber=1KBU1HKLTL1.260917&). Средства идут на серверы и развитие.

---

## Лицензия

MIT — смотри [LICENSE](LICENSE).