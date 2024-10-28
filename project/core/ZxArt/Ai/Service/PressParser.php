<?php
declare(strict_types=1);

namespace ZxArt\Ai\Service;

use JsonException;
use ZxArt\Ai\ChunkProcessor;

readonly class PressParser
{
    public function __construct(
        private ChunkProcessor $chunkProcessor,
    )
    {
    }

    public function getParsedData(string $text): ?array
    {
        $createPrompt = static function (string $chunk): string {
            return "Я отправлю тебе текст статьи из журнала/газеты для ZX Spectrum.
* Перескажи статью В ТРИ ПРЕДЛОЖЕНИЯ (поле Short Content). Читателю должно быть понятно, о чем статья. Понимай правильно опечатки при чтении. Укажи, если это новелла, описание, мануал, реклама итд. 
* Если в статье ЯВНО указано, кто написал текст статьи, то укажи их информацию (поле articleAuthors). Если nickname нет, то укажи реальное имя. Если имя автора статьи неясно, не пиши поле articleAuthors в ответе. Укажи  город, страну и демогруппу/фирму, если это понятно из статьи.
Формат AuthorObj: {realName?:'', nickName?:'',city?:'',country?:'', group?:[GroupObj],roles?:[Role]}
Используй ТОЛЬКО ТАКИЕ значения Role:code,music,support,testing,graphics,loading_screen,intro_code,intro_graphics,intro_music,design,logo,font,ascii,localization,concept,text,editing.            
Не сочиняй realName, он не обязательное. Укажи его в тех случаях, когда имя и фамилия выглядят как реальные и общепринятые имена. Если псевдоним не соответствует этим критериям, оставляй поле пустым. Псевдонимы в поле realName использовать не следует. Используй формат \"Имя Фамилия\" в полной форме - например, вместо \"sasha\" пиши \"Александр\", вместо Misha - Михаил, итд.
Если реальное имя указано как часть псевдонима, то используй это имя в поле realName, а псевдоним укажи отдельно в поле nickName.
Если про человека известно только имя собственное, без фамили ИЛИ без группы, не указывай его.
* Если в статье указаны авторы всего ЖУРНАЛА или ГАЗЕТЫ, то собери их в pressArticles.
* Если в статье указаны группы,фирмы выпустившее САМ ЖУРНАЛ или ГАЗЕТу, то собери их в pressGroups.
* Собери упомянутые демопати (поле parties). Укажи год, город, страну, если это ЯВНО указано в статье. Формат PartyObj: {name: '', city?: '', country?: '', year: ''}
* Собери ВСЕ упомянутые программы, игры и демо. Формат SoftwareObj: {name: '', authors?: [AuthorObj], groups?: [GroupObj], publishers?: [GroupObj], year?: 2002}
* В названии программ НЕ ИСПОЛЬЗУЙ версии. 'Program v1.1' - это ПЛОХО. 'Program' - это ПРАВИЛЬНО.   
* Собери ВСЕ упомянутые аппаратные расширения ZX-Spectrum - модемы, звуковые карты, расширения графики итд. Формат HardwareObj: {name: '', authors?: [AuthorObj], groups?: [GroupObj], year?: 2002}
* Собери ВСЕХ упомянутых в статье группы/фирмы ZX-Spectrum в формате GroupObj в поле groups. Формат GroupObj: {name: '', city?: '', country?: ''}
* Собери ВСЕХ упомянутых отдельных людей в формате AuthorObj в поле people. Не пиши селебрити типа Билла Гейтса. Если человек связан с группой или фирмой по контексту, то укажи группу в его данных. 
* Собери упомянутые мелодии ZX-Spectrum в формате TuneObj в поле tunes. Формат TuneObj: {name: '', authors?: [AuthorObj], year?: 2002}
* Собери упомянутые картинки ZX-Spectrum в формате PictureObj в поле pictures. Формат PictureObj: {name: '', authors?: [AuthorObj], year?: 2002}
* Собери 10 наиболее значимых тегов из фактов и темы статьи. Пример хороших тегов: \"Обзор\", \"Демопати\", \"Критика\", \"Графика\", \"Демосцена\", \"Техника рисования\"
* заполни поле publicationYear ТОЛЬКО если в статье ЯВНО указана дата публикации самого ЖУРНАЛА или ГАЗЕТЫ. НЕ УГАДЫВАЙ.
* Сгенерируй реально полезные SEO поля для этой статьи на трех языках. Не переводи названия софта и псевдонимы авторов, но делай читабельные названия категорий (игры, демо, программы).
* краткое описание meta description с важными параметрами (155-160 символов), его показывают в результатах поиска поисковики.
* page title будет показан посетителю в поисковике (30-60 символов). Он должен подходить под требования поисковиков и содержать правильные ключевики для ранжирования.
* h1 будет показан посетителю уже на сайте, это главный заголовок над текстом (до 70 символов). Он должен быть человекопонятным и сразу дать кратко понять, что это за статья. H1 должен отличаться от page title.
* НЕ ПИШИ СВОЙСТВА ОБЪЕКТОВ, если они ПУСТЫЕ '', \"\", [] или null.
* Используй сухой научный язык. НИКОГДА НЕ ИСПОЛЬЗУЙ call to action и кликбейтов. Не пиши \"Изучите события и размышления о сцене\". Пиши просто \"События и размышления о сцене\".
* Пати, party - это демопати, demoparty.
* Указывай год программ ТОЛЬКО если он написан в тексте ТОЧНО.
* Поля city и country заполнять только в случаях, если текст явно обозначает место происхождения группы или автора как постоянное место их деятельности.
* В ответе не пиши ничего лишнего, ТОЛЬКО JSON в формате:
{
shortContent:[eng:'', spa:'', rus:''],
pressAuthors:[AuthorObj],
pressGroups:[GroupObj],
articleAuthors:[AuthorObj],
groups:[GroupObj],
people:[AuthorObj],
parties:[PartyObj],
tunes:[TuneObj],
pictures:[PictureObj],
software:[SoftwareObj],
hardware:[HardwareObj],
tags: ['tag1'],
publicationYear:2000
h1:[eng:'', spa:'', rus:''],
metaDescription:[eng:'', spa:'', rus:''],
pageTitle:[eng:'', spa:'', rus:''],
}
Кусок статьи:{$chunk}";
        };

        /**
         * @throws JsonException
         */
        $processResponse = static function(string $response): array {
            return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        };

        return $this->chunkProcessor->processJson(
            $text,
            $createPrompt,
            $processResponse,
            1,
            null,
            true,
            PromptSender::MODEL_4O
        );
    }
}
