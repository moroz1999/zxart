<?php

class EmailDispatchmentReceiver extends errorLogger
{
    protected $historyId;
    protected $email;
    protected $name;
    protected $unsubscribeLink;
    protected $webLink;
    /**
     * @var persistableObject
     */
    protected $persistableObject;

    public function __construct(&$persistableObject)
    {
        $this->persistableObject = $persistableObject;
        $this->historyId = $persistableObject->id;
        $this->email = $persistableObject->email;
        $this->name = $persistableObject->name;
        $this->unsubscribeLink = $this->getUnsubscribeLink();
    }

    public function getUnsubscribeLink()
    {
        if (is_null($this->unsubscribeLink)) {
            $controller = controller::getInstance();
            $secret = defined('EMAIL_DISPATCHMENT_SECRET')
                ? EMAIL_DISPATCHMENT_SECRET
                : $controller->getConfigManager()
                    ->get('emails.dispatchmentSecret'); // constant deprecated since 2016.03
            $key = hash_hmac('sha256', $this->email, $secret);
            $this->unsubscribeLink = $controller->baseURL . 'emails/action:unsubscribe/email:' . urlencode($this->email) . '/key:' . $key . '/id:' . $this->historyId;
        }
        return $this->unsubscribeLink;
    }

    public function getWebLink()
    {
        if (is_null($this->webLink)) {
            $controller = controller::getInstance();
            $secret = defined('EMAIL_DISPATCHMENT_SECRET')
                ? EMAIL_DISPATCHMENT_SECRET
                : $controller->getConfigManager()
                    ->get('emails.dispatchmentSecret'); // constant deprecated since 2016.03
            $key = hash_hmac('sha256', $this->email, $secret);
            $this->webLink = $controller->baseURL . 'emails/action:viewOnline/email:' . urlencode($this->email) . '/id:' . $this->historyId . '/key:' . $key;
        }
        return $this->webLink;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setStatus($newStatus)
    {
        $this->persistableObject->status = $newStatus;
    }

    public function persist()
    {
        $this->persistableObject->persist();
    }
}
