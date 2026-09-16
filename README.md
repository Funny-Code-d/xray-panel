# VPN Panel

Веб-панель для управления VPN-сервером на базе Xray (VMess + WebSocket). Единый личный кабинет для клиентов и админ-панель для владельца сервиса.

## О проекте

Вместо ручного редактирования конфигов Xray и общения в Telegram — централизованное управление через веб-интерфейс:

- **Личный кабинет** — создание VPN-ключей, QR-код для подключения, статистика трафика
- **Админ-панель** — модерация заявок, управление пользователями, роли, блокировки, лимиты
- **Лента новостей** — публикации для пользователей с подписками на email/Telegram *(в разработке)*
- **Уведомления** — email и Telegram *(в разработке)*

## Стек

### Backend
- **PHP 8.2+**
- **Laravel 12**
- **MySQL / MariaDB** — база данных
- **Laravel Sanctum** — API-аутентификация через токены
- **Xray-core** — VPN-сервер (VMess + WebSocket)

### Frontend
- **Vue 3** (Composition API, `<script setup>`)
- **Vite** — сборщик
- **Vue Router** — роутинг
- **Pinia** — стейт-менеджмент
- **Axios** — HTTP-клиент
- **Tailwind CSS v4** — стилизация
- **qrcode** — генерация QR-кодов

### Дизайн
- **Neo-brutalism** — острые углы, жирные контуры, жёсткие тени
- **Светлая и тёмная темы** — переключение через класс `dark` на `<html>`
- **Акценты** — синий (светлая тема), оранжевый (тёмная)

## Требования

- PHP 8.2+
- Composer
- Node.js 20.19+ или 22.12+
- MySQL 8.0+ / MariaDB 10.6+
- Xray-core (для продакшена)

## Установка

### 1. Клонирование

```bash
git clone <url-репозитория> vpn-panel
cd vpn-panel
```

### 2. Backend

```bash
composer install

# Копируем .env
cp .env.example .env

# Генерируем APP_KEY
php artisan key:generate
```

### 3. Настройка `.env`

Открой `.env` и настрой подключение к БД, почту и Xray:

```env
APP_NAME="VPN Panel"
APP_URL=http://127.0.0.1:8000
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

# Xray
XRAY_SERVER_HOST=your.vpn.server
XRAY_SERVER_PORT=80
XRAY_SERVER_NETWORK=ws
XRAY_SERVER_PATH=/api/v2/download
XRAY_SERVER_TLS=
XRAY_SERVER_SNI=
XRAY_SERVER_ALTER_ID=0
```

**Важно:**
- `MAIL_PASSWORD` — это **пароль приложения Google**, не обычный пароль. Создаётся на `myaccount.google.com/apppasswords` при включённой двухэтапной аутентификации.
- `XRAY_SERVER_HOST` — IP или домен твоего VPN-сервера. Именно это значение попадёт в `vmess://` ссылки.

### 4. Миграции и сидеры

```bash
# Создай базу данных в MySQL
mysql -u root -p -e "CREATE DATABASE vpn_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Применяем миграции + создаём роли и админа + демо-данные
php artisan migrate:fresh --seed
```

После этого в БД будет:
- **Роли:** `admin`, `user`
- **Админ:** `admin@vpn.local` / `password`
- **10 демо-пользователей** с ключами (только в `local` окружении)

⚠️ **Смени пароль администратора после первого входа.** Дефолтный `password` — только для локальной разработки.

### 5. Запуск backend

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Laravel доступен на `http://127.0.0.1:8000`, API — на `http://127.0.0.1:8000/api`.

### 6. Frontend

В новом терминале:

```bash
cd frontend
npm install
npm run dev
```

Vite запустится на `http://localhost:5173`. Открой в браузере.

## Структура проекта

```
vpn-panel/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── ClientController.php
│   │   │   └── Admin/
│   │   │       ├── UserController.php
│   │   │       ├── PostController.php
│   │   │       └── DashboardController.php
│   │   ├── Middleware/
│   │   │   ├── EnsureUserIsAdmin.php
│   │   │   ├── EnsureUserIsApproved.php
│   │   │   ├── EnsureUserIsNotBlocked.php
│   │   │   └── ForceJsonResponse.php
│   │   └── Requests/
│   │       └── LoginRequest.php
│   ├── Mail/
│   │   └── PasswordChangedMail.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── VpnClient.php
│   │   └── Post.php
│   └── Notifications/
│       └── ResetPasswordNotification.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── frontend/
│   ├── src/
│   │   ├── api/axios.js
│   │   ├── components/
│   │   │   ├── ui/           — Button, Input, Modal, IconButton, ConfirmModal
│   │   │   ├── admin/        — AdminLayout, RejectModal, BlockModal, EditUserModal
│   │   │   ├── dashboard/    — UserDashboard, AdminDashboard
│   │   │   ├── AppLayout.vue
│   │   │   ├── ClientStatus.vue
│   │   │   ├── CreateClientModal.vue
│   │   │   └── QrCodeModal.vue
│   │   ├── pages/
│   │   │   ├── LoginPage.vue
│   │   │   ├── RegisterPage.vue
│   │   │   ├── ForgotPasswordPage.vue
│   │   │   ├── ResetPasswordPage.vue
│   │   │   ├── PendingPage.vue
│   │   │   ├── BlockedPage.vue
│   │   │   ├── DashboardPage.vue
│   │   │   ├── ClientsPage.vue
│   │   │   ├── AdminUserPage.vue
│   │   │   └── admin/
│   │   │       ├── ApplicationsPage.vue
│   │   │       └── UsersPage.vue
│   │   ├── router/index.js
│   │   ├── stores/
│   │   │   ├── auth.js
│   │   │   └── theme.js
│   │   ├── utils/format.js
│   │   ├── App.vue
│   │   ├── main.js
│   │   └── style.css
│   └── package.json
├── routes/
│   ├── api.php
│   ├── console.php
│   └── web.php
└── .env
```

