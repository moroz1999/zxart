<?php

class AiManager
{
    private ConfigManager $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function setConfigManager(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    public function getProdData(zxProdElement $element)
    {
        $output = null;
        $prodData = $element->getElementData('ai');
        if (!$prodData) {
            return null;
        }
        $hasIntro = false;
        $length = 2700;

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
        if (!empty($prodData['seriesProds'])) {
            $prodData['type'] = 'Series of software';
        }
        if (!empty($prodData['manualString'])) {
            if (strlen($prodData['manualString']) > 500) {
                unset($prodData['description']);
                unset($prodData['releaseFileDescription']);

                $prodData['manualString'] = $this->truncateUtf8($prodData['manualString'], $length);
                $hasIntro = true;
            }
        }
        if (!empty($prodData['description'])) {
            if (strlen($prodData['description']) > 500) {
                if (!empty($prodData['releaseFileDescription'])) {
                    $length = 1500;
                }
                $prodData['description'] = $this->truncateUtf8($prodData['description'], $length);
                $hasIntro = true;
            }
        }
        if (!empty($prodData['releaseFileDescription'])) {
            if (strlen($prodData['releaseFileDescription']) > 500) {
                $prodData['releaseFileDescription'] = $this->truncateUtf8($prodData['releaseFileDescription'], $length);

                $hasIntro = true;
            }
        }

        $promt = "Действуй как опытный SEO-специалист, но отвечай как API. Есть сайт-коллекция софта для ZX Spectrum, Sam Coupe, ZX Next, ZX81, ZX Evolution и тд, нужно сделать SEO, чтобы люди в поисковике нашли нужную информацию. 
Я скину данные софта, сгенерируй из них JSON на трех языках eng/rus/spa, где был бы эффективный page title (30-60 символов), краткое описание meta description с важными параметрами (155-160 символов), правильный заголовок h1 (до 70 символов). 
* Учитывай категорию софта (игры будут искать игроки, системные программы - спецы и интересующиеся старым софтом, демки - искусство) при составлении текста. 
* Не переводи названия софта и псевдонимы авторов, но делай читабельные названия категорий (игры, демо, программы).
";
        if (!empty($prodData['isRunnableOnline'])) {
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
rus:{...},
spa:{...}
}

Данные софта:
";
        $prodText = json_encode($prodData, JSON_UNESCAPED_UNICODE);

        if (!$prodText) {
            return null;
        }
        $promt .= $prodText;
        if ($text = $this->sendPromt($promt, 0.3, $element->id . '_seo', $prodData)) {
            $output = json_decode($text, true);
            if (empty($output['rus']['pageTitle']) || empty($output['rus']['metaDescription']) || empty($output['rus']['h1'])) {
                return null;
            }
            if (empty($output['eng']['pageTitle']) || empty($output['eng']['metaDescription']) || empty($output['eng']['h1'])) {
                return null;
            }
            if (empty($output['spa']['pageTitle']) || empty($output['spa']['metaDescription']) || empty($output['spa']['h1'])) {
                return null;
            }
        } else {
            return null;
        }
        if ($hasIntro) {
            $promt2 = "Я скину данные софта, сгенерируй из них краткое описание программы в виде JSON на трех языках eng/rus/spa.
* Для каждого языка напиши по 4 абзаца, используя html теги для форматирования, всё вместе 200 слов
* Не выдумывай, пересказывай объективно. Текст должен быть сухим, как карточка в библиотеке. 
* НЕ ИСПОЛЬЗУЙ эпитеты \"потрясающий\", \"захватывающий\" итд.
* Не пиши про управление и hardware. Пиши про жанр, сюжет и особенности программы.
* Не переводи названия софта и псевдонимы авторов.
* В ответе не пиши ни слова, ТОЛЬКО JSON в формате:
{
eng:{intro: 'all text here'},
rus:{...},
spa:{...}
}

Данные софта:
";
            $promt2 .= $prodText;
            if ($text = $this->sendPromt($promt2, 0.5, $element->id . '_intro', $prodData)) {
                $data2 = json_decode($text, true);
                if (!empty($data2['eng']['intro']) && !empty($data2['rus']['intro']) && !empty($data2['spa']['intro'])) {
                    $output['eng']['intro'] = $data2['eng']['intro'];
                    $output['rus']['intro'] = $data2['rus']['intro'];
                    $output['spa']['intro'] = $data2['spa']['intro'];
                    return $output;
                }
            } else {
                return null;
            }
        } else {
            return $output;
        }
        return null;
    }

    private function sendPromt($promt, $temperature, $log, $prodData)
    {
        $apiKey = $this->configManager->getConfig('main')->get('ai_key');
        $client = OpenAI::client($apiKey);

        $result = false;
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
            errorLog::getInstance()->logMessage(self::class, $exception->getMessage() . ': ' . $prodData['title']);
        }
        if (!is_dir(ROOT_PATH . '/temporary/ai')) {
            mkdir(ROOT_PATH . '/temporary/ai');
        };
        file_put_contents(ROOT_PATH . '/temporary/ai/' . $log, $promt);

        return $result;
    }

    private function truncateUtf8($string, $length)
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