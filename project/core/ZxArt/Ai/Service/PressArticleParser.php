<?php
declare(strict_types=1);

namespace ZxArt\Ai\Service;

use JsonException;
use ZxArt\Ai\ChunkProcessor;

readonly class PressArticleParser
{
    public function __construct(
        private ChunkProcessor $chunkProcessor,
    )
    {
    }

    public function getParsedData(string $text, string $pressTitle, ?int $pressYear): ?array
    {
        $createPrompt = static function (string $chunk) use ($pressTitle, $pressYear): string {
            return "Отправляю тебе текст статьи из журнала/газеты для ZX Spectrum.
* В persons собери всех людей из других полей в формате полного объекта AuthorObj.
    Для ролей человека в группе используй ТОЛЬКО ТАКИЕ значения GroupRole:coder,cracker,graphician,hardware,musician,organizer,support,tester,gamedesigner. У автора может быть несколько ролей.            
    Не сочиняй realName, оставь пустым, если есть сомнения. Укажи его в тех случаях, когда имя и фамилия выглядят как реальные и общепринятые имена. Если псевдоним не соответствует этим критериям, оставляй поле пустым. Псевдонимы в поле realName использовать не следует. Используй формат \"Имя Фамилия\" в полной форме - например, вместо \"sasha\" пиши \"Александр\", вместо Misha - Михаил, итд.
    Если реальное имя указано как часть псевдонима, то используй это имя в поле realName, а псевдоним укажи отдельно в поле nickName.
    Если про человека известно только имя собственное, без фамилии ИЛИ без группы, не указывай его.
    в AuthorId положи уникальный признак (имя), его же используй в AuthorObj->id
* В groups собери всех людей из других полей в формате полного объекта AuthorObj.
    В GroupId положи уникальный признак (имя), его же используй в GroupObj->id
    Допустимые типы групп: GroupType:'unknown'|'company'|'crack'|'studio'|'scene'|'education'|'store'|'science'. У группы может быть только один GroupType. 
    Если группа указана в формате 'Group1/Group2' или 'Group1/Group2/Group3', to Group2 - это родительская группа, её id укажи в поле parentGroupIds. А Group3 - родительская группа для Group2, ё id укажи в parentGroupIds в объекте Group2. 
* Собери ВСЕ упомянутые в статье группы/фирмы ZX-Spectrum в формате GroupId в поле mentionedGroupIds.     
* Если в статье ЯВНО указано, кто написал текст статьи, то собери их id в поле articleAuthors. Если nickname нет, то укажи реальное имя. Если имя автора статьи неясно, не пиши поле articleAuthors в ответе. Укажи  город, страну и демогруппу/фирму, если это понятно из статьи.
* Если в статье указаны авторы всего ЖУРНАЛА или ГАЗЕТЫ, то собери их id в pressAuthors.
* Если в статье указаны группы, фирмы выпустившее САМ ЖУРНАЛ или ГАЗЕТУ, то собери их id в pressGroups.
* Собери упомянутые демопати в поле parties в формате PartyObj. Если в статье обзор работ с конкурса с демопати, укажи это демопати обязательно. Укажи год, город, страну демопати если это ЯВНО указано в статье, иначе не указывай их в объекте. 
Не выбрасывай год демопати из названия, если оно является частью названия.
* Собери ВСЕ упомянутые системные программы, игры и демо в поле software. Музыкальные ТРЕКИ и картинки с конкурсов НЕ ОТНОСЯТСЯ к software.
    В названии программ НЕ ИСПОЛЬЗУЙ версии. 'Program   1.1' - это ПЛОХО. 'Program' - это ПРАВИЛЬНО. Убери версию из software name, если она там есть.  
    В поле authorship заведи массив, элементы которого состоят из ID автора-персоны, для ролей автора в программе используй ТОЛЬКО ТАКИЕ значения SoftwareRole:code,music,support,testing,graphics,loading_screen,intro_code,intro_graphics,intro_music,design,logo,font,ascii,localization,concept,text,editing.            
* Не переделывай названия в латиницу.
* Собери ВСЕ упомянутые аппаратные расширения ZX-Spectrum - модемы, звуковые карты, расширения графики итд в поле hardware.
* Собери id ВСЕХ упомянутых в статье людей в поле mentionedPersonIds. Не пиши селебрити типа Билла Гейтса. Если человек связан с группой или фирмой по контексту, то укажи группу в его данных. В этот список включи всех, кто хотя бы отдаленно имеет отношение к ZX Spectrum или кто участвовал в демосцене хотя бы как зритель, или чьё имя похоже на Nickname / Group.  
* Собери упомянутые мелодии, треки, музыку ZX-Spectrum в формате MusicTrackObj в поле music. Мелодии (треки) могут обозреваться на конкурсах (компо) с пати (демопати), не пропусти их.
* Собери упомянутые картинки, графику, заставки и пиксельарт ZX-Spectrum в формате PictureObj в поле pictures. Картинки (gfx) могут обозреваться на конкурсах (компо) с пати (демопати), не пропусти их.
* заполни поле publicationYear ТОЛЬКО если в статье ЯВНО указана дата публикации самого ЖУРНАЛА или ГАЗЕТЫ. НЕ УГАДЫВАЙ.
* НЕ ПИШИ СВОЙСТВА ОБЪЕКТОВ, если они ПУСТЫЕ '', \"\", [] или null. Пиши объект, если хотя бы одно поле не пустое.
* Пати, party - это демопати, demoparty.
* Указывай год программ ТОЛЬКО если он написан в тексте ТОЧНО.
* Поля city и country заполнять только в случаях, если текст явно обозначает место происхождения группы или автора как постоянное место их деятельности.
* Типы полей:
AuthorObj: {id: 'generate_unique', realName?:'', nickName?:'', city?:'',country?:'', groupIds?:[GroupId], groupRoles?:[GroupRole]}
GroupObj: {id: 'generate_unique', name: '', city?: '', country?: '', type?: GroupType, parentGroupIds?: [GroupId]}
PartyObj: {name: '', city?: '', country?: '', year: ''}
AuthorshipObj: {id: AuthorId, roles?: [SoftwareRole]}
SoftwareObj: {name: '', authorship?: [AuthorshipObj], groupIds?: [GroupId], publisherIds?: [GroupId], year?: 2002}
HardwareObj: {name: '', authorIds?: [AuthorId], groupIds?: [GroupId], year?: 2002}
MusicTrackObj: {name: '', authorIds?: [AuthorId], year?: 2002}
PictureObj: {name: '', authorIds?: [AuthorId], year?: 2002}
* В ответе не пиши ничего лишнего, ТОЛЬКО JSON в формате:
{
groups:[GroupObj],
persons:[AuthorObj],

parties?:[PartyObj],
music?:[MusicTrackObj],
pictures?:[PictureObj],
software?:[SoftwareObj],
hardware?:[HardwareObj],

pressGroupIds?:[GroupId],
pressAuthorIds?:[AuthorId],
mentionedPersonIds:[AuthorId],
mentionedGroupIds:[GroupId],
articleAuthorIds?:[AuthorId],

publicationYear?:int
}

Название издания:{$pressTitle}
Год выпуска издания:{$pressYear}
Кусок статьи:{$chunk}";
        };

        /**
         * @throws JsonException
         */
        $processResponse = static function (string $response): array {
            return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        };

        return $this->chunkProcessor->processJson(
            $text,
            $createPrompt,
            $processResponse,
            0.1,
            null,
            true,
            PromptSender::MODEL_4O
        );
    }
}
