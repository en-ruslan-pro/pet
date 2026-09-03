# Установка Laravel Reverb

## Цель

Установить и настроить Laravel Reverb как WebSocket-транспорт для следующего этапа Virtual Pet TV.

## Ограничения и критерии готовности

- Использовать совместимую с Laravel 13 версию Reverb.
- Включить broadcasting и маршруты авторизации каналов.
- Не раскрывать ключи Reverb в документации.

## Этапы работы

1. **Готово:** установлен `laravel/reverb` 1.11.1 и опубликована конфигурация Reverb и broadcasting.
2. **Готово:** подключён файл маршрутов каналов в bootstrap-конфигурации приложения; сгенерированы локальные параметры Reverb.
3. **Готово:** проверена загрузка Laravel с новой конфигурацией.

## Результат и проверка

- Изменённые области: `composer.json`, `composer.lock`, `bootstrap/app.php`, `config/broadcasting.php`, `config/reverb.php`, `routes/channels.php`, локальный `.env`.
- Composer согласованно понизил `guzzlehttp/guzzle`, `guzzlehttp/promises` и `guzzlehttp/psr7` до совместимых с Reverb версий.
- Проверки: `php artisan about --only=environment`, `APP_ENV=testing php artisan config:show broadcasting.default`, `php artisan reverb:start --help`, `vendor/bin/pint config/broadcasting.php config/reverb.php routes/channels.php bootstrap/app.php --format agent` и `git diff --check` завершились успешно.
- Перед запуском realtime-функций Reverb нужно держать сервер запущенным через `php artisan reverb:start`.
