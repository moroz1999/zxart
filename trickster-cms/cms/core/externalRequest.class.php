<?php

class externalRequest extends errorLogger
{
    protected $requestDomain = false;
    protected $requestURI = false;
    protected $requestPort = 80;
    protected $requestResource;
    protected $requestParameters;
    protected $requestType = false;
    protected $requestString = false;
    protected $requestStatus = false;
    protected $requestBody;
    protected $protocol;
    protected $contentType;
    protected $postParameters;
    protected $fullRequestString = '';
    protected $dataString = '';
    protected $requestParametersList = [];
    protected $headerErrors = [];
    protected $badAnswer = '';
    protected $requestHeaders = [];
    protected $responseReadTimeout;
    protected $curlOptions = [];

    public function __construct(
        $requestDomain = '',
        $requestURI = '',
        $requestParametersList = [],
        $requestType = 'GET',
        $requestPort = 80
    ) {
        $this->requestDomain = $requestDomain;
        $this->requestPort = $requestPort;
        $this->requestURI = $requestURI;
        $this->requestType = $requestType;

        $this->setRequestParameters($requestParametersList);
    }

    public function setRequestParameters($parametersList)
    {
        $parameterStrings = [];
        foreach ($parametersList as $parameterName => &$parameterValue) {
            $parameterStrings[] = $parameterName . '=' . urlencode($parameterValue);
        }

        $this->requestString = implode("&", $parameterStrings);
        $this->requestParameters = $parametersList;

        return true;
    }

    public function setPostParameters($parameters)
    {
        $this->postParameters = $parameters;
        $parameterStrings = [];
        foreach ($parameters as $parameterName => &$parameterValue) {
            $parameterStrings[] = $parameterName . '=' . urlencode($parameterValue);
        }

        $this->requestBody = implode("&", $parameterStrings);

        return true;
    }

    public function getData()
    {
        $this->dataString = false;
        $this->makeHTTPRequest();
        return $this->dataString;
    }

    protected function makeHTTPRequest()
    {
        $result = false;

        $protocol = "";
        if ($this->protocol) {
            $protocol = $this->protocol . "://";
        }

        $this->fullRequestString = $protocol . $this->requestDomain . ':' . $this->requestPort . $this->requestURI . '?' . $this->requestString;
        $this->requestResource = curl_init();
        curl_setopt($this->requestResource, CURLOPT_URL, $this->fullRequestString);
        curl_setopt($this->requestResource, CURLOPT_RETURNTRANSFER, 1);

        switch ($this->requestType) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($this->requestResource, CURLOPT_POST, true);
                if ($this->postParameters) {
                    curl_setopt($this->requestResource, CURLOPT_POSTFIELDS, $this->postParameters);
                }
                break;
            default:
                curl_setopt($this->requestResource, CURLOPT_CUSTOMREQUEST, $this->requestType);
        }
        foreach ($this->curlOptions as $key => $value) {
            curl_setopt($this->requestResource, $key, $value);
        }
        if ($this->contentType) {
            $this->requestHeaders[] = "Content-Type: " . $this->contentType;
        }
        if ($this->requestHeaders) {
            curl_setopt($this->requestResource, CURLOPT_HTTPHEADER, $this->requestHeaders);
        }
        if ($this->requestBody) {
            curl_setopt($this->requestResource, CURLOPT_POSTFIELDS, $this->requestBody);
        }
        if ($response = curl_exec($this->requestResource)) {
            $httpStatus = curl_getinfo($this->requestResource, CURLINFO_HTTP_CODE);
            if ($httpStatus == 200) {
                $this->dataString = $response;
                $result = true;
            } else {
                $this->badAnswer = $response;
                $this->logError('Invalid HTTP answer status: "' . $httpStatus . '"');
            }
        } else {
            $this->logError('Request socket opening problem [' . $this->fullRequestString . ']: ' . curl_error($this->requestResource));
        }
        curl_close($this->requestResource);
        return $result;
    }

    public function setRequestDomain($domain)
    {
        $this->requestDomain = $domain;
    }

    public function setRequestPort($port)
    {
        $this->requestPort = $port;
    }

    public function setRequestUri($requestURI)
    {
        $this->requestURI = $requestURI;
    }

    public function setRequestType($requestType)
    {
        $this->requestType = $requestType;
    }

    public function setRequestBody($requestBody)
    {
        $this->requestBody = $requestBody;
    }

    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    public function getBadAnswer()
    {
        return $this->badAnswer;
    }

    public function addRequestHeader($header)
    {
        $this->requestHeaders[] = $header;
    }

    public function addBasicAuthHeader($user, $password)
    {
        $this->requestHeaders[] = 'Authorization: Basic ' . base64_encode("$user:$password");
    }

    public function setCurlOption($key, $value)
    {
        return $this->curlOptions[$key] = $value;
    }
}

