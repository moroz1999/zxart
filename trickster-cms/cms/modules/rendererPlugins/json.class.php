<?php

class jsonRendererPlugin extends rendererPlugin implements RendererPluginAppendInterface
{
    protected $attributesList = [];
    protected $contentRead = false;

    public function init()
    {
        $this->requestHeadersManager = $this->getService(requestHeadersManager::class);
        $this->httpResponse = CmsHttpResponse::getInstance();

        $this->maxAge = 0;
        $this->httpResponse->setCacheControl('no-cache');
        $this->preferredEncodings = [
            'gzip',
            'deflate',
            'identity',
        ];
    }

    public function fetch()
    {
    }

    public function assign($attributeName, $value)
    {
        if ($attributeName == 'body' || $attributeName == 'responseData' || $attributeName == 'responseStatus' || $attributeName == 'start' || $attributeName == 'limit' || $attributeName == 'totalAmount') {
            $this->attributesList[$attributeName] = $value;
        }
    }

    public function getAttribute($attributeName)
    {
        if (isset($this->attributesList[$attributeName])) {
            return $this->attributesList[$attributeName];
        }
        return false;
    }

    protected function getEtag()
    {
        return false;
    }

    protected function getContentLength()
    {
        return strlen($this->contentText);
    }

    protected function getContentType()
    {
        $contentTypes = $this->requestHeadersManager->getAcceptedTypes();
        $preferredOrder = ['application/json'];

        $selectedType = $this->selectHTTPParameter($preferredOrder, $contentTypes, '*/*');
        return $selectedType;
    }

    public function getContentDisposition()
    {
        return 'inline';
    }

    protected function renderContent()
    {
        if (array_key_exists('body', $this->attributesList)) {
            $this->contentText = json_encode($this->attributesList['body']);
        } else {
            $this->contentText = json_encode($this->attributesList);
        }
    }

    protected function getContentTextPart()
    {
        if (!$this->contentRead) {
            $this->contentRead = true;
            return $this->contentText;
        }
        return false;
    }

    protected function compress($encoding)
    {
        if ($encoding == 'gzip') {
            $this->contentText = $this->gzip($this->contentText);
        }
    }

    public function appendResponseData($type, $value)
    {
        if (!isset($this->attributesList['responseData'])) {
            $this->attributesList['responseData'] = [];
        }
        if (!isset($this->attributesList['responseData'][$type])) {
            $this->attributesList['responseData'][$type] = [];
        }
        $this->attributesList['responseData'][$type][] = $value;
    }

    public function assignResponseData($type, $value)
    {
        $this->attributesList['responseData'][$type] = $value;
    }
}