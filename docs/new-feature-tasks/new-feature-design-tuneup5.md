пока забей на MCP, не работает.
1. zx-filter: сделай чтоб больше выглядел как селект когда свернут. сейчас стрелка крупновата (сравни) и цвет текста светловат (сравни)
2. zx-filter: строчка с выбором не полностью кликабельна, надо целиться в текст или чекбокс. сделай полностью.
3. mobilemenu_closeicon в светлой теме сделай темным.
4. плеер: на мобилке краткий виде не лезет по ширине во вьюпорт. подели элементы на группы и пусть на своих строках будут.
5. в разделе с комментариями есть пагинация, я выбираю вторую страницу и ничего не показывается. на бэке ошибка:
[2026-02-13T13:51:31.390402+02:00] error_log.ERROR: [2026-02-13T13:51:31+02:00] [ERROR] Comments::handleList: Comment 573426 has no author
#0 E:\projects\zxart\project\core\ZxArt\Comments\CommentsService.php(76): ZxArt\Comments\CommentsTransformer->transformToDto(Object(commentElement))
#1 E:\projects\zxart\project\core\ZxArt\Controllers\Comments.php(62): ZxArt\Comments\CommentsService->getAllCommentsPaginated(2)
#2 E:\projects\zxart\project\core\ZxArt\Controllers\Comments.php(41): ZxArt\Controllers\Comments->handleList()
#3 E:\projects\zxart\trickster-cms\cms\core\controller.class.php(262): ZxArt\Controllers\Comments->execute(Object(controller))
#4 E:\projects\zxart\htdocs\index.php(23): controller->dispatch()
#5 {main} | IP: 127.0.0.1 | Referer: http://zxart.loc/rus/kommentarii/ | URL: http://zxart.loc/comments/?action=list&page=2  
