<?php

namespace ZxArt\Ai;
use ConfigManager;
use errorLog;
use JsonException;
use OpenAI;
use zxProdElement;

class AiQueryService
{
    private ConfigManager $configManager;
    private const DESCRIPTION_LIMIT = 2700;
    private const MIN_DESCRIPTION_LIMIT = 500;

    public function setConfigManager(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    private function getSeoPromt($isRunnable)
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

        $output = $this->sendPromt($promt, 0.3, $prodElement->id . '_seo', $prodData);
        if (!$output) {
            return null;
        }

        if (!$this->validateSeoResponse($output)) {
            return null;
        }
        return $output;
    }

    private function getIntroPromt()
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

    public function queryIntroForProd(zxProdElement $prodElement)
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
            $response = $this->sendPromt($promt, 0.5, $prodElement->id . '_intro', $prodData);
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

    private function sendPromt(string $promt, float $temperature, string $log, array $prodData): ?array
    {
        $apiKey = $this->configManager->getConfig('main')->get('ai_key');
        $client = OpenAI::client($apiKey);

        $result = null;
        try {
            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'temperature' => $temperature,
                'messages' => [
                    ['role' => 'user', 'content' => $promt],
                ],
            ]);
            $result = $response->choices[0]->message->content;
        } catch (Exception $exception) {
            errorLog::getInstance()?->logMessage(self::class, $exception->getMessage() . ': ' . $prodData['title']);
        }
        if (!is_dir(ROOT_PATH . '/temporary/ai')) {
            mkdir(ROOT_PATH . '/temporary/ai');
        };
        file_put_contents(ROOT_PATH . '/temporary/ai/' . $log, $promt);


        try {
            $data = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return null;
        }

        return $data;
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
}