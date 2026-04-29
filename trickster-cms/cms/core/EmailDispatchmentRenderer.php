<?php

use Pelago\Emogrifier\CssInliner;

class EmailDispatchmentRenderer extends errorLogger
{
    protected $receiverEmail;
    protected $receiverName;
    protected $fromEmail;
    protected $fromName;
    protected $subject;
    protected $data;
    protected $type;
    protected $dispatchment;
    protected $dispatchmentId;
    protected $unsubscribeLink;
    protected $webLink;
    protected $designThemesManager;

    /**
     * @return DesignThemesManager
     */
    public function getDesignThemesManager()
    {
        return $this->designThemesManager;
    }

    /**
     * @param DesignThemesManager $designThemesManager
     */
    public function setDesignThemesManager($designThemesManager)
    {
        $this->designThemesManager = $designThemesManager;
    }

    public function setUnsubscribleLink($unsubscribeLink)
    {
        $this->unsubscribeLink = $unsubscribeLink;
    }

    public function setWebLink($webLink)
    {
        $this->webLink = $webLink;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setDispatchment($dispatchment)
    {
        $this->dispatchment = $dispatchment;
    }

    public function getDispatchment()
    {
        return $this->dispatchment;
    }

    public function renderContent()
    {
        $content = false;
        if ($emailType = $this->getEmailDispatchmentType()) {
            $emailCss = $this->renderCss($emailType->getCssFiles(), $emailType->getCssImagesURL());
            $emailHTML = $this->renderHtml(
                $emailType->getEmailTemplate(),
                $emailType->getContentTemplate(),
                $emailType->getDisplayWebLink(),
                $emailType->getDisplayUnsubscribeLink(),
                $emailType->getCssImagesURL(),
                $emailType,
                $emailType->isLinksTrackingEnabled()
            );
            $content = $this->applyCssToHtml($emailCss, $emailHTML);
        }

        return $content;
    }

    protected function applyCssToHtml($emailCss, $emailHTML)
    {
        $content = false;
        try {
            $emogrifier = CssInliner::fromHtml($emailHTML)
                ->inlineCss($emailCss);
            $content = $emogrifier->render();
        } catch (exception $ex) {
            $this->logError('emogrifier error: ' . $ex->getMessage());
        }
        return $content;
    }

    protected function renderHtml(
        $emailTemplate,
        $contentTemplate,
        $displayWebLink,
        $displayUnsubscribeLink,
        $imagesUrl,
        EmailDispatchmentType $emailType,
        $trackLinks
    ) {
        $controller = controller::getInstance();
        $htmlRenderer = renderer::getPlugin('smarty');

        if ($displayWebLink && $this->webLink) {
            $htmlRenderer->assign('webLink', $this->webLink);
        }

        if ($imagesUrl) {
            $htmlRenderer->assign('imagesUrl', $imagesUrl);
        }

        if ($displayUnsubscribeLink && $this->unsubscribeLink) {
            $htmlRenderer->assign('unsubscribeLink', $this->unsubscribeLink);
        }

        $htmlRenderer->assign('controller', $controller);
        $htmlRenderer->assign('data', $this->data);
        $htmlRenderer->assign('contentTheme', $emailType->getContentTheme());
        $htmlRenderer->assign('theme', $emailType->getTheme());
        $htmlRenderer->template = $emailTemplate;
        $htmlRenderer->assign('contentTemplate', $contentTemplate);
        $htmlRenderer->assign('dispatchmentType', $emailType);
        $htmlRenderer->assign('dispatchment', $this->getDispatchment());
        $result = $htmlRenderer->fetch();

        $dpId = $this->getDispatchmentHistoryId();
        if ($trackLinks && $dpId && $controller->domainName) {
            // if dispatchment ID doesn't exist, email preview is being rendered
            $doc = new DOMDocument();
            $doc->loadHTML($result);
            $links = $doc->getElementsByTagName('a');

            foreach ($links as $link) {
                if ($linkAddr = $link->getAttribute('href')) {
                    if (
                        substr($linkAddr, 0, 1) != '#' &&
                        $linkAddr != $this->webLink &&
                        $linkAddr != $this->unsubscribeLink &&
                        (
                            substr($linkAddr, 0, 2) == '//' ||
                            substr($linkAddr, 0, 7) == 'http://' ||
                            substr($linkAddr, 0, 8) == 'https://'
                        )
                    ) {
                        $parsedUrl = parse_url($linkAddr);
                        $parsedHost = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
                        $trackingUrl = $controller->domainURL . '/emails/id:' . $dpId . '/action:viewURL/url:' . base64_encode($linkAddr) . '/';
                        $externalLink = strcasecmp($controller->domainName, $parsedHost) !== 0;
                        if ($externalLink) {
                            $trackingUrl .= 'external:1/';
                        }
                        $link->setAttribute('href', $trackingUrl);
                    }
                }
            }
            $result = $doc->saveHTML();
        }
        return $result;
    }

    public function getDispatchmentHistoryId()
    {
        $dispatchmentId = $this->dispatchmentId;
        $receiverEmail = $this->receiverEmail;
        $collection = persistableCollection::getInstance('email_dispatchments_history');
        if ($records = $collection->load(['dispatchmentId' => $dispatchmentId, 'email' => $receiverEmail])) {
            $record = reset($records);
            $dpId = $record->id;
        }
        return $dpId;
    }

    // http://php.net/manual/en/function.parse-url.php#106731
    public function unparse_url($parsed_url)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    protected function renderCss($cssFiles, $imagesURL)
    {
        $emailCss = '';
        try {
            $css = '@image_folder: "' . $imagesURL . '";';
            foreach ($cssFiles as &$filePath) {
                $css .= file_get_contents($filePath);
            }
            $less = new lessc();
            $emailCss = $less->compile($css);
        } catch (exception $ex) {
            $this->logError('lessphp error: ' . $ex->getMessage());
        }
        return $emailCss;
    }

    /**
     * @return EmailDispatchmentType
     */
    protected function getEmailDispatchmentType()
    {
        $object = false;
        $className = $this->type . 'EmailDispatchmentType';
        if (!class_exists($className, false)) {
            $fileName = $this->type . '.class.php';
            $pathsManager = controller::getInstance()->getPathsManager();
            $fileDirectory = $pathsManager->getRelativePath('dispatchmentTypes');
            if ($filePath = $pathsManager->getIncludeFilePath($fileDirectory . $fileName)) {
                include_once($filePath);
            }
        }
        if (class_exists($className, false)) {
            /**
             * @var EmailDispatchmentType $object
             */
            $object = new $className();
            $object->setEmailDispatchmentRenderer($this);
            $object->initialize();
        } else {
            $this->logError('EmailDispatchmentType class "' . $className . '" is missing');
        }
        return $object;
    }

    public function setDispatchmentId($dispatchmentId)
    {
        $this->dispatchmentId = $dispatchmentId;
    }

    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;
    }

    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setReceiverEmail($receiverEmail)
    {
        $this->receiverEmail = $receiverEmail;
    }

    public function setReceiverName($receiverName)
    {
        $this->receiverName = $receiverName;
    }
}
