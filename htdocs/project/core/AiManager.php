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
        $data = $element->getElementData('ai');
        if (!empty($data['manualString'])) {
            $data['manualString'] = substr($data['manualString'], 0, min(strlen($data['manualString']), 3000));
            $hasIntro = true;
        } elseif (!empty($data['description'])) {
            $data['description'] = substr($data['description'], 0, min(strlen($data['description']), 3000));
            $hasIntro = true;
        } elseif (!empty($data['releaseFileDescription'])) {
            $data['releaseFileDescription'] = substr($data['releaseFileDescription'], 0, min(strlen($data['releaseFileDescription']), 3000));
            $hasIntro = true;
        } else {
            $hasIntro = false;
        }

        $promt = "Действуй как опытный SEO-специалист, но отвечай как API. Есть сайт-коллекция софта для ZX Spectrum, Sam Coupe, ZX Next, ZX81, ZX Evolution и тд, нужно сделать SEO, чтобы люди в поисковике нашли нужную информацию. 
Я скину поля софта, сгенерируй JSON ответ на трех языках eng/rus/spa, где был бы эффективный page title (30-60 символов), краткое описание meta description с важными параметрами (155-160 символов), правильный заголовок h1 (до 70 символов). 
* Учитывай категорию софта (игры будут искать игроки, системные программы - спецы и интересующиеся старым софтом, демки - искусство) при составлении текста. 
* Не переводи названия софта и псевдонимы авторов, но делай читабельные названия категорий (игры, демо, программы).
";
        if ($data['isPlayable']) {
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

";

        $promt .= json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($text = $this->sendPromt($promt)) {
            $output = json_decode($text, true);
        }
        if ($hasIntro) {
            $promt2 = "Я скину поля софта, сгенерируй JSON ответ на трех языках eng/rus/spa, где было бы краткое описание (3-4 абзаца одни полем с html тегами, всё вместе примерно 200 слов).
* Не выдумывай, пересказывай объективно. Текст должен быть сухим, как карточка в библиотеке. Не надо эпитетов \"потрясающий\", \"захватывающий\" итд.
* Делай упор не на управлении и железе, а на жанре, сюжете и особенностях программы.
* Не переводи названия софта и псевдонимы авторов.
* В ответе не пиши ни слова, ТОЛЬКО JSON в формате:
{
eng:{intro: 'all text here'},
rus:{...},
spa:{...}
}

";
            $promt2 .= json_encode($data, JSON_UNESCAPED_UNICODE);
            if ($text = $this->sendPromt($promt2)) {
                $data2 = json_decode($text, true);
                $output['eng']['intro'] = $data2['eng']['intro'];
                $output['rus']['intro'] = $data2['rus']['intro'];
                $output['spa']['intro'] = $data2['spa']['intro'];
            }
        }


        return $output;
    }

    private function sendPromt($promt)
    {
        $apiKey = $this->configManager->getConfig('main')->get('ai_key');
        $client = OpenAI::client($apiKey);

        $result = false;
        try {
            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'temperature' => 0.1,
                'messages' => [
                    ['role' => 'user', 'content' => $promt],
                ],
            ]);
            $result = $response->choices[0]->message->content;
        } catch (Exception $exception) {
            errorLog::getInstance()->logMessage(self::class, $exception->getMessage());
        }
        return $result;

    }
}