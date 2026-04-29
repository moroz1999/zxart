<?php

class CmsHttpResponse
{
    /** @var CmsHttpResponse */
    private static $instance;
    private $httpResponse = [];
    private $statusTextList = [];

    private function __construct()
    {
        $this->httpResponse['Etag'] = null;
        $this->httpResponse['accessControlAllowOrigin'] = null;
        $this->httpResponse['contentDisposition'] = null;
        $this->httpResponse['contentEncoding'] = null;
        $this->httpResponse['contentLength'] = null;
        $this->httpResponse['contentType'] = null;
        $this->httpResponse['statusText'] = null;
        $this->httpResponse['statusCode'] = null;
        $this->httpResponse['expires'] = null;
        $this->httpResponse['location'] = null;
        $this->httpResponse['lastModified'] = null;
        $this->httpResponse['maxAge'] = null;
        $this->httpResponse['charset'] = null;
        $this->httpResponse['cacheControl'] = null;
        $this->httpResponse['fileName'] = null;
        $this->httpResponse['contentRange'] = null;
        $this->httpResponse['acceptRanges'] = null;

        $this->statusTextList['200'] = 'OK';
        $this->statusTextList['206'] = 'Partial Content';
        $this->statusTextList['301'] = 'Moved Permanently';
        $this->statusTextList['302'] = 'Found';
        $this->statusTextList['304'] = 'Not Modified';
        $this->statusTextList['400'] = 'Bad Request';
        $this->statusTextList['403'] = 'Forbidden';
        $this->statusTextList['404'] = 'Not Found';
        $this->statusTextList['500'] = 'Internal Server Error';

        $this->setStatusCode('200');
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            $className = __CLASS__;
            self::$instance = new $className();
        }

        return self::$instance;
    }

    public function sendHeaders()
    {
        header($this->httpResponse['statusText']);
        if (!is_null($this->httpResponse['accessControlAllowOrigin'])) {
            header('Access-Control-Allow-Origin:' . $this->httpResponse['accessControlAllowOrigin']);
        }
        if (!is_null($this->httpResponse['location'])) {
            header('Location:' . $this->httpResponse['location']);
        }
        if (!is_null($this->httpResponse['contentDisposition'])) {
            if (!is_null($this->httpResponse['fileName'])) {
                header('Content-Disposition: ' . $this->httpResponse['contentDisposition'] . '; filename="' . $this->httpResponse['fileName'] . '"');
            } else {
                header('Content-Disposition: ' . $this->httpResponse['contentDisposition']);
            }
        }
        if (!is_null($this->httpResponse['acceptRanges'])) {
            header('Accept-Ranges: ' . $this->httpResponse['acceptRanges']);
        }
        if (!is_null($this->httpResponse['contentEncoding'])) {
            header('Content-Encoding: ' . $this->httpResponse['contentEncoding']);
        }
        if (!is_null($this->httpResponse['contentRange'])) {
            header('Content-Range: ' . $this->httpResponse['contentRange']);
        }
        if (!is_null($this->httpResponse['contentLength'])) {
            header('Content-Length: ' . $this->httpResponse['contentLength']);
        }
        if (!is_null($this->httpResponse['lastModified'])) {
            header('Last-Modified: ' . date('r', $this->httpResponse['lastModified']));
        }
        if (!is_null($this->httpResponse['contentType'])) {
            $resultString = 'Content-Type: ' . $this->httpResponse['contentType'];
            if (!is_null($this->httpResponse['charset'])) {
                $resultString .= '; charset=' . $this->httpResponse['charset'];
            }
            header($resultString);
        }
        if (!is_null($this->httpResponse['cacheControl'])) {
            $maxAgeString = '';
            if ($this->httpResponse['cacheControl'] == 'public') {
                if (!is_null($this->httpResponse['maxAge'])) {
                    $maxAgeString = ', max-age=' . $this->httpResponse['maxAge'];
                    if (is_null($this->httpResponse['expires'])) {
                        $this->httpResponse['expires'] = time() + $this->httpResponse['maxAge'];
                    }
                }
                if (!is_null($this->httpResponse['expires'])) {
                    $expiresDate = gmdate('D, d M Y H:i:s T', $this->httpResponse['expires']);
                    header('Expires: ' . $expiresDate);
                }
                if (!is_null($this->httpResponse['Etag'])) {
                    header('Etag: "' . $this->httpResponse['Etag'] . '"');
                }
            }
            header('Cache-control: ' . $this->httpResponse['cacheControl'] . $maxAgeString);
            header('Pragma: ' . $this->httpResponse['cacheControl']);
        }
    }

    public function sendContent($contentText)
    {
        echo $contentText;
    }

    public function setEtag($Etag)
    {
        if ($Etag) {
            $this->httpResponse['Etag'] = $Etag;
        }
    }

    public function setLastModified($value)
    {
        if ($value) {
            $this->httpResponse['lastModified'] = $value;
        }
    }

    public function setCharset($charset)
    {
        $this->httpResponse['charset'] = $charset;
    }

    public function setContentRange($contentRange)
    {
        $this->httpResponse['contentRange'] = $contentRange;
    }

    public function setStatusCode($codeNumber)
    {
        $this->httpResponse['statusCode'] = $codeNumber;
        $this->httpResponse['statusText'] = 'HTTP/1.1 ' . $codeNumber . ' ' . $this->statusTextList[$codeNumber];
    }

    public function setContentEncoding($encoding)
    {
        if ($encoding != 'identity') {
            $this->httpResponse['contentEncoding'] = $encoding;
        }
    }

    public function setCacheControl($value)
    {
        $this->httpResponse['cacheControl'] = $value;
    }

    public function setContentDisposition($value)
    {
        $this->httpResponse['contentDisposition'] = $value;
    }

    public function setContentLength($length)
    {
        $this->httpResponse['contentLength'] = $length;
    }

    public function setExpires($timestamp)
    {
        $this->httpResponse['expires'] = $timestamp;
    }

    public function setLocation($url)
    {
        $this->httpResponse['location'] = $url;
    }

    public function setMaxAge($seconds)
    {
        $this->httpResponse['maxAge'] = $seconds;
    }

    public function setContentType($contentType)
    {
        $this->httpResponse['contentType'] = $contentType;
    }

    public function setAcceptRanges($acceptRanges)
    {
        $this->httpResponse['acceptRanges'] = $acceptRanges;
    }

    public function setFileName($fileName)
    {
        $this->httpResponse['fileName'] = $fileName;
    }

    public function setAccessControlAllowOrigin($accessControlAllowOrigin)
    {
        $this->httpResponse['accessControlAllowOrigin'] = $accessControlAllowOrigin;
    }
}

