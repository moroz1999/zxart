<?php

class googleSocialNetworkAdapter extends SocialNetworkAdapter
{
    protected $clientId = '';
    protected $clientSecret = '';
    protected $client;
    protected $userInfo;

    public function getAuthorizedUserData()
    {
        $result = null;
        $userInfo = $this->getUserInfo();
        if ($userInfo) {
            $result = new SocialNetworkUserInfo();
            $result->id = $userInfo->id;
            $result->firstName = $userInfo->givenName;
            $result->lastName = $userInfo->familyName;
            $result->email = $userInfo->email;
        }
        return $result;
    }

    public function getSessionUserId()
    {
        $result = '';
        if ($userInfo = $this->getUserInfo()) {
            $result = $userInfo->id;
        }
        return $result;
    }

    public function getAuthorizationToken()
    {
        return $this->getClient()->getAccessToken();
    }

    public function getAuthRedirectUrl()
    {
        return $this->getClient()->createAuthUrl();
    }

    public function setCredentials(array $credentials)
    {
        $this->clientId = $credentials['clientId'];
        $this->clientSecret = $credentials['clientSecret'];
    }

    public function authenticate($code)
    {
        $this->getClient()->authenticate($code);
    }

    public function useAccessToken($token)
    {
        $this->getClient()->setAccessToken($token);
    }

    protected function getUserInfo()
    {
        if ($this->userInfo === null) {
            $service = new Google_Service_Oauth2($this->getClient());
            $this->userInfo = $service->userinfo->get();
        }
        return $this->userInfo;
    }

    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = $client = new Google_Client();
            $client->setClientId($this->clientId);
            $client->setClientSecret($this->clientSecret);
            $client->setRedirectUri($this->authReturnUrl);
            $client->setScopes([
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/plus.login',
                'https://www.googleapis.com/auth/userinfo.profile',
            ]);
        }
        return $this->client;
    }
}

