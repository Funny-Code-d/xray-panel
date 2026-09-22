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
- CRUD постов и тегов
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

**Инфраструктура:**
- Деплой на VPS (Nginx + PHP-FPM + MariaDB)
- HTTPS через Let's Encrypt
- GitHub Actions CI/CD с self-hosted runner
- Docker для Xray и агента
- Cron для синхронизации трафика и reload Xray

### 🚧 В планах

**Xray-интеграция:**
- [ ] gRPC-дельты (`AddUser` / `RemoveUser`) без перезапуска Xray
- [ ] Автодеактивация ключей при превышении лимита трафика
- [ ] Сброс лимита трафика по расписанию
- [ ] Учёт онлайн-сессий и аптайма (`GetStatsOnline`)
- [ ] Развёртывание Xray на отдельном VPS на порту 443

**Коммуникация:**
- [ ] Рассылка при публикации постов (`NewPostMail` + Job + очередь)
- [ ] Уведомления в Telegram
- [ ] Уведомления о событиях (заявка одобрена, аккаунт заблокирован, трафик на исходе)
- [ ] Telegram-бот для управления аккаунтом

**Инфраструктура:**
- [ ] Блок «Серверы» со стойкой и LED-индикаторами
- [ ] Выбор сервера при создании ключа
- [ ] Node Agent — heartbeat и метрики с каждого узла
- [ ] Балансировщик нагрузки

**Безопасность:**
- [ ] Двухфакторная аутентификация (2FA)
- [ ] Логи действий администраторов
- [ ] Включить UFW на VPS
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

---

## Архитектура

```
┌─────────────────────────────────────────┐
│            Laravel Panel                │
│  ┌───────────────────────────────────┐  │
│  │  XrayConfigBuilder                │  │
│  │  → генерирует config.json         │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │  XrayService                      │  │
│  │  → читает статистику по email     │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │  GET /api/xray/config             │  │
│  │  → отдаёт конфиг серверу          │  │
│  └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
                    ↑
        HTTP (Bearer token)
                    │
┌─────────────────────────────────────────┐
│              Xray Node                  │
│  ┌───────────────────────────────────┐  │
│  │  Docker: Xray + Agent             │  │
│  │  → забирает конфиг при старте     │  │
│  │  → hot-reload через SIGHUP        │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │  Xray (VLESS + Reality)           │  │
│  │  → API 127.0.0.1:10085            │  │
│  └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
```

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
| PATCH | `/api/admin/users/{id}` | Обновить (роли, лимит) |
| POST | `/api/admin/users/{id}/approve` | Одобрить |
| POST | `/api/admin/users/{id}/reject` | Отклонить |
| POST | `/api/admin/users/{id}/block` | Заблокировать |
| POST | `/api/admin/users/{id}/unblock` | Разблокировать |
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
vless://<UUID>@<HOST>:<PORT>?type=tcp&security=reality&flow=xtls-rprx-vision&sni=dl.google.com&fp=chrome&pbk=<PUBLIC_KEY>&sid=<SHORT_ID>&spx=%2F#Name
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

# Xray сервер (VLESS + Reality)
XRAY_VLESS_HOST=your.server.ip
XRAY_VLESS_PORT=443
XRAY_VLESS_API_PORT=10085
XRAY_VLESS_INBOUND_TAG=vless-inbound
XRAY_VLESS_FLOW=xtls-rprx-vision

