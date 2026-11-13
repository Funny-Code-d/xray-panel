# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Мониторинг доступности серверов через Check-Host API
- Автоматическая смена IP через Hostkey API
- Уведомления в Telegram о событиях

## [1.1.0] - 2026-10-04

### Added
- Поддержка нескольких протоколов на одном Xray-сервере: VLESS + Reality, VMess + WebSocket + TLS, Trojan + TLS.
- Таблица `xray_server_protocols` — настройки протоколов вынесены из `xray_servers`.
- `ConfigBuilder` и `InboundBuilders` (Vless, Vmess, Trojan) — сборка конфига Xray из БД.
- `LinkGenerator` и `LinkBuilders` — генерация ссылок `vless://`, `vmess://`, `trojan://`.
- API-inbound (`dokodemo-door`) на порту 10085 + routing-правило для статистики Xray.
- Форма сервера с чекбоксами протоколов в админке.
- Табы протоколов в модалке ключа, QR-код и копирование ссылки.
- Выбор сервера и протокола при создании ключа.
- Nginx в Docker на 80 порту (заглушка + поддержка ACME webroot).
- Certbot с автообновлением сертификатов и deploy-хук для перезагрузки Xray.
- Плашки протоколов в списке серверов.
- Перегенерация API-токена сервера.

### Changed
- `XrayServer` переработана: добавлены связи `protocols()`, `enabledProtocols()`, хелперы `protocol()`, `hasProtocol()`.
- VMess-ссылки генерируются в формате **VMessAEAD URI** (`vmess://uuid@host:port?params#name`) вместо legacy `vmess://Base64(JSON)`.
- `policy.levels` в конфиге Xray сериализуется как объект (не массив) — иначе статистика по клиентам не работает.
- `XrayConfigController@show` использует новый `ConfigBuilder`.
- `ClientController@config` возвращает `links: { vless, vmess, trojan }` вместо отдельных `vless_link` / `vmess_link`.
- Сертификаты Let's Encrypt выпускаются на поддомены (`fr.funny-code.space`, `nl.funny-code.space`) на самих серверах.

### Removed
- Старые колонки из `xray_servers`: `port`, `protocol`, `inbound_tag`, `network`, `security`, `flow`, `alter_id`, `reality_dest`, `reality_server_names`, `reality_private_key`, `reality_public_key`, `reality_short_ids`, `fingerprint`, `ws_path`.
- Методы `addUser` / `removeUser` из `XrayService` (в roadmap через gRPC).

### Fixed
- Xray API (порт 10085) — добавлен inbound и routing-правило, статистика теперь собирается.
- Пагинация на странице ключей.
- Корректный `path` для VMess в клиентских ссылках.

### Deprecated
- Xray помечает Trojan, VMess и WebSocket как deprecated в пользу VLESS + TLS и XHTTP. В roadmap — переход на VLESS + TLS вместо VMess.

## [1.0.0] - 2026-09-15

### Added
- Регистрация с модерацией заявок (`pending` / `approved` / `rejected`).
- Логин / логаут через Laravel Sanctum.
- Смена пароля и восстановление через email.
- Блокировка / разблокировка пользователей с деактивацией всех ключей.
- Роли (`admin` / `user`) через таблицу `roles` + pivot `role_user`.
- CRUD ключей доступа с автогенерацией UUID и email.
- Привязка ключа к конкретному Xray-серверу.
- Генерация `vless://` ссылок (VLESS + Reality).
- QR-код для подключения.
- Копирование ссылки в буфер обмена.
- Мульти-серверная модель (`xray_servers`).
- Генерация полного конфига Xray из БД (`XrayConfigBuilder`).
- Endpoint `GET /api/xray/config` с Bearer-токеном для серверов.
- Docker-контейнер Xray с автозагрузкой конфига.
- Агент hot-reload через `SIGHUP`.
- Чтение статистики через `xray api statsquery`.
- Синхронизация трафика по email (`xray:sync-traffic`).
- Таблица `traffic_stats` — дневная разбивка трафика.
- Лента новостей с HTML-контентом.
- Теги с цветами и описанием (`updates`, `incidents`, `maintenance`, `news`, `guides`).
- Публичная лента `/news` с фильтром по тегам.
- Подписки на email (все новости или конкретные теги).
- Админ-панель: дашборд, заявки, пользователи, посты, теги, серверы.
- Лендинг для неавторизованных с публичным списком серверов.
- FAQ, стек, roadmap на лендинге.
- Neo-brutalism дизайн: жёлтый акцент, жёсткие тени, острые углы.
- Светлая и тёмная темы.
- Адаптив под мобильные.

### Security
- SSH-ключи Ed25519 на всех серверах.
- Вход по паролю отключён.
- UFW на всех серверах.
- API Xray и агент — только с IP панели.
- Sanctum-токены для API.

[Unreleased]: https://github.com/Funny-Code-d/xray-panel/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/Funny-Code-d/xray-panel/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/Funny-Code-d/xray-panel/releases/tag/v1.0.0
