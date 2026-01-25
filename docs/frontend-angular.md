### Интеграция Angular в Legacy (Smarty)

Интеграция Angular компонентов в существующие легаси-шаблоны Smarty реализована через Custom Elements (Web Components).

#### Основные принципы
1. **Custom Elements**: Angular компоненты регистрируются в `AppModule` как кастомные элементы с префиксом `app-`. Это позволяет использовать их как обычные HTML-теги в `.tpl` файлах.
2. **Передача данных**:
    - **Attributes**: ID элемента и простые настройки передаются через атрибуты тега (например, `element-id="{$element->id}"`). Атрибуты в Angular компоненте принимаются через `@Input()`.
    - **Prefetched Data**: Для исключения лишних HTTP-запросов данные могут передаваться через глобальный объект `window.elementsData`. В шаблоне Smarty это выглядит так:
      ```html
      <script>
          window.elementsData = window.elementsData ? window.elementsData : { };
          window.elementsData[{$element->id}] = {$element->getJsonInfo('presetName')};
      </script>
      <app-component element-id="{$element->id}"></app-component>
      ```
      В Angular сервисе `ElementsService.getPrefetchedModel` эти данные считываются и преобразуются в модели.

#### Роутинг и навигация
На данный момент за роутинг отвечает легаси-часть системы. При переходе по ссылкам происходит полная перезагрузка страницы браузером. Angular компоненты инициализируются "на лету" при загрузке страницы, если соответствующий тег присутствует в отрендеренном HTML.

#### Сборка и проверка
После внесения любых изменений в Angular-часть проекта (`ng-zxart`), необходимо:
1. Выполнить сборку проекта: `npm run build` (находясь в директории `ng-zxart`).
2. Убедиться, что сборка прошла без ошибок.
3. Проверить результат в браузере.

#### Пример интеграции комментариев
Для интеграции нового списка комментариев используется тег `<app-comments-list>` в соответствующих детальных шаблонах (например, `zxProd.details.tpl`):

```html
<app-comments-list element-id="{$element->id}"></app-comments-list>
```

Компонент самостоятельно запрашивает данные с бэкенда по предоставленному `element-id` через `CommentsService`. Старый механизм комментариев через `{include file=$theme->template('component.comments.tpl')}` в публичных шаблонах больше не используется.
