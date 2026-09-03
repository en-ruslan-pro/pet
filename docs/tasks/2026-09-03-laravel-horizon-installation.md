# Установка Laravel Horizon

## Цель

Установить Laravel Horizon для мониторинга и обработки Redis-очередей.

## Ограничения и критерии готовности

- Использовать версию, совместимую с Laravel 13.
- Настроить Horizon для Redis-очереди без добавления секретов в репозиторий.
- Проверить доступность команды Horizon и конфигурации в тестовом окружении.

## Этапы

1. **Готово:** установлен `laravel/horizon` 5.48.3, опубликованы конфигурация и провайдер, а очередь в локальном и примерном окружении переключена на Redis.
2. **Готово:** добавлен снимок метрик `horizon:snapshot` каждые пять минут.
3. **Готово:** подтверждены конфигурация, маршруты панели, расписание и загрузка приложения в тестовом окружении.

## Результат и проверка

- Изменённые области: `composer.json`, `composer.lock`, `bootstrap/providers.php`, `app/Providers/HorizonServiceProvider.php`, `config/horizon.php`, `routes/console.php`, `.env.example`, локальный `.env`, `boost.json` и навыки Laravel Boost.
- Horizon использует Redis и запускает до трёх процессов в локальном окружении; production-конфигурация допускает до десяти.
- Проверки пройдены: `APP_ENV=testing php artisan config:show horizon`, `php artisan route:list --name=horizon`, `APP_ENV=testing php artisan schedule:list`, `APP_ENV=testing php artisan test --compact tests/Feature/ExampleTest.php`, `vendor/bin/pint --dirty --format agent`, `git diff --check`.
- `php artisan horizon:status` успешно подключился к локальному Redis и подтвердил, что Horizon пока не запущен. Для обработки задач нужно запустить `php artisan horizon`; для плановых снимков метрик — планировщик Laravel.
