# 3D-модели комнаты

Набор моделей интерьера хранится в `public/models/room`. Это GLB-файлы, доступные приложению по URL `/models/room/<имя-файла>.glb`.

## Состав

| Категория | Модели |
| --- | --- |
| Кровати | `double_bed`, `single_bed`, `simple_double_bed`, `simple_single_bed` |
| Столы и рабочее место | `coffee_table`, `high_table`, `simple_desk_A`, `simple_desk_B`, `simple_rect_table_A`, `simple_rect_table_B`, `simple_square_table_A`, `simple_square_table_B`, `bedside_table` |
| Сиденья | `armchair`, `couch`, `simple_chair`, `simple_footstool` |
| Хранение и настенный декор | `shelf`, `simple_library_A`, `simple_library_B`, `simple_library_C`, `frames` |
| Напольный и живой декор | `carpet`, `rug`, `lamp`, `plant`, `pot`, `cactus` |

Каждое имя из таблицы соответствует файлу с суффиксом `.glb`, например: `/models/room/couch.glb`.

## Использование в демо

Страница `/demo` использует модели `frames`, `couch`, `armchair`, `simple_library_A`, `lamp` и `plant`. Они заменяют процедурные заглушки интерьера; пол и стены остаются геометрией сцены. Модель `frames` используется как декоративные рамки.

## Подключение

Используйте `GLTFLoader` из Three.js и загружайте файлы по публичному пути:

```js
loader.load('/models/room/couch.glb', ({ scene }) => {
    room.add(scene);
});
```

Масштаб, положение и поворот задавайте после загрузки: модели поставляются как отдельные предметы и не образуют готовую комнату. Перед включением модели в основной TV-сценарий следует визуально проверить её масштаб и коллизию с персонажем.

## Источник и лицензирование

Файлы импортированы из предоставленного набора `furniture_pack_update_2-1.0/models/gltf_format`. В полученной папке не было отдельного файла лицензии или атрибуции; перед публикацией продукта необходимо подтвердить условия использования у источника набора.
