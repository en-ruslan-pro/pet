# Документация Virtual Pet TV

## Первичное развёртывание

Выполните команды из корня приложения после настройки production-переменных в `.env`:

```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --class=PetCatalogSeeder --force
php artisan optimize:clear
php artisan optimize
```

`PetCatalogSeeder` создаёт каталог питомцев, моделей и действий.

### Пользователь Filament

Создайте пользователя интерактивно — так пароль не попадёт в историю команд:

```bash
php artisan make:filament-user --panel=dostup
```

Затем назначьте ему роль администратора, подставив ID созданного пользователя:

```bash
php artisan shield:super-admin --panel=dostup --user=<user-id>
```

Команда создаёт роль `admin`, назначает её пользователю и синхронизирует разрешения Shield. Панель будет доступна по адресу `${APP_URL}/dostup`. Не запускайте `DatabaseSeeder` на production: он создаёт тестового пользователя.

Если предыдущий запуск этой версии миграции завершился ошибкой, сначала проверьте состояние production-схемы и таблицы `migrations`. Не запускайте команды вслепую поверх частично созданных таблиц.

## План разработки

Подробная последовательность создания продукта — от автономного 3D-прототипа до полноценного виртуального питомца — описана в [плане разработки](development/plan.md).

## 3D-модели комнаты

Библиотека GLB-ассетов для интерьера находится в [каталоге моделей комнаты](assets/room-models.md). Там приведены пути, состав набора и рекомендации по подключению в сцену.
