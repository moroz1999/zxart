<?php

abstract class SocialNetworkAdapter extends errorLogger
{
    protected $credentials = [];
    protected $authReturnUrl = '';

    public abstract function getAuthorizedUserData();

    public abstract function getSessionUserId();

    public abstract function getAuthorizationToken();

    public abstract function getAuthRedirectUrl();

    public abstract function useAccessToken($token);

    public abstract function authenticate($code);

    public function setCredentials(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function setAuthReturnUrl($authUrl)
    {
        $this->authReturnUrl = $authUrl;
    }

    protected function htmlToPlainText($src)
    {
        $result = $src;
        $result = html_entity_decode($result, ENT_QUOTES);
        $result = preg_replace('/[\xA0]*/', '', $result);
        $result = preg_replace('#[\n\r\t]#', "", $result);
        $result = preg_replace('#[\s]+#', " ", $result);
        $result = preg_replace('#(</li>|</div>|</td>|</tr>|<br />|<br/>|<br>)#', "$1\n", $result);
        $result = preg_replace('#(</h1>|</h2>|</h3>|</h4>|</h5>|</p>)#', "$1\n\n", $result);
        $result = strip_tags($result);
        $result = preg_replace('#^ +#m', "", $result); //left trim whitespaces on each line
        $result = preg_replace('#([\n]){2,}#', "\n\n", $result); //limit newlines to 2 max
        $result = trim($result);
        return $result;
    }
}