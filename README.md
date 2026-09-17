# Panel

Открытая веб-панель для централизованного управления сервером и клиентами. Личный кабинет для пользователей и админ-панель для владельца.

## О проекте

Вместо ручной правки конфигов и общения в мессенджерах — единый интерфейс:

- **Личный кабинет** — создание ключей доступа, QR-код для подключения, статистика трафика
- **Админ-панель** — модерация заявок, управление пользователями, роли, блокировки, лимиты
- **Мульти-сервер** — управление несколькими узлами из одной панели
- **Статистика** — сбор трафика с серверов, разбивка по дням
- **Открытый код** — проект полностью открыт, поддержка добровольными пожертвованиями

## Стек

### Backend
- **PHP 8.2+**
- **Laravel 12**
- **MySQL / MariaDB**
- **Laravel Sanctum** — API-аутентификация
- **Xray-core** — VLESS + Reality

### Frontend
- **Vue 3** (Composition API, `<script setup>`)
- **Vite**
- **Vue Router**
- **Pinia**
- **Axios**
- **Tailwind CSS v4**
- **qrcode**

### Дизайн
- **Neo-brutalism** — острые углы, жирные контуры, жёсткие тени
- **Светлая и тёмная темы** — переключение через класс `dark`
- **Акценты** — синий (светлая), оранжевый (тёмная)

## Требования

- PHP 8.2+
- Composer
- Node.js 20.19+ или 22.12+
- MySQL 8.0+ / MariaDB 10.6+
- Xray-core с API (HandlerService + StatsService)
- Nginx (для прода)

## Установка

### 1. Клонирование

```bash
git clone <url-репозитория> vpn-panel
cd vpn-panel
```

### 2. Backend

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 3. Настройка `.env`

```env
APP_NAME="Panel"
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

# Xray API (для статистики и управления)
XRAY_API_SERVER=127.0.0.1:10085
XRAY_BINARY=xray

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

- `MAIL_PASSWORD` — пароль приложения Google (создаётся на `myaccount.google.com/apppasswords`).
- `XRAY_VLESS_HOST` — IP или домен VPN-сервера. Попадёт в `vless://` ссылки.
- `XRAY_VLESS_REALITY_PUBLIC_KEY` — сгенерировать из приватного: `xray x25519 --input <privateKey>`

### 4. Миграции и сидеры

```bash
mysql -u root -p -e "CREATE DATABASE vpn_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
```

После этого в БД будет:
- Роли `admin`, `user`
- Админ `admin@vpn.local` / `password`
- Xray-сервер с API-токеном (выведется в консоль — сохрани)

⚠️ **Смени пароль администратора после первого входа.**

### 5. Frontend

```bash
cd frontend
npm install
npm run dev
```

Vite запустится на `http://localhost:5173`.

## Развёртывание на VPS

### 1. Подготовка сервера

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mariadb-server \
    php8.3 php8.3-fpm php8.3-mysql php8.3-curl php8.3-json \
    php8.3-xml php8.3-mbstring php8.3-zip php8.3-gd php8.3-bcmath \
    php8.3-cli unzip git curl

curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Клонирование проекта

```bash
cd /var/www
sudo git clone <url-репозитория> vpn-panel
sudo chown -R $USER:$USER /var/www/vpn-panel
cd /var/www/vpn-panel
composer install --no-dev --optimize-autoloader
```

### 3. База данных

```bash
sudo mysql
```

```sql
CREATE DATABASE vpn_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'vpn_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON vpn_panel.* TO 'vpn_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Настройка `.env`

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
FRONTEND_URL=https://your-domain.com
```

Применить миграции:

```bash
php artisan key:generate
php artisan migrate --seed
```

Права:

```bash
sudo chown -R www-data:www-data /var/www/vpn-panel
sudo chmod -R 775 /var/www/vpn-panel/storage
sudo chmod -R 775 /var/www/vpn-panel/bootstrap/cache
```

### 5. Сборка фронта

```bash
cd frontend
npm install
npm run build
```