XRAY_VLESS_REALITY_DEST=dl.google.com:443
XRAY_VLESS_REALITY_SNI=dl.google.com
XRAY_VLESS_REALITY_PRIVATE_KEY=your_private_key
XRAY_VLESS_REALITY_PUBLIC_KEY=your_public_key
XRAY_VLESS_REALITY_SHORT_ID=0123456789abcdef
XRAY_VLESS_FINGERPRINT=chrome
```

**Важно:**
- `MAIL_PASSWORD` — пароль приложения Google (не обычный пароль).
- `XRAY_VLESS_HOST` — IP или домен VPN-сервера.
- `XRAY_VLESS_REALITY_PUBLIC_KEY` — сгенерировать: `xray x25519 -i <privateKey>`

**4. Миграции и сидеры:**

```bash
mysql -u root -p -e "CREATE DATABASE vpn_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
```

После этого в БД:
- Роли `admin`, `user`
- Админ `admin@vpn.local` / `password`
- Xray-сервер с API-токеном (выведется в консоль — сохрани)
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

Или кратко:

**1. Подготовка сервера:**

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mariadb-server \
    php8.3 php8.3-fpm php8.3-mysql php8.3-curl php8.3-json \
    php8.3-xml php8.3-mbstring php8.3-zip php8.3-gd php8.3-bcmath \
    php8.3-cli unzip git curl

curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs

curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

**2. Клонирование и установка:**

```bash
cd /var/www
sudo git clone https://github.com/Funny-Code-d/xray-panel.git vpn-panel
sudo chown -R www-data:www-data /var/www/vpn-panel
cd /var/www/vpn-panel
sudo -u www-data composer install --no-dev --optimize-autoloader
```

**3. База данных:**

```bash
sudo mysql

CREATE DATABASE vpn_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'vpn_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON vpn_panel.* TO 'vpn_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**4. Production `.env`:**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
FRONTEND_URL=https://your-domain.com
```

```bash
sudo -u www-data php artisan key:generate
sudo -u www-data php artisan migrate --seed
```

**5. Сборка фронта:**

```bash
cd frontend
sudo -u www-data npm install
sudo -u www-data npm run build
```

**6. Nginx:**

```nginx
server {
    listen 80;
    server_name your-domain.com;

    root /var/www/vpn-panel/frontend/dist;
    index index.html;

    location /api/ {
        root /var/www/vpn-panel/public;
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        root /var/www/vpn-panel/public;
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

**7. SSL:**

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

**8. Cron:**

```bash
sudo crontab -e -u www-data
```

```
* * * * * cd /var/www/vpn-panel && php artisan schedule:run >> /dev/null 2>&1
```

### CI/CD через GitHub Actions

Проект использует **self-hosted runner** на VPS. При пуше в `main` запускается workflow `.github/workflows/deploy.yml`.

**Настройка runner:**

1. GitHub → Settings → Actions → Runners → New self-hosted runner
2. Следуй инструкциям (скачать, зарегистрировать, запустить)
3. Установи как systemd-сервис

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
cd /var/www/vpn-panel
docker compose up -d --build    # Запуск
docker logs -f xray             # Логи
docker restart xray             # Перезапуск
```

### Переменные окружения для Xray

```env
XRAY_API_SERVER=127.0.0.1:10085
XRAY_BINARY=/usr/local/bin/xray

XRAY_VLESS_HOST=your.server.ip
XRAY_VLESS_PORT=443
XRAY_VLESS_API_PORT=10085
XRAY_VLESS_INBOUND_TAG=vless-inbound
XRAY_VLESS_FLOW=xtls-rprx-vision

XRAY_VLESS_REALITY_DEST=dl.google.com:443
XRAY_VLESS_REALITY_SNI=dl.google.com
XRAY_VLESS_REALITY_PRIVATE_KEY=...
XRAY_VLESS_REALITY_PUBLIC_KEY=...
XRAY_VLESS_REALITY_SHORT_ID=...
XRAY_VLESS_FINGERPRINT=chrome
```

---

## Поддержать проект

Проект полностью открытый и развивается в свободное время. Если хотите поддержать — можно сделать добровольное пожертвование через [ЮMoney](https://yoomoney.ru/quickpay/fundraise/button?billNumber=1KBU1HKLTL1.260917&). Средства идут на серверы и развитие.

---

## Лицензия

MIT — смотри [LICENSE](LICENSE).