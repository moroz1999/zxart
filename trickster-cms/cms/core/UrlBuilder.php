<?php

class UrlBuilder
{
    /**
     * @param $parameters
     * @param $currentUrl
     * @param array $excludedParameters
     * @param bool $encoded
     * @return string
     */
    public function getUrlParametersString($parameters, $currentUrl, $excludedParameters=[], $encoded = false)
    {
        $imploded = "";
        foreach ($parameters as $key => $value) {
            if (!empty($excludedParameters) && in_array($key, $excludedParameters)) {
                continue;
            }
            if (!is_array($value)) {
                if ($encoded) {
                    $imploded .= $key . ":" . urlencode($value) . "/";
                } else {
                    $imploded .= $key . ":" . $value . "/";
                }
            }
        }
        return $currentUrl.$imploded;
    }

}