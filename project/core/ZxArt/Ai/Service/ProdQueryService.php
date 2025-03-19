<?php

namespace ZxArt\Ai\Service;

use JsonException;
use ZxArt\Ai\QueryFailException;
use ZxArt\Ai\QuerySkipException;
use zxProdCategoryElement;
use zxProdElement;

class ProdQueryService
{
    private const DESCRIPTION_LIMIT = 20000;
    private const MIN_DESCRIPTION_LIMIT = 500;
    private const TAGS_MANUAL_LIMIT = 1000;
    private const IMAGES_LIMIT_FOR_CATEGORIES = 10;
    private const IMAGES_LIMIT_FOR_INTRO = 15;
    private const IMAGES_LIMIT_FOR_INTRO_NOTEXT = 30;

    public function __construct(
        private readonly PromptSender $promptSender,
    )
    {

    }


    private function getSeoPromt($isRunnable): string
    {
        $promt = "
Отправляю тебе через API данные. Сделай всё правильно с первой попытки, так как я не смогу проверить и указать на ошибки. 
Действуй как опытный SEO-специалист. Есть сайт-коллекция софта для ZX Spectrum, Sam Coupe, ZX Next, ZX81, ZX Evolution и тд, нужно сделать SEO, чтобы люди в поисковике нашли нужную информацию. 
Я скину данные софта, сгенерируй из них JSON на трех языках eng/rus/spa с SEO полями. 
* Учитывай категорию софта (игры будут искать игроки, системные программы - спецы и интересующиеся старым софтом, демки - искусство) при составлении текста. В игры играют, демо смотрят, софтом пользуются.
* Категории приведены в иерархическом виде через слэш. Слева - родительские, справа - дочерние. 
* Не переводи названия софта и псевдонимы авторов, но делай читабельные названия категорий (игры, демо, программы).
";
        if ($isRunnable) {
            $promt .= "* программу можно запустить на сайте в онлайн-эмуляторе, это важно, используй call to action 'Можно играть на сайте', 'играть онлайн' для игр или 'Запустить онлайн' для программ, 'Посмотреть онлайн' для демо.
";
        }
        $promt .= "* Упомяни год выпуска если влезает.
* page title (65-70 символов) будет показан посетителю в поисковике. Хороший page title содержит название софта, его тип и платформу. Например: Satisfaction - мегадемо для ZX-Spectrum. Или: Turbo Copier - утилита копирования диска для Sam Coupe. Или например: Spectrofon #02 - электронный журнал для ZX Spectrum.  
* h1 (100 символов) будет показан посетителю уже на сайте, это главный заголовок над текстом. Он должен быть человекопонятным и сразу дать кратко понять, что это за программа. Например, Экшен-платформер игра Captain Square Jaw. Не используй оценочных суждений типа 'Впечатляющий', 'Увлекательный' итд 
* Не используй идиотских call to action типа 'Откройте для себя' - это не йогурт, это программы для ретро-компьютеров.
* В Meta description нужно уместить краткое описание, увидев которого человек захотел бы кликнуть и посмотреть страницу среди других страниц в результатах поиска. Нужно выгодно выделиться информативностью и приятным стилем. Желательно указать компанию-производителя, издателя, год, язык, про что программа или игра, жанр. По возможности нужно использовать все 170 символов под завязку. 
* В ответе пиши ТОЛЬКО JSON в формате:
{
eng:{pageTitle, metaDescription, h1},
rus:{},
spa:{}
}

Данные программы:
";
        return $promt;
    }

    private function getSeoProdData(zxProdElement $prodElement): ?array
    {
        $prodData = $prodElement->getElementData('ai');
        if (!$prodData) {
            return null;
        }
        $map = [
            'seriesProds' => ['type', 'Series of software'],
            'isPlayable' => ['isRunnableOnline', true],
            'compilationItems' => ['type', 'Compilation of software'],
            'authorsInfoString' => ['authors', null],
            'languageString' => ['languages', null],
            'categoriesString' => ['categories', null],
            'hardwareString' => ['hardware', null],
            'groupsString' => ['createdBy', null],
            'articleIntros' => ['articles', null],
            'partyString' => ['demoPartyCompetition', null],
            'publishersString' => ['publishers', null],
        ];
        foreach ($map as $key => [$newKey, $value]) {
            if (!empty($prodData[$key])) {
                $prodData[$newKey] = $value ?? $prodData[$key];
                unset($prodData[$key]);
            }
        }

        return $this->processDescriptions($prodData);
    }

