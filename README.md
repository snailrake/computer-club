# Computer Club

Учебное веб-приложение для бронирования компьютеров в компьютерном клубе Саратова.

![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![Blade](https://img.shields.io/badge/Views-Blade-F9322C)
![SQLite](https://img.shields.io/badge/Database-SQLite-003B57?logo=sqlite&logoColor=white)

Серверный MVC: Laravel обрабатывает запросы, Eloquent работает с базой данных, Blade формирует HTML. Стили подключены напрямую.

## Возможности

- Каталог компьютеров с характеристиками и ближайшими занятыми интервалами.
- Регистрация, вход и выход; роли клиента и администратора.
- Полный CRUD компьютеров для администратора.
- Создание, просмотр, изменение и удаление бронирований с проверкой прав.
- Проверка периода, активности компьютера и пересечений бронирований.
- Защита от двойного бронирования при конкурентных запросах через транзакции SQLite `IMMEDIATE`.
- Ввод и отображение времени по Саратову (`Europe/Saratov`); хранение в UTC.
- Адаптивный интерфейс, серверная валидация и понятные сообщения об ошибках.

## Первый запуск

Нужны **PHP 8.5**, **Composer 2** и **Git**. В PHP должны быть доступны PDO SQLite, mbstring и стандартные расширения Laravel. Composer проверит требования при установке; для тестов также нужны DOM и XML.

В терминале:

```sh
git clone https://github.com/snailrake/computer-club.git
cd computer-club
composer setup
php artisan serve
```

Открыть: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**.

`composer setup` устанавливает версии библиотек из `composer.lock`, создаёт локальный `.env`, генерирует ключ приложения, создаёт SQLite-базу, применяет миграции и добавляет демонстрационные данные.

При последующих запусках достаточно `php artisan serve`.

Локальные настройки, рабочая база, зависимости и файлы PhpStorm исключены из Git. На другом компьютере создаётся своя база с демонстрационными аккаунтами и тремя компьютерами. Бронирования можно оформить через интерфейс.

## Демонстрационные аккаунты

Пароль для всех трёх: **`ClubDemo2026!`**

| Роль | Email | Права |
|---|---|---|
| Администратор | `admin@club.test` | Управление компьютерами, просмотр всех броней, перенос будущих броней, удаление любых броней |
| Клиент | `client@club.test` | Создание и просмотр своих броней, перенос и отмена до начала |
| Второй клиент | `second@club.test` | Та же роль; удобно проверять защиту чужих записей |

Регистрация создаёт только клиента. Компьютер с бронированиями нельзя удалить: можно отключить приём новых броней, сохранив существующие.

## Открыть в PhpStorm

1. Открыть корневую папку `computer-club`, где находится `composer.json`.
2. В **Settings → PHP → CLI Interpreter** выбрать установленный PHP 8.5.
3. При необходимости указать **Settings → PHP → Composer → Path to composer.json**: файл из корня проекта.
4. Выполнять команды запуска во встроенном терминале.

## Где находится код

| Часть | Расположение |
|---|---|
| Веб-маршруты | [routes/web.php](routes/web.php) |
| Контроллеры | [app/Http/Controllers](app/Http/Controllers) |
| Модели Eloquent и связи | [app/Models](app/Models) |
| Проверка входных данных | [app/Http/Requests](app/Http/Requests) |
| Правила доступа | [app/Policies](app/Policies) |
| Транзакция и проверка пересечений | [BookingService.php](app/Services/BookingService.php) |
| Схема БД и демонстрационные данные | [database](database) |
| Blade-шаблоны | [resources/views](resources/views) |
| Стили | [public/styles.css](public/styles.css) |
| Тесты | [tests/Feature](tests/Feature) |

## Проверка

```sh
composer test
```

Тесты используют отдельные базы и проверяют CRUD, аутентификацию, права, валидацию дат, преобразование часового пояса, конкурентное бронирование и обработку блокировки SQLite.

Проверка стиля собственных PHP-исходников:

```sh
php vendor/bin/pint --test --preset psr12 app bootstrap/app.php bootstrap/providers.php config database lang routes tests public/index.php artisan
```