## Роли и статусы

### Роли (`role_user`)
- **`admin`** — полный доступ к админ-панели
- **`user`** — обычный пользователь

### Статусы одобрения (`users.approval_status`)
- **`pending`** — заявка на рассмотрении, доступ только к `/pending`
- **`approved`** — полный доступ
- **`rejected`** — заявка отклонена, доступ только к `/pending`

### Блокировка (`users.is_blocked`)
Независимо от статуса одобрения. При блокировке:
- Все VPN-ключи деактивируются
- Все активные сессии (Sanctum-токены) отзываются
- Пользователь видит только `/blocked`

## API

Полный список роутов: `php artisan route:list --path=api`

### Публичные

| Метод | Роут | Описание |
| :--- | :--- | :--- |
| POST | `/api/login` | Логин |
| POST | `/api/register` | Регистрация |
| POST | `/api/forgot-password` | Запрос ссылки сброса пароля |
| POST | `/api/reset-password` | Сброс пароля по токену |

### Аутентифицированные

| Метод | Роут | Описание |
| :--- | :--- | :--- |
| POST | `/api/logout` | Выход |
| GET | `/api/me` | Текущий пользователь |
| POST | `/api/change-password` | Смена пароля |

### Только для одобренных

| Метод | Роут | Описание |
| :--- | :--- | :--- |
| GET | `/api/clients` | Список ключей |
| POST | `/api/clients` | Создать ключ |
| GET | `/api/clients/{id}` | Конкретный ключ |
| PATCH | `/api/clients/{id}` | Обновить ключ |
| DELETE | `/api/clients/{id}` | Удалить ключ |
| GET | `/api/clients/{id}/config` | Конфиг + `vmess://` ссылка |

### Только для админов

| Метод | Роут | Описание |
| :--- | :--- | :--- |
| GET | `/api/admin/dashboard` | Метрики системы |
| GET | `/api/admin/users` | Список пользователей |
| GET | `/api/admin/users/{id}` | Карточка пользователя |
| PATCH | `/api/admin/users/{id}` | Обновить (роли, лимит) |
| POST | `/api/admin/users/{id}/approve` | Одобрить заявку |
| POST | `/api/admin/users/{id}/reject` | Отклонить заявку |
| POST | `/api/admin/users/{id}/block` | Заблокировать |
| POST | `/api/admin/users/{id}/unblock` | Разблокировать |

## Генерация `vmess://` ссылки

Формат ссылки — `vmess://` + base64 от JSON. Совместим с **v2rayTun**, **OneXray**, **v2rayNG**, **WINGS**.

**Особенности формата** (важно для совместимости с iOS-клиентами):
- `port` и `aid` — **числа**, не строки
- `host` — **пустая строка** для не-TLS конфигураций
- `tls` — **явно `"none"`**, если TLS нет
- `JSON_PRETTY_PRINT` — с отступами, как в эталонных ссылках

Пример декодированного JSON:

```json
{
  "v": "2",
  "ps": "Мой iPhone",
  "add": "your.vpn.server",
  "port": 80,
  "id": "00000000-0000-0000-0000-000000000000",
  "aid": 0,
  "net": "ws",
  "type": "none",
  "host": "",
  "path": "/api/v2/download",
  "tls": "none"
}
```

## Разработка

### Запуск в dev-режиме

**Терминал 1 — Laravel:**

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**Терминал 2 — Vite:**

```bash
cd frontend
npm run dev
```

**Терминал 3 — Mailpit (для отладки писем):**

```bash
brew install mailpit
mailpit
```

Веб-интерфейс Mailpit: `http://localhost:8025`. Все письма, отправленные Laravel, будут перехвачены сюда.

### Тестирование с телефона

Чтобы зайти с телефона в локальной сети:

1. Узнай IP Mac:
   ```bash
   ipconfig getifaddr en0
   ```

2. Laravel уже слушает `0.0.0.0` (флаг `--host`).

3. Vite должен слушать `0.0.0.0` — добавь в `frontend/vite.config.js`:
   ```js
   server: {
     host: '0.0.0.0',
     port: 5173,
     // ...
   }
   ```

4. Открой на телефоне:
   ```
   http://<твой-локальный-IP>:5173
   ```

## Полезные команды

```bash
# Очистить кэш
php artisan optimize:clear

# Пересоздать БД с сидерами
php artisan migrate:fresh --seed

# Список роутов
php artisan route:list --path=api -v

# Tinker
php artisan tinker

# Сборка фронта для прода
cd frontend && npm run build
```

## Что дальше (Roadmap)

- [x] Аутентификация (Sanctum)
- [x] Модерация регистраций (pending/approved/rejected)
- [x] Блокировка пользователей
- [x] CRUD VPN-ключей
- [x] Генерация `vmess://` ссылки
- [x] QR-код для подключения
- [x] Админ-панель с метриками
- [x] Смена и восстановление пароля
- [x] Дизайн-система (brutalism, светлая/тёмная тема)
- [x] Анимации переходов
- [ ] Xray-интеграция (синхронизация UUID через API)
- [ ] Сбор статистики трафика
- [ ] Лента новостей
- [ ] Подписки (email + Telegram)
- [ ] Уведомления о событиях
- [ ] Двухфакторная аутентификация
- [ ] Экспорт данных

## Лицензия

MIT