    private function processDescriptions(array $prodData): array
    {
        $length = self::DESCRIPTION_LIMIT;

        if (!empty($prodData['manualString']) && strlen($prodData['manualString']) > self::MIN_DESCRIPTION_LIMIT) {
            $prodData['manual'] = $this->truncateUtf8($prodData['manualString'], $length);
        }

        if (!empty($prodData['releaseFileDescription'])) {
            $length = (int)(self::DESCRIPTION_LIMIT * 0.8);
        }

        foreach (['description', 'releaseFileDescription'] as $key) {
            if (!empty($prodData[$key])) {
                if (strlen($prodData[$key]) > self::MIN_DESCRIPTION_LIMIT) {
                    $prodData[$key] = $this->truncateUtf8($prodData[$key], $length);
                } else {
                    unset($prodData[$key]);
                }
            }
        }

        return $prodData;
    }

    private function validateSeoResponse(array $output): bool
    {
        $languages = ['rus', 'eng', 'spa'];
        $fields = ['pageTitle', 'metaDescription', 'h1'];

        foreach ($languages as $language) {
            foreach ($fields as $field) {
                if (empty($output[$language][$field])) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateCategoriesAndTagsResponse(array $output): bool
    {
        $fields = ['tags', 'category'];

        foreach ($fields as $field) {
            if (empty($output[$field])) {
                return false;
            }
        }

        return true;
    }

    private function validateTagsResponse(array $output): bool
    {
        $fields = ['tags'];

        foreach ($fields as $field) {
            if (empty($output[$field])) {
                return false;
            }
        }

        return true;
    }

    private function getProdDataJson(array $prodData): ?string
    {
        try {
            return json_encode($prodData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            return null;
        }
    }

    public function querySeoForProd(zxProdElement $prodElement): ?array
    {
        $prodData = $this->getSeoProdData($prodElement);
        if ($prodData === null) {
            return null;
        }
        $prodDataJson = $this->getProdDataJson($prodData);
        if ($prodDataJson === null) {
            return null;
        }
        $promt = $this->getSeoPromt($prodData['isRunnableOnline']);
        $promt .= $prodDataJson;

        $this->logAi($promt, $prodElement->id . '_seo');
        $response = $this->promptSender->send(
            $promt,
            0.3,
            null,
            true,
            PromptSender::MODEL_4O_MINI,
        );
        if (!$response) {
            return null;
        }
        $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        if (!$this->validateSeoResponse($response)) {
            return null;
        }
        return $response;
    }

    private function getIntroPromt($hasTextDescriptions): string
    {
        $prompt = "
Отправляю тебе через API данные. Сделай всё правильно с первой попытки, так как я не смогу проверить и указать на ошибки.         
Я скину данные софта, сгенерируй из них краткое описание программы в виде JSON на трех языках eng/rus/spa. 
Отправляю скриншоты из программы, распознай их и используй тексты и информацию с них.";
        if ($hasTextDescriptions) {
            $prompt .= "* Для каждого языка напиши по 4 абзаца, используя html теги для форматирования, ЖЕЛАТЕЛЬНО 150 слов";
        } else {
            $prompt .= "* Для каждого языка напиши по два абзаца, используя html теги для форматирования, всё вместе примерно 100 слов";
        }
        $prompt .= "
* Не выдумывай факты, не пиши отсебятину, пересказывай объективно . Текст должен быть объективным, повествовательно-описательным. Это не картотека, нужны реально параграфы описания. ЕСЛИ НЕ ЗНАЕШЬ ЧТО ПИСАТЬ - ЛУЧШЕ НАПИШИ МЕНЬШЕ ТЕКСТА. 
* НЕ ИСПОЛЬЗУЙ эпитеты \"потрясающий\", \"захватывающий\" итд.
* Не пиши про управление и hardware. Больше всего пиши про сюжет (если это игра или демка), а ещё пиши про жанр и особенности программы. В системном и прикладном софте пиши про функционал.
* Если произведение явно основано на фильме или книге, упомяни это обязательно и используй сюжет фильма и книги для дополнительных деталей в описании. 
* Если видно музыкальную группу, селебрити - упомяни их.
* Не переводи названия программ и псевдонимы авторов.
* Не пиши общие фразы про то, что игра 'требует быструю реакцию и стратегическое мышление'. Лучше используй скриншоты и описывай сеттинг в общих чертах.
* Не пиши про 'увлекательный игровой процесс', если ты не знаешь наверняка. Если это известная игра типа manic miner, то да, можно писать про её игровой процесс. А если у тебя НЕТ информации про неё в базе, то не надо сочинять.
* Пиши только интересное читателю, не надо 'воды' типа 'уникальные испытания и окружения' или 'Игроки проходят сложные уровни, преодолевая препятствия и выполняя точные прыжки для продвижения'. Пиши ТОЛЬКО МАКСИМАЛЬНО самую конкретику.  
* В ответе не пиши ни слова, ТОЛЬКО JSON в формате:
{
eng:'all text here',
rus:'',
spa:''
}

Данные:
";
        return $prompt;
    }

    /**
     * @throws QuerySkipException
     * @throws JsonException
     */
    public function queryIntroForProd(zxProdElement $prodElement): ?array
    {
        $result = [
            'eng' => '',
            'rus' => '',
            'spa' => '',
        ];
        $prodData = $this->getSeoProdData($prodElement);

        unset($prodData['isRunnableOnline']);

        $hasTextDescriptions =
            !empty($prodData['manual']) ||
            !empty($prodData['description']) ||
            !empty($prodData['releaseFileDescription']) ||
            !empty($prodData['articles']);

        $imageUrls = $prodElement->getImagesUrls();
        if ($hasTextDescriptions) {
            $imageUrls = array_slice($imageUrls, 0, self::IMAGES_LIMIT_FOR_INTRO);
        } else {
            $imageUrls = array_slice($imageUrls, 0, self::IMAGES_LIMIT_FOR_INTRO_NOTEXT);
        }

        if (!$imageUrls) {
            throw new QuerySkipException("Prod {$prodElement->id} {$prodElement->title} should have at least one screenshot to get the intro, now skipped");
        }

        if ($hasTextDescriptions || $imageUrls) {
            $prodDataJson = $this->getProdDataJson($prodData);
            if ($prodDataJson === null) {
                return null;
            }

            $promt = $this->getIntroPromt($hasTextDescriptions);
            $promt .= $prodDataJson;
            $this->logAi($promt, $prodElement->id . '_intro');
            $response = $this->promptSender->send(
                $promt,
                0.5,
                $imageUrls,
                true,
                PromptSender::MODEL_4O,
            );
            if (!$response) {
                return null;
            }
            $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            $isValid = $this->validateIntroResponse($response);
            if (!$isValid) {
                return null;
            }
            $result = $response;
        }
        return $result;
    }

    private function validateIntroResponse(array $response): bool
    {
        return
            !empty($response['eng']) &&
            !empty($response['rus']) &&
            !empty($response['spa']);
    }


    private function logAi($message, $type)
    {
        if (!is_dir(ROOT_PATH . '/temporary/ai')) {
            mkdir(ROOT_PATH . '/temporary/ai');
        };
        file_put_contents(ROOT_PATH . '/temporary/ai/' . $type, $message, FILE_APPEND);
    }


    private function truncateUtf8($string, int $length)
    {
        if (strlen($string) <= $length) {
            return $string;
        }

        $truncated = substr($string, 0, $length);

        while (!preg_match('//u', $truncated)) {
            $length--;
            $truncated = substr($string, 0, $length);
        }

        return $truncated;
    }

    /**
     * @throws QueryFailException
     * @throws QuerySkipException
     */
    public function queryCategoriesAndTagsForProd(zxProdElement $prodElement, $queryCategories): array
    {
        if (count($prodElement->compilationItems) > 0) {
            throw new QuerySkipException('Skip compilation. ' . $prodElement->getTitle() . ' ' . $prodElement->getId());
        }

        $prodData = $this->getTagsProdData($prodElement);
        $prodDataString = $this->mapToCustomString($prodData);

        $imageUrls = $prodElement->getImagesUrls();
        //skip loading screen
        if (count($imageUrls) > 1) {
            $imageUrls = array_slice($imageUrls, 1);
        }
        $imageUrls = array_slice($imageUrls, 0, self::IMAGES_LIMIT_FOR_CATEGORIES);
        if (count($imageUrls) === 0) {
            throw new QuerySkipException('No images for prod. ' . $prodElement->getTitle() . ' ' . $prodElement->getId());
        }

        if ($queryCategories) {
            $promt = $this->getCategoriesAndTagsPromt();
            $categoriesStructure = $this->getParentCategoriesTreeData($prodElement);
            $categoriesText = $this->jsonToIndentedString($categoriesStructure);
            $promt = str_ireplace('%%categories%%', $categoriesText, $promt);
        } else {
            $promt = $this->getTagsPromt();
        }
        $promt = str_ireplace('%%prod%%', $prodDataString, $promt);
        $this->logAi($promt, $prodElement->id . '_categories_tags');
        $response = $this->promptSender->send(
            $promt,
            0.3,
            $imageUrls,
            true,
        );
        if (!$response) {
            throw new QueryFailException('AI Response is null. ' . $prodElement->getTitle() . ' ' . $prodElement->getId());
        }
        $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        $this->logAi(json_encode($response), $prodElement->id . '_categories_tags');

        if ($queryCategories) {
            $isValid = $this->validateCategoriesAndTagsResponse($response);
        } else {
            $isValid = $this->validateTagsResponse($response);
        }
        if (!$isValid) {
            throw new QueryFailException('AI Response is invalid. ' . $prodElement->getTitle() . ' ' . $prodElement->getId());
        }
        return $response;
    }

    private function mapToCustomString(array $data)
    {
        $result = '';
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                $result .= $key . ' ' . $value . "\n";
            }
        }
        return $result;
    }

    private function getParentCategoriesTreeData(zxProdElement $prodElement): array
    {
        $result = [];
        $categoriesLists = $prodElement->getCategoriesPaths();
        $mainCategoryIds = [];
        foreach ($categoriesLists as $list) {
            $mainCategory = $list[0] ?? null;
            if ($mainCategory === null) {
                continue;
            }
            $mainCategoryId = $mainCategory->getId();
            if (!in_array($mainCategoryId, $mainCategoryIds)) {
                $this->gatherCategoryStructure($mainCategory, $result);
            }
            $mainCategoryIds[] = $mainCategoryId;
        }
        return $result;
    }

    private function gatherCategoryStructure(zxProdCategoryElement $category, &$result)
    {
        $id = $category->getId();
        $result[$id] = [
            'name' => html_entity_decode($category->getTitle()),
        ];
        $subCategories = $category->getCategories();
        $subResult = [];
        foreach ($subCategories as $subCategory) {
            $this->gatherCategoryStructure($subCategory, $subResult);
        }
        if (count($subResult) > 0) {
            $result[$id]['sub'] = $subResult;
        }
    }

    private function jsonToIndentedString(array $data, $level = 0)
    {
        $result = '';
        $indent = str_repeat("\t", $level); // Уровень табуляции

        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value['name'])) {
                $result .= $indent . $key . ' ' . $value['name'] . "\n";
                if (isset($value['sub'])) {
                    $result .= $this->jsonToIndentedString($value['sub'], $level + 1);
                }
            } elseif (is_string($value)) {
                $result .= $indent . $key . ' ' . $value . "\n";
            }
        }

