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

    public function getParsedData(string $text, int $articleId, string $pressTitle, ?int $pressYear): ?array
    {
        $createPrompt = static function (string $chunk) use ($pressTitle, $pressYear): string {
            return "Отправляю тебе через АПИ текст статьи из журнала/газеты для ZX Spectrum. Сделай всё правильно с первой попытки, так как я не смогу проверить и указать на ошибки. 
* В persons собери всех людей из других полей в формате полного объекта AuthorObj.
    Для ролей человека в команде используй ТОЛЬКО ТАКИЕ значения TeamRole:'coder'|'cracker'|'graphician'|'hardware'|'musician'|'organizer'|'support'|'tester'|'gamedesigner'. У автора может быть несколько ролей.
    Не сочиняй realName, оставь пустым, если есть сомнения. Укажи его в тех случаях, когда имя и фамилия выглядят как реальные и общепринятые имена. Если псевдоним не соответствует этим критериям, оставляй поле пустым. Псевдонимы в поле realName использовать не следует. Используй формат \"Имя Фамилия\" в полной форме - например, вместо \"sasha\" пиши \"Александр\", вместо Misha - Михаил, вместо Макс - Максим итд.
    НЕ ПИШИ ОТЧЕСТВО.
    Если реальное имя указано как часть псевдонима, то используй это имя в поле realName, а псевдоним укажи отдельно в поле nickName.
    Если про человека известно только имя собственное, без фамилии ИЛИ без команды, не указывай его.
    Укажи город, страну и команду/демогруппу/фирму, если это понятно из статьи.
    в AuthorId положи уникальный признак (имя), его же используй в AuthorObj->id.
* В teams собери все команды людей из других полей в формате полного объекта TeamObj. 
Включай в teams только те объекты, которые описаны в статье как объединения людей (например, demoteams, студии, компании, фирмы, группы). Если объект назван как издание, проект, продукт, газета, журнал или публикация — это не команда и не добавляется в teams, даже если подразумевается коллективная работа. Убедись, что группа описана именно как коллектив людей.        
Учитывай, что названия изданий, газет, журналов, публикаций, проектов, концепций и продуктов (например, Lamergy, Adventurer) не являются командами, даже если подразумевается коллективная работа. Включай в команды только те объекты, которые явно описаны как группы людей или демосцены (например, Perspective Group или CPU).
В TeamId положи уникальный признак (имя), его же используй в TeamObj->id.
Допустимые типы команд: TeamType:'unknown'|'company'|'crack'|'studio'|'scene'|'education'|'store'|'science'. У команды может быть только один TeamType. 
Люди в статье иногда указаны вместе с командой. MyAuthor^MyTeam означает, что автор состоит напрямую в MyTeam.
MyAuthor/MyTeam/MegaTeam - означает, что MyAuthor состоит напрямую в MyTeam, а MyTeam входит в состав MegaTeam. MyTeam тогда ДОЛЖНА иметь ID родительской команды у себя {id:'myteam',parentTeamIds:['megateam']}
MyAuthor/MyTeam/MegaTeam&MyAssoc - означает, что MyTeam входит в MegaTeam и MyAssoc, но MegaTeam и MyAssoc никак не связаны друг с другом. MyTeam тогда ДОЛЖНА иметь ID родительских команд у себя {id:'myteam',parentTeamIds:['megateam', 'myassoc']}   
Например, Slider/Bis/ASM&RUSH: Slider - персона. Bis - его команда. Bis находится в составе родительских крупных команд ASM и Rush.    
* Собери ВСЕ упомянутые в статье команды/фирмы/демогруппы ZX-Spectrum в формате TeamId в поле mentionedTeamIds.     
* Если в статье ЯВНО указано, кто написал текст статьи, то собери их id в поле articleAuthorIds. Если nickname нет, то укажи реальное имя. Если в статье в самом начале отдельно написано имя автора с (C), то это автор статьи. Например, (C) Welcome & TP 1997 - авторы статьи Welcome и TP, написано в 1997 году.
* Если в статье указаны авторы всего ЖУРНАЛА или ГАЗЕТЫ, то собери их id и роли (если есть) в pressAuthorship в формате AuthorshipObj и SoftwareRole. Если в статье указан автор музыки к статье, добавь его в авторы издания в pressAuthorship. Музыку всегда пишет человек, не команда. Music by Midisoft в начале статьи означает, что автор музыки Midisoft, и это человек, а не команда.
* Если в статье указаны команды, фирмы выпустившее САМ ЖУРНАЛ или ГАЗЕТУ, то собери их id в pressTeams.
* Собери упомянутые демопати в поле parties в формате PartyObj. Если в статье обзор работ с конкурса с демопати, укажи это демопати обязательно. Укажи год, город, страну демопати если это ЯВНО указано в статье, иначе не указывай их в объекте. 
Не выбрасывай год демопати из названия, если оно является частью названия.
* Если в названии команды, автора, пати или софта есть апостроф с цифрами в конце, то распознавай это как год в отдельном поле year. В названии год убирай. Например, Dihalt'99 - это Dihalt, который был в 1999 году. Например, RUSH'98 - это продукция команды Rush, выпущенная в 1998 году. 
* Собери ВСЕ упомянутые системные программы, игры, демо, издания, журналы и газеты в поле software. Музыкальные ТРЕКИ и картинки с конкурсов НЕ ОТНОСЯТСЯ к software. Все упомянутые журналы и газеты - электронные, поэтому они относятся к software.
    В названии программ НЕ ИСПОЛЬЗУЙ версии. 'Program   1.1' - это ПЛОХО. 'Program' - это ПРАВИЛЬНО. Убери версию из software name, если она там есть.
    В названии журналов и газет МОЖЕТ быть номер, сохраняй номер если он есть. Например, Adventurer #03 - это полное и правильное название конкретного журнала.   
    В поле authorship заведи массив, элементы которого состоят из ID автора-персоны, для ролей автора в программе используй ТОЛЬКО ТАКИЕ значения SoftwareRole:'code'|'music'|'support'|'testing'|'graphics'|'loading_screen'|'intro_code'|'intro_graphics'|'intro_music'|'design'|'logo'|'font'|'ascii'|'localization'|'concept'|'text'|'editing'|'gamedesign'.
    Если у программы нет авторов, всё равно добавь её.
    Если в статье упоминается проект игры, демо или журнала, даже если он не завершён или упоминается в контексте слухов, добавляй его в software как отдельный объект.
* Не переделывай названия в латиницу.
* Собери ВСЕ упомянутые аппаратные расширения ZX-Spectrum - модемы, звуковые карты, расширения графики итд в поле hardware.
* Собери id ВСЕХ упомянутых в статье людей в поле mentionedPersonIds. Не пиши селебрити типа Билла Гейтса. Если человек связан с командой или фирмой по контексту, то укажи команду в его данных. Если человек не связан с командой, всё равно его упомяни. В этот список включи всех, кто хотя бы отдаленно имеет отношение к ZX Spectrum или кто участвовал в демосцене хотя бы как зритель, или чьё имя похоже на Nickname / Team.  
* Собери упомянутые мелодии, треки, музыку ZX-Spectrum в формате MusicTrackObj в поле music. Мелодии (треки) могут обозреваться на конкурсах (компо) с пати (демопати), не пропусти их.
* Собери упомянутые картинки, графику, заставки и пиксельарт ZX-Spectrum в формате PictureObj в поле pictures. Картинки (gfx) могут обозреваться на конкурсах (компо) с пати (демопати), не пропусти их.
* Заполни поле publicationYear ТОЛЬКО если в статье ЯВНО указана дата публикации самого ЖУРНАЛА или ГАЗЕТЫ. НЕ УГАДЫВАЙ. Если в статье про авторов или credits указан год в стиле (c) 2002, то это и есть publicationYear. 
* Пати, party - это демопати, demoparty.
* Указывай год программ ТОЛЬКО если он написан в тексте ТОЧНО, иначе не упоминай это в объекте. Если год есть, то указывай его четырехзначным.
* Поля city и country заполнять только в случаях, если текст явно обозначает место происхождения команды или автора как постоянное место их деятельности.
* Во всех id полях используй строго snake_case. Например, 'Rush Ltd' будет 'rush_ltd'. Не изменяй оригинальное написание названий команд и никнеймов, сохраняй точное совпадение с текстом.
* TeamRole значения используй ТОЛЬКО в поле TeamRoles, а SoftwareRole значения используй ТОЛЬКО в поле SoftwareRoles.
* Точно авторы: KSA, Midisoft, IMP,  nix./site (
* Типы полей:
AuthorObj: {id: 'generate_unique', realName?:'', nickName?:'', city?:'',country?:'', teamIds?:[TeamId], teamRoles?:[TeamRole]}
TeamObj: {id: 'generate_unique', name: '', city?: '', country?: '', type?: TeamType, parentTeamIds?: [TeamId]}
PartyObj: {name: '', city?: '', country?: '', year: ''}
AuthorshipObj: {id: AuthorId, softwareRoles?: [SoftwareRole]}
SoftwareObj: {name: '', authorship?: [AuthorshipObj], teamIds?: [TeamId], publisherIds?: [TeamId], year?: 2002}
HardwareObj: {name: '', authorIds?: [AuthorId], teamIds?: [TeamId], year?: 2002}
MusicTrackObj: {name: '', authorIds?: [AuthorId], year?: 2002}
PictureObj: {name: '', authorIds?: [AuthorId], year?: 2002}
* В ответе не пиши ничего лишнего, ТОЛЬКО JSON в формате:
{
teams:[TeamObj],
persons:[AuthorObj],

parties?:[PartyObj],
music?:[MusicTrackObj],
pictures?:[PictureObj],
software?:[SoftwareObj],
hardware?:[HardwareObj],

pressTeamIds?:[TeamId],
pressAuthorship?:[AuthorshipObj],
mentionedPersonIds:[AuthorId],
mentionedTeamIds:[TeamId],
articleAuthorIds?:[AuthorId],

publicationYear?:int
}

Название издания: {$pressTitle}
Год выпуска издания: {$pressYear}
Кусок статьи: {$chunk}";
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
            PromptSender::MODEL_4O,
            $articleId
        );
    }
}