### 6. Nginx

Создай `/etc/nginx/sites-available/vpn-panel`:

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

Активируй:

```bash
sudo ln -s /etc/nginx/sites-available/vpn-panel /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

### 7. SSL (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

Certbot сам настроит HTTPS и автопродление.

### 8. Cron

**Laravel-расписание:**

```bash
sudo crontab -e -u www-data
```

```
* * * * * cd /var/www/vpn-panel && php artisan schedule:run >> /dev/null 2>&1
```

**Reload Xray** — раз в 5 минут подтягивает актуальный конфиг из панели:

```bash
sudo nano /usr/local/bin/xray-reload.sh
```

```bash
#!/bin/bash
set -euo pipefail

PANEL_URL="http://127.0.0.1"
API_TOKEN="<токен-из-БД>"
CONFIG_PATH="/usr/local/etc/xray/config.json"
TEMP_PATH="/tmp/xray-config.json"

curl -sf "$PANEL_URL/api/xray/config" \
    -H "Authorization: Bearer $API_TOKEN" \
    -o "$TEMP_PATH"

if xray run -test -config "$TEMP_PATH" > /dev/null 2>&1; then
    cp "$CONFIG_PATH" "$CONFIG_PATH.bak" 2>/dev/null || true
    cp "$TEMP_PATH" "$CONFIG_PATH"
    systemctl restart xray
fi
```

```bash
sudo chmod +x /usr/local/bin/xray-reload.sh

sudo crontab -e
```

```
*/5 * * * * /usr/local/bin/xray-reload.sh >> /var/log/xray-reload.log 2>&1
```

## Архитектура

```
┌─────────────────────────────────────────┐
│             Laravel Panel               │
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
│  │  xray-reload.sh (cron)            │  │
│  │  → забирает конфиг, рестартит     │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │  Xray (VLESS + Reality)           │  │
│  │  → API 127.0.0.1:10085            │  │
│  └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
```

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

## API

### Публичные

| Метод | Роут | Описание |
| :--- | :--- | :--- |
| POST | `/api/login` | Логин |
| POST | `/api/register` | Регистрация |
| POST | `/api/forgot-password` | Запрос сброса пароля |
| POST | `/api/reset-password` | Сброс пароля |
| GET | `/api/xray/config` | Конфиг Xray для сервера (Bearer) |

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

## Разработка

### Запуск в dev-режиме

```bash
# Терминал 1 — Laravel
php artisan serve --host=0.0.0.0

# Терминал 2 — Vite
cd frontend && npm run dev

# Терминал 3 — Mailpit (для писем)
brew install mailpit && mailpit
```

Mailpit: `http://localhost:8025`

### Полезные команды

```bash
php artisan optimize:clear           # Очистить весь кэш
php artisan migrate:fresh --seed     # Пересоздать БД
php artisan route:list --path=api -v # Список роутов
php artisan xray:sync-traffic        # Синхронизация трафика
php artisan xray:sync-clients        # Синхронизация клиентов
php artisan tinker                   # REPL
```

## Roadmap

- [x] Аутентификация (Sanctum)
- [x] Модерация регистраций
- [x] Блокировка пользователей
- [x] CRUD ключей
- [x] Генерация VLESS + Reality ссылок
- [x] QR-код для подключения
- [x] Мульти-сервер (`xray_servers`)
- [x] Генерация конфига Xray
- [x] Endpoint для Xray-сервера
- [x] Статистика трафика
- [x] Админ-панель с метриками
- [x] Смена и восстановление пароля
- [x] Дизайн-система (brutalism, темы)
- [x] Анимации
- [x] Страница «О проекте» с поддержкой
- [ ] gRPC-дельты (add/remove без перезапуска)
- [ ] Автодеактивация при превышении лимита
- [ ] Лента новостей
- [ ] Подписки (email + Telegram)
- [ ] Уведомления о событиях
- [ ] Балансировщик нагрузки
- [ ] Двухфакторная аутентификация

## Лицензия

MIT