        return $result;
    }

    private function getTagsProdData(zxProdElement $prodElement): array
    {
        $prodData = $prodElement->getElementData('aiCategories');
        if (!$prodData) {
            throw new QueryFailException('Prod data is null. ' . $prodElement->getTitle() . ' ' . $prodElement->getId());
        }
        $manual = '';
        if (!empty($prodData['manualString']) && strlen($prodData['manualString']) > self::MIN_DESCRIPTION_LIMIT) {
            $manual = $this->cleanString($prodData['manualString']);
            $length = self::TAGS_MANUAL_LIMIT;
            $manual = $this->truncateUtf8($manual, $length);
        }
        $data = [
            'title' => $prodData['title'],
            'year' => $prodData['year'],
            'produced' => $prodData['groupsString'],
            'published' => $prodData['publishersString'],
            'manual' => $manual,
        ];
        return $data;
    }

    private function cleanString($input)
    {
        $input = preg_replace('/\s+/', ' ', $input);
        $output = preg_replace('/([^a-zA-Z0-9\s])\1+/', '$1', $input);
        return trim($output);
    }

    private function getCategoriesAndTagsPromt(): string
    {
        return "You are an expert in software classification. 
Assign category for the ZX Spectrum program and suggest 10-15 tags that would be useful for a visitor. 
Base your analysis strictly on the visual layout and similarity to other programs of that genre. 
Focus on the most relevant category the one you are confident in. 
For tags, list only the theme, setting, celebrity characters (if any), and titles of movies or books (if any) IN ENGLISH. DO NOT INCLUDE genre, pixel art, retro, year, or ZX Spectrum in the tags.
Example of good tags for game Predator:Predator,Movie-based,Schwarzenegger,Jungle,Survival,Warfare. 
Determine category based solely on the screenshot, but don't contradict the theme from title or description; ignore setting and theme. Use your knowledge from Wikipedia where applicable.
%%prod%%
Categories tree (tabs denote children, the number is the ID):
%%categories%%
In the response, do not write ANYTHING except the JSON structure:
{tags:['tag1', ...],category:id}
";
    }

    private function getTagsPromt(): string
    {
        return "You are an expert in software classification. Using screenshots, suggest 10-15 tags that would be useful for a visitor. 
For tags, list only the theme, setting, celebrity characters (if any), and titles of movies or books (if any) IN ENGLISH. DO NOT INCLUDE genre, pixel art, retro, year, or ZX Spectrum in the tags. 
DO NOT INCLUDE producer, genre, pixel art, retro, year, or ZX Spectrum in the tags.
Example of good tags for game Predator:Predator,Movie-based,Schwarzenegger,Jungle,Survival,Warfare.
%%prod%%
In the response, do not write ANYTHING except the JSON structure:
{tags:['tag1', ...]}
";
    }
}

