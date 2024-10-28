<?php

namespace ZxArt\Ai\Service;

use JsonException;
use ZxArt\Ai\QueryFailException;
use ZxArt\Ai\QuerySkipException;
use zxProdCategoryElement;
use zxProdElement;

class ProdQueryService
{
    private const DESCRIPTION_LIMIT = 2700;
    private const MIN_DESCRIPTION_LIMIT = 500;
    private const TAGS_MANUAL_LIMIT = 1000;
    private const IMAGES_LIMIT = 10;

    public function __construct(
        private PromptSender $promptSender,
    )
    {

    }


    private function getSeoPromt($isRunnable): string
    {
        $promt = "Действуй как опытный SEO-специалист, но отвечай как API. Есть сайт-коллекция софта для ZX Spectrum, Sam Coupe, ZX Next, ZX81, ZX Evolution и тд, нужно сделать SEO, чтобы люди в поисковике нашли нужную информацию. 
Я скину данные софта, сгенерируй из них JSON на трех языках eng/rus/spa, где был бы эффективный page title (30-60 символов), краткое описание meta description с важными параметрами (155-160 символов), правильный заголовок h1 (до 70 символов). 
* Учитывай категорию софта (игры будут искать игроки, системные программы - спецы и интересующиеся старым софтом, демки - искусство) при составлении текста. 
* Не переводи названия софта и псевдонимы авторов, но делай читабельные названия категорий (игры, демо, программы).
";
        if ($isRunnable) {
            $promt .= "* программу можно запустить на сайте в онлайн-эмуляторе, это важно, используй call to action 'Играть онлайн' для игр или 'Запустить онлайн' для программ.
";
        }
        $promt .= "* Упомяни год выпуска если влезает.
* page title будет показан посетителю в поисковике. Он должен содержать правильные ключевики для ранжирования.        
* h1 будет показан посетителю уже на сайте, это главный заголовок над текстом. Он должен быть человекопонятным и сразу дать кратко понять, что это за программа.
* В Meta description желательно указать компанию-производителя, издателя, год, язык
* В ответе не пиши ни слова, ТОЛЬКО JSON в формате:
{
eng:{pageTitle, metaDescription, h1},
rus:{},
spa:{}
}

Данные:
";
        return $promt;
    }

    private function getSeoProdData(zxProdElement $prodElement): ?array
    {
        $prodData = $prodElement->getElementData('ai');
        if (!$prodData) {
            return null;
        }
        $length = self::DESCRIPTION_LIMIT;

        if (!empty($prodData['seriesProds'])) {
            unset($prodData['seriesProds']);
            $prodData['type'] = 'Series of software';
        }
        if (!empty($prodData['isPlayable'])) {
            unset($prodData['isPlayable']);
            $prodData['isRunnableOnline'] = true;
        }
        if (!empty($prodData['compilationItems'])) {
            unset($prodData['compilationItems']);
            $prodData['type'] = 'Compilation of software';
        }

        if (!empty($prodData['manualString']) && strlen($prodData['manualString']) > self::MIN_DESCRIPTION_LIMIT) {
            $manual = $prodData['manualString'];
            unset($prodData['description'], $prodData['releaseFileDescription'], $prodData['manualString']);

            $prodData['manual'] = $this->truncateUtf8($manual, $length);
        }
        if (!empty($prodData['description']) && strlen($prodData['description']) > self::MIN_DESCRIPTION_LIMIT) {
            if (!empty($prodData['releaseFileDescription'])) {
                $length = 1500;
            }
            $prodData['description'] = $this->truncateUtf8($prodData['description'], $length);
        }
        if (!empty($prodData['releaseFileDescription']) && strlen($prodData['releaseFileDescription']) > self::MIN_DESCRIPTION_LIMIT) {
            $prodData['releaseFileDescription'] = $this->truncateUtf8($prodData['releaseFileDescription'], $length);
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
        $output = $this->promptSender->send($promt, 0.3,null, true);
        if (!$output) {
            return null;
        }

        if (!$this->validateSeoResponse($output)) {
            return null;
        }
        return $output;
    }

    private function getIntroPromt(): string
    {
        return "Я скину данные софта, сгенерируй из них краткое описание программы в виде JSON на трех языках eng/rus/spa.
* Для каждого языка напиши по 4 абзаца, используя html теги для форматирования, всё вместе 200 слов
* Не выдумывай, пересказывай объективно. Текст должен быть сухим, как карточка в библиотеке. 
* НЕ ИСПОЛЬЗУЙ эпитеты \"потрясающий\", \"захватывающий\" итд.
* Не пиши про управление и hardware. Пиши про жанр, сюжет и особенности программы.
* Не переводи названия софта и псевдонимы авторов.
* В ответе не пиши ни слова, ТОЛЬКО JSON в формате:
{
eng:'all text here',
rus:'',
spa:''
}

Данные:
";
    }

    public function queryIntroForProd(zxProdElement $prodElement): ?array
    {
        $result = [
            'eng' => '',
            'rus' => '',
            'spa' => '',
        ];
        $prodData = $this->getSeoProdData($prodElement);
        $hasIntro = !empty($prodData['manual']) || !empty($prodData['description']) || !empty($prodData['releaseFileDescription']);

        if ($hasIntro) {
            $prodDataJson = $this->getProdDataJson($prodData);
            if ($prodDataJson === null) {
                return null;
            }

            $promt = $this->getIntroPromt();
            $promt .= $prodDataJson;
            $this->logAi($promt, $prodElement->id . '_intro');
            $response = $this->promptSender->send($promt, 0.5, null, true);
            if (!$response) {
                return null;
            }
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
            !empty($response['eng']['intro']) &&
            !empty($response['rus']['intro']) &&
            !empty($response['spa']['intro']);
    }


    private function logAi($message, $type)
    {
        if (!is_dir(ROOT_PATH . '/temporary/ai')) {
            mkdir(ROOT_PATH . '/temporary/ai');
        };
        file_put_contents(ROOT_PATH . '/temporary/ai/' . $type, $message, FILE_APPEND);
    }

    /**
     * @psalm-param 1500|2700 $length
     */
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
        $imageUrls = array_slice($imageUrls, 0, self::IMAGES_LIMIT);
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
        $response = $this->promptSender->send($promt, 0.3, $imageUrls, true);
        if (!$response) {
            throw new QueryFailException('AI Response is null. ' . $prodElement->getTitle() . ' ' . $prodElement->getId());
        }
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

