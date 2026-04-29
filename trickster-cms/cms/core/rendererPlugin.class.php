<?php

abstract class rendererPlugin extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    protected $preferredEncodings = [];
    protected $renderingEngine;
    /**
     * @var requestHeadersManager $requestHeadersManager
     */
    protected $requestHeadersManager;
    /**
     * @var CmsHttpResponse
     */
    protected $httpResponse;
    protected $acceptRanges;
    protected $maxAge;
    protected $lastModified;
    protected $cacheControl = 'public';
    protected $contentDisposition;
    protected $contentType;
    protected $expires;
    protected $contentText;
    protected $encoding;
    protected $bytesToSend = false;
    protected $startPoint = 0;
    public $debugMode = false;

    abstract public function init();

    abstract public function assign($attributeName, $value);

    abstract public function fetch();

    abstract protected function renderContent();

    abstract protected function getContentType();

    abstract protected function getEtag();

    abstract protected function getContentLength();

    abstract protected function getContentTextPart();

    abstract protected function getContentDisposition();

    abstract protected function compress($encoding);

    protected function getLastModified()
    {
        return $this->lastModified;
    }

    protected function getForcedEncoding()
    {
        return false;
    }

    final public function display()
    {
        $lastModified = $this->getLastModified();
        $etag = $this->getEtag();
        $this->httpResponse->setCacheControl($this->cacheControl);

        //encoding should be set before content rendering
        if (!($this->encoding = $this->getForcedEncoding())) {
            $this->encoding = $this->selectHTTPParameter($this->preferredEncodings, $this->requestHeadersManager->getAcceptedEncodings());
        }

        if (($this->matchesEtag($etag) !== true) && ($this->isModifiedSince($lastModified) !== false)) {
            //clear contents
            if (ob_get_level() > 0){
                ob_clean();
            }
            $this->renderContent();

            $this->compress($this->encoding);
            //content length should be taken after possible compression.
            $contentLength = $this->getContentLength();
            if ($rangeValue = $this->requestHeadersManager->getRange()) {
                if ($ranges = $this->parseRangeRequest($contentLength, $rangeValue)) {
                    $firstRange = reset($ranges);
                    $this->startPoint = $firstRange[0];
                    $this->bytesToSend = $firstRange[1] - $firstRange[0] + 1;
                    $this->httpResponse->setStatusCode('206');
                    $rangeString = 'bytes ' . $firstRange[0] . '-' . $firstRange[1] . '/' . $contentLength;

                    $this->httpResponse->setContentRange($rangeString);
                    $contentLength = $this->bytesToSend;
                }
            }
            $contentDisposition = $this->getContentDisposition();
            if (is_null($this->contentType)) {
                $contentType = $this->getContentType();
            } else {
                $contentType = $this->contentType;
            }

            if ($fileName = $this->getFileName()) {
                $this->httpResponse->setFileName($fileName);
            }

            $this->httpResponse->setLastModified($lastModified);
            $this->httpResponse->setMaxAge($this->maxAge);
            $this->httpResponse->setExpires($this->expires);
            $this->httpResponse->setEtag($etag);
            $this->httpResponse->setContentDisposition($contentDisposition);
            $this->httpResponse->setContentEncoding($this->encoding);
            $this->httpResponse->setContentLength($contentLength);
            $this->httpResponse->setContentType($contentType);
            if ($this->acceptRanges) {
                $this->httpResponse->setAcceptRanges($this->acceptRanges);
            }
            $this->endOutputBuffering();

            $this->httpResponse->sendHeaders();
            if ($this->requestHeadersManager->getRequestType() !== 'HEAD') {
                while ($contentText = $this->getContentTextPart()) {
                    $this->httpResponse->sendContent($contentText);
                }
            }
        } else {
            $this->httpResponse->setStatusCode('304');
            $this->httpResponse->setLastModified($lastModified);
            $this->httpResponse->setMaxAge($this->maxAge);
            $this->httpResponse->setExpires($this->expires);
            $this->httpResponse->setEtag($etag);
            $this->endOutputBuffering();
            $this->httpResponse->sendHeaders();
        }
    }

    final protected function matchesEtag($currentEtag)
    {
        $requestedEtag = $this->requestHeadersManager->getIfNoneMatch();
        if (!$requestedEtag) {
            return null;
        }
        return $requestedEtag === '"' . $currentEtag . '"';
    }

    final protected function isModifiedSince($lastModified)
    {
        $requestedModified = $this->requestHeadersManager->getIfModifiedSince();
        if (!$requestedModified) {
            return null;
        }
        return $lastModified > $requestedModified;
    }

    final public function fileNotFound()
    {
        $this->httpResponse->setStatusCode('404');
        $this->endOutputBuffering();

        $this->httpResponse->sendHeaders();
    }

    final protected function selectHTTPParameter($preferredOrder, $HTTPParameters, $universalParameter = null)
    {
        $preferredParameter = false;

        $oneLevelParameters = [];
        arsort($HTTPParameters);
        $parametersCount = count($HTTPParameters);
        foreach ($HTTPParameters as $parameter => &$level) {
            $parametersCount--;
            if ($parameter == $universalParameter) {
                return reset($preferredOrder);
            }
            if (reset($oneLevelParameters) > $level) {
                foreach ($preferredOrder as &$preferredParameter) {
                    if (isset($oneLevelParameters[$preferredParameter])) {
                        return $preferredParameter;
                    }
                }
                $oneLevelParameters = [];
            }
            $oneLevelParameters[$parameter] = $level;
            if ($parametersCount == 0) {
                foreach ($preferredOrder as &$preferredParameter) {
                    if (isset($oneLevelParameters[$preferredParameter])) {
                        return $preferredParameter;
                    }
                }
            }
        }
        return $preferredParameter;
    }

    public function getFileName()
    {
        return false;
    }

    final protected function gzip($contentText)
    {
        return gzencode($contentText, 3);
    }

    public function setContentDisposition($value)
    {
        $this->contentDisposition = $value;
    }

    public function setAcceptRanges($value)
    {
        $this->acceptRanges = $value;
    }

    public function setMaxAge($value)
    {
        $this->maxAge = $value;
    }

    public function setLastModified($value)
    {
        $this->lastModified = $value;
    }

    public function setCacheControl($value)
    {
        $this->cacheControl = $value;
    }

    public function setContentType($value)
    {
        $this->contentType = $value;
    }

    public function clearCache()
    {
    }

    public function endOutputBuffering()
    {
        //todo: remove workaround and provide proper ob handler
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    public function getAttribute($attributeName)
    {
        return false;
    }

    protected function parseRangeRequest($entity_body_length, $range_header)
    {
        $range_list = [];

        if ($entity_body_length == 0) {
            return $range_list; // mark unsatisfiable
        }

        // The only range unit defined by HTTP/1.1 is "bytes". HTTP/1.1
        // implementations MAY ignore ranges specified using other units.
        // Range unit "bytes" is case-insensitive
        if (preg_match('/^bytes=([^;]+)/i', $range_header, $match)) {
            $range_set = $match[1];
        } else {
            return false;
        }

        // Wherever this construct is used, null elements are allowed, but do
        // not contribute to the count of elements present. That is,
        // "(element), , (element) " is permitted, but counts as only two elements.
        $range_spec_list = preg_split('/,/', $range_set, 0, PREG_SPLIT_NO_EMPTY);

        foreach ($range_spec_list as $range_spec) {
            $range_spec = trim($range_spec);

            if (preg_match('/^(\d+)\-$/', $range_spec, $match)) {
                $first_byte_pos = $match[1];

                if ($first_byte_pos > $entity_body_length) {
                    continue;
                }

                $first_pos = $first_byte_pos;
                $last_pos = $entity_body_length - 1;
            } elseif (preg_match('/^(\d+)\-(\d+)$/', $range_spec, $match)) {
                $first_byte_pos = $match[1];
                $last_byte_pos = $match[2];

                // If the last-byte-pos value is present, it MUST be greater than or
                // equal to the first-byte-pos in that byte-range-spec
                if ($last_byte_pos < $first_byte_pos) {
                    return false;
                }

                $first_pos = $first_byte_pos;
                $last_pos = min($entity_body_length - 1, $last_byte_pos);
            } elseif (preg_match('/^\-(\d+)$/', $range_spec, $match)) {
                $suffix_length = $match[1];

                if ($suffix_length == 0) {
                    continue;
                }

                $first_pos = $entity_body_length - min($entity_body_length, $suffix_length);
                $last_pos = $entity_body_length - 1;
            } else {
                return false;
            }

            $range_list[] = [$first_pos, $last_pos];
        }

        return $range_list;
    }
}
