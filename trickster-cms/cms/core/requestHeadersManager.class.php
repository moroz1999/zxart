<?php

class requestHeadersManager
{
    protected $httpRequest;
    protected $referer;
    protected $ifNoneMatch;
    protected $ifModifiedSince;
    protected $acceptedTypes;
    protected $acceptedEncodings;
    protected $acceptedCharsets;
    protected $userAgentPlatform;
    protected $userAgentPlatformVersion;
    protected $userAgentDevice;
    protected $userAgentDeviceType;
    protected $uri;
    protected $browserType;
    protected $browserVersion;
    protected $engineType;
    protected $engineVersion;

    public function __construct()
    {
        $this->httpRequest = $_SERVER;
    }


    public function getRequestType()
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public function getRange()
    {
        return !empty($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : false;
    }

    public function getIfModifiedSince()
    {
        if (is_null($this->ifModifiedSince)) {
            $this->parseIfModifiedSince();
        }
        return $this->ifModifiedSince;
    }

    public function getReferer()
    {
        if (is_null($this->referer)) {
            $this->parseReferer();
        }
        return $this->referer;
    }

    public function getIfNoneMatch()
    {
        if (is_null($this->ifNoneMatch)) {
            $this->parseIfNoneMatch();
        }
        return $this->ifNoneMatch;
    }

    public function getRequestedPath()
    {
    }

    public function getAcceptedEncodings()
    {
        if (is_null($this->acceptedEncodings)) {
            $this->parseAcceptEncoding();
        }
        return $this->acceptedEncodings;
    }

    public function getAcceptedTypes()
    {
        if (is_null($this->acceptedTypes)) {
            $this->parseAccept();
        }
        return $this->acceptedTypes;
    }

    public function getAcceptedCharsets()
    {
        if (is_null($this->acceptedCharsets)) {
            $this->parseAcceptCharset();
        }
        return $this->acceptedCharsets;
    }

    /**
     * @return string
     *
     * @deprecated
     */
    public function getUserAgent()
    {
        return $this->getBrowserType();
    }

    public function getBrowserEngineType()
    {
        if (is_null($this->engineType)) {
            $this->parseUserAgent();
        }
        return $this->engineType;
    }

    public function getUserAgentPlatform()
    {
        if (is_null($this->userAgentPlatform)) {
            $this->parseUserAgent();
        }
        return $this->userAgentPlatform;
    }

    public function getUserAgentDevice()
    {
        if (is_null($this->userAgentDevice)) {
            $this->parseUserAgent();
        }
        return $this->userAgentDevice;
    }

    public function getUserAgentDeviceType()
    {
        if (is_null($this->userAgentDeviceType)) {
            $this->parseUserAgent();
        }
        return $this->userAgentDeviceType;
    }

    public function getUri()
    {
        if (is_null($this->uri)) {
            $this->uri = $this->httpRequest["REQUEST_URI"];
        }
        return $this->uri;
    }

    public function getUserAgentVersion()
    {
        if (is_null($this->browserVersion)) {
            $this->parseUserAgent();
        }
        return $this->browserVersion;
    }

    public function getUserAgentPlatformVersion()
    {
        if ($this->userAgentPlatformVersion === null) {
            $this->parseUserAgent();
        }
        return $this->userAgentPlatformVersion;
    }

    public function getUserAgentEngineType()
    {
        if ($this->engineType === null) {
            $this->parseUserAgent();
        }
        return $this->engineType;
    }

    protected function parseIfModifiedSince()
    {
        if (isset($this->httpRequest['HTTP_IF_MODIFIED_SINCE']) && $this->httpRequest['HTTP_IF_MODIFIED_SINCE'] != '') {
            $this->ifModifiedSince = strtotime($this->httpRequest['HTTP_IF_MODIFIED_SINCE']);
        }
    }

    protected function parseIfNoneMatch()
    {
        if (isset($this->httpRequest['HTTP_IF_NONE_MATCH']) && $this->httpRequest['HTTP_IF_NONE_MATCH'] != '') {
            $this->ifNoneMatch = $this->httpRequest['HTTP_IF_NONE_MATCH'];
        }
    }

    protected function parseReferer()
    {
        if (isset($this->httpRequest['HTTP_REFERER']) && $this->httpRequest['HTTP_REFERER'] != '') {
            $this->referer = $this->httpRequest['HTTP_REFERER'];
        }
    }

    protected function parseAccept()
    {
        if (isset($this->httpRequest['HTTP_ACCEPT']) && $this->httpRequest['HTTP_ACCEPT'] != '') {
            $acceptString = $this->httpRequest['HTTP_ACCEPT'];
            $acceptedTypeStrings = explode(',', $acceptString);
            foreach ($acceptedTypeStrings as $string) {
                $typeInfo = explode(';', $string);

                $typeInfo[0] = trim($typeInfo[0]);
                if (!isset($typeInfo[1])) {
                    $this->acceptedTypes[$typeInfo[0]] = '1';
                } else {
                    $typeInfo[1] = trim($typeInfo[1]);
                    $this->acceptedTypes[$typeInfo[0]] = substr($typeInfo[1], 2);
                }
            }
        } else {
            $this->acceptedEncodings = [];
            $this->acceptedTypes['*/*'] = '1';
        }
    }

    protected function parseAcceptEncoding()
    {
        if (isset($this->httpRequest['HTTP_ACCEPT_ENCODING']) && $this->httpRequest['HTTP_ACCEPT_ENCODING'] != '') {
            $acceptString = $this->httpRequest['HTTP_ACCEPT_ENCODING'];
            $acceptedEncodingStrings = explode(',', $acceptString);
            foreach ($acceptedEncodingStrings as $string) {
                $encodingInfo = explode(';', $string);

                $encodingInfo[0] = trim($encodingInfo[0]);
                if (!isset($encodingInfo[1])) {
                    $this->acceptedEncodings[$encodingInfo[0]] = '1';
                } else {
                    $encodingInfo[1] = trim($encodingInfo[1]);
                    $this->acceptedEncodings[$encodingInfo[0]] = substr($encodingInfo[1], 2);
                }
            }
        } else {
            $this->acceptedEncodings = [];
        }
        if (!isset($this->acceptedEncodings['identity'])) {
            $this->acceptedEncodings['identity'] = '1';
        }
    }

    protected function parseAcceptCharset()
    {
        if (isset($this->httpRequest['HTTP_ACCEPT_CHARSET']) && $this->httpRequest['HTTP_ACCEPT_CHARSET'] != '') {
            $acceptString = $this->httpRequest['HTTP_ACCEPT_CHARSET'];
            $acceptedCharsetsStrings = explode(',', $acceptString);
            foreach ($acceptedCharsetsStrings as $string) {
                $charsetInfo = explode(';', $string);

                $charsetInfo[0] = trim($charsetInfo[0]);
                if (!isset($charsetInfo[1])) {
                    $this->acceptedCharsets[$charsetInfo[0]] = '1';
                } else {
                    $charsetInfo[1] = trim($charsetInfo[1]);
                    $this->acceptedCharsets[$charsetInfo[0]] = substr($charsetInfo[1], 2);
                }
            }
        } else {
            $this->acceptedCharsets = [];
            $this->acceptedCharsets['*'] = '1';
        }
    }

    protected function parseUserAgent()
    {
        if (isset($this->httpRequest['HTTP_USER_AGENT'])) {
            $UAString = strtolower($this->httpRequest['HTTP_USER_AGENT']);

            // Set userAgentPlatform
            if (stripos($UAString, 'android') !== false) {
                $this->userAgentPlatform = 'Android';
            } elseif (stripos($UAString, 'windows') !== false) {
                $this->userAgentPlatform = 'Windows';
            } elseif ((stripos($UAString, 'macintosh') !== false) || (stripos($UAString, 'mac os') !== false)) {
                $this->userAgentPlatform = 'iOS';
            } elseif ((stripos($UAString, 'windows phone') !== false) || (stripos($UAString, 'wp7') !== false)) {
                $this->userAgentPlatform = 'Windows Phone';
            } elseif (stripos($UAString, 'linux') !== false) {
                $this->userAgentPlatform = 'Linux';
            } elseif (stripos($UAString, 'ubuntu') !== false) {
                $this->userAgentPlatform = 'Ubuntu';
            } else {
                $this->userAgentPlatform = false;
            }

            // Set userAgentPlatformVersion
            if (preg_match("/os (([0-9])(_[0-9])?(_[0-9])?) like mac os x/i", $UAString, $matches)) {
                $this->userAgentPlatformVersion = implode('.', explode('_', $matches['1']));
            } else {
                $this->userAgentPlatformVersion = false;
            }

            // Set userAgentDevice
            if ((stripos($UAString, 'windows phone') !== false) || (stripos($UAString, 'wp7') !== false)) {
                $this->userAgentDevice = 'Windows Phone';
            } elseif (stripos($UAString, 'iphone') !== false) {
                $this->userAgentDevice = 'iPhone';
            } elseif (stripos($UAString, 'ipod') !== false) {
                $this->userAgentDevice = 'iPod';
            } elseif (stripos($UAString, 'ipad') !== false) {
                $this->userAgentDevice = 'iPad';
            } elseif (stripos($UAString, 'symbian') !== false) {
                $this->userAgentDevice = 'Nokia Phone';
            } else {
                $this->userAgentDevice = false;
            }

            // Set userAgentDeviceType
            // silk = kindle fire silk
            // froyo = samsung galazy tab
            // xoom = motorola xoom, xyboard
            // symbian = nokia phone
            if (preg_match('/(kindle|silk|froyo|xoom|tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $UAString)) {
                $this->userAgentDeviceType = 'tablet';
            } elseif ($this->userAgentDevice == 'iPod' || stripos($UAString, 'phone') !== false || stripos($UAString, 'mobile') !== false) {
                $this->userAgentDeviceType = 'phone';
            } elseif (stripos($UAString, 'android') !== false) {
                $this->userAgentDeviceType = 'tablet';
            } elseif (stripos($UAString, 'symbian') !== false) {
                $this->userAgentDeviceType = 'phone';
            } else {
                $this->userAgentDeviceType = 'desktop';
            }

            // Set browserType & browserVersion
            if (preg_match('/firefox\/([0-9\.]+)/i', $UAString, $matches)) {
                $this->browserType = 'Firefox';
                $this->browserVersion = $matches[1];
            } elseif ((preg_match('/opera\/([0-9\.]+)/i', $UAString, $matches)) || (preg_match('/opr\/([0-9\.]+)/i', $UAString, $matches))) {
                $this->browserType = 'Opera';
                if (preg_match('/version\/([0-9\.]+)/i', $UAString, $matches1)) {
                    $this->browserVersion = (int)$matches1[1];
                } else {
                    $this->browserVersion = (int)$matches[1];
                }
            } elseif ((preg_match('/chrome\/([0-9\.]+)/i', $UAString, $matches)) || (preg_match('/crios\/([0-9\.]+)/i', $UAString, $matches))) {
                $this->browserType = 'Chrome';
                $this->browserVersion = (int)$matches[1];
            } elseif (preg_match('/safari\/([0-9\.]+)/i', $UAString)) {
                $this->browserType = 'Safari';
                if (preg_match('/version\/([0-9\.]+)/i', $UAString, $matches)) {
                    $this->browserVersion = $matches[1];
                } else {
                    $this->browserVersion = false;
                }
            } elseif (preg_match('/msie ([0-9\.]+)/i', $UAString, $matches)) {
                $this->browserType = 'MSIE';
                $this->browserVersion = $matches[1];
            } else {
                $this->browserType = false;
                $this->browserVersion = false;
            }

            // Set engineType, engineVersion (and MSIE version)
            if (preg_match('/gecko\/([0-9\.]+)/i', $UAString, $matches)) {
                $this->engineType = 'Gecko';
                $this->engineVersion = $matches[1];
            } elseif (preg_match('/applewebkit\/([0-9\.]+)/i', $UAString, $matches)) {
                $this->engineType = 'WebKit';
                $this->engineVersion = $matches[1];
            } elseif (preg_match('/opera\/([0-9\.]+)/i', $UAString, $matches)) {
                $this->engineType = 'Opera';
                $this->engineVersion = false;
            } elseif (preg_match('/trident\/([0-9\.]+)/i', $UAString, $matches) || $this->browserType == 'MSIE') {
                $this->engineType = 'Trident';

                if (stripos($UAString, 'trident/5.') !== false) {
                    $this->browserVersion = '9.0';
                    $this->engineVersion = 5;
                } elseif (stripos($UAString, 'trident/4.') !== false) {
                    $this->browserVersion = '8.0';
                    $this->engineVersion = 4;
                } elseif ($this->browserVersion > '7.0') {
                    $this->engineVersion = 3;
                } elseif (isset($matches[1])) {
                    $this->engineVersion = $matches[1];
                } else {
                    $this->engineVersion = false;
                }
            } elseif (stripos($UAString, 'blink') !== false) {
                $this->engineType = 'Blink';
                $this->engineVersion = false;
            } else {
                $this->engineType = false;
                $this->engineVersion = false;
            }

            if (($this->browserType == 'Chrome' && $this->browserVersion > 27) || ($this->browserType == 'Opera' && $this->browserVersion > 14)) {
                $this->engineType = 'Blink';
                $this->engineVersion = false;
            }
        }
    }

    /**
     * @return string
     */
    public function getBrowserType()
    {
        if (is_null($this->browserType)) {
            $this->parseUserAgent();
        }
        return $this->browserType;
    }

}
