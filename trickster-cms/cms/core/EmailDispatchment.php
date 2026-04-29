<?php

class EmailDispatchment
{
    protected $id;
    protected $referenceId;
    protected $type;
    protected $receiversList;
    protected $attachmentsList;
    protected $data;
    protected $startTime;
    protected $dataLifeTime;
    protected $priority;
    protected $fromName;
    protected $fromEmail;
    protected $subject;
    protected $unsubscribeLink;
    /**
     * @var EmailDispatchmentReceiver
     */
    protected $receiver;
    protected $emailDispatcher;

    /**
     * @param EmailDispatcher $emailDispatcher
     */
    public function setEmailDispatcher($emailDispatcher)
    {
        $this->emailDispatcher = $emailDispatcher;
    }

    /**
     * @return EmailDispatcher
     */
    public function getEmailDispatcher()
    {
        return $this->emailDispatcher;
    }

    /**
     * @param mixed $receiver
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
    }

    public function __construct()
    {
        $this->dataLifeTime = 60 * 60 * 24 * 365 * 7;
        $this->priority = 0;
        $this->startTime = time();
        $this->receiversList = [];
        $this->attachmentsList = [];
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setData($data)
    {
        $this->data = json_encode($data);
    }

    public function mb_unserialize($string)
    {
        $string = preg_replace_callback(
            '!s:(\d+):"(.*?)";!s',
            function ($m) {
                $len = strlen($m[2]);
                $result = "s:$len:\"{$m[2]}\";";
                return $result;
            },
            $string
        );
        return unserialize($string);
    }

    public function getData()
    {
        $data = json_decode($this->data, true);
        if ($data === null) {
            $data = $this->mb_unserialize($this->data);
        }
        return $data;
    }

    public function setDataLifeTime($dataLifeTime)
    {
        $this->dataLifeTime = $dataLifeTime;
    }

    public function getDataLifeTime()
    {
        return $this->dataLifeTime;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function registerReceiver($email, $name = null, $referenceId = null)
    {
        $this->receiversList[] = [
            'email' => $email,
            'name' => $name,
            'referenceId' => $referenceId,
        ];
    }

    public function registerAttachment($filePath, $fileName)
    {
        $this->attachmentsList[] = [
            'filePath' => $filePath,
            'fileName' => $fileName,
        ];
    }

    public function getReceiversList()
    {
        return $this->receiversList;
    }

    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;
    }

    public function getReferenceId()
    {
        return $this->referenceId;
    }

    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;
    }

    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
    }

    public function getFromName()
    {
        return $this->fromName;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function clearData()
    {
        $collection = persistableCollection::getInstance('email_dispatchments');
        foreach ($collection->load(['id' => $this->id]) as $dataObject) {
            $dataObject->data = '';
            $dataObject->persist();
        }
    }

    public function persist()
    {
        $collection = persistableCollection::getInstance('email_dispatchments');
        $dataObject = $collection->getEmptyObject();
        $dataObject->referenceId = $this->referenceId;
        $dataObject->type = $this->type;
        $dataObject->data = $this->data;
        $dataObject->startTime = $this->startTime;
        $dataObject->dataLifeTime = $this->dataLifeTime;
        $dataObject->priority = $this->priority;
        $dataObject->fromName = $this->fromName;
        $dataObject->fromEmail = $this->fromEmail;
        $dataObject->subject = $this->subject;

        $dataObject->persist();

        $this->id = $dataObject->id;

        $collection = persistableCollection::getInstance('email_dispatchments_history');
        foreach ($this->receiversList as &$receiverData) {
            $dataObject = $collection->getEmptyObject();
            $dataObject->referenceId = $receiverData['referenceId'];
            $dataObject->dispatchmentId = $this->id;
            $dataObject->name = $receiverData['name'];
            $dataObject->email = $receiverData['email'];
            $dataObject->startTime = $this->startTime;
            $dataObject->status = 'awaiting';

            $dataObject->persist();
        }
        $collection = persistableCollection::getInstance('email_dispatchments_attachments');
        foreach ($this->attachmentsList as &$attachmentData) {
            $dataObject = $collection->getEmptyObject();
            $dataObject->dispatchmentId = $this->id;
            $dataObject->fileName = $attachmentData['fileName'];
            $dataObject->filePath = $attachmentData['filePath'];

            $dataObject->persist();
        }
    }

    public function setPersistedData($dataObject)
    {
        $this->id = $dataObject->id;
        $this->referenceId = $dataObject->referenceId;
        $this->type = $dataObject->type;
        $this->data = $dataObject->data;
        $this->startTime = $dataObject->startTime;
        $this->dataLifeTime = $dataObject->dataLifeTime;
        $this->priority = $dataObject->priority;
        $this->fromName = $dataObject->fromName;
        $this->fromEmail = $dataObject->fromEmail;
        $this->subject = $dataObject->subject;
    }

    public function cancelSending()
    {
        $collection = persistableCollection::getInstance('email_dispatchments_history');
        if ($objects = $collection->load([
            'dispatchmentId' => $this->id,
            'status' => 'awaiting',
        ])
        ) {
            foreach ($objects as &$object) {
                if ($object->status == 'awaiting') {
                    $object->status = 'cancelled';
                    $object->persist();
                }
            }
        }
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Dispatches one email receiver from email dispatchers receivers.
     * Returns false if there are no receivers left, otherwise returns string status of email sending
     *
     * @return bool|string
     */
    public function dispatchAwaitingItem()
    {
        $result = false;

        if ($this->receiver = $this->getNextAwaitingItem()) {
            if ($content = $this->getContent()) {
                $this->receiver->setStatus('inprogress');
                $this->receiver->persist();

                $result = 'fail';
                if ($this->sendEmail($content, $this->receiver->getEmail())) {
                    $result = 'success';
                }

                $this->receiver->setStatus($result);
                $this->receiver->persist();
            }
        }

        return $result;
    }

    public function getContent($preview = false)
    {
        $renderer = new EmailDispatchmentRenderer();
        $renderer->setDesignThemesManager($this->getEmailDispatcher()->getDesignThemesManager());
        $renderer->setData($this->getData());
        $renderer->setType($this->getType());
        $renderer->setDispatchment($this);

        if (!$preview && $this->receiver) {
            $renderer->setUnsubscribleLink($this->receiver->getUnsubscribeLink());
            $renderer->setWebLink($this->receiver->getWebLink());
            $renderer->setReceiverEmail($this->receiver->getEmail());
            $renderer->setReceiverName($this->receiver->getName());
        }

        $renderer->setFromEmail($this->getFromEmail());
        $renderer->setFromName($this->getFromName());
        $renderer->setSubject($this->getSubject());
        $renderer->setDispatchmentId($this->getId());
        return $renderer->renderContent();
    }

    protected function sendEmail($content, $receiverEmail)
    {
        $sender = new EmailDispatchmentSender();
        return $sender->sendEmail($content, $receiverEmail, $this->fromEmail, $this->fromName, $this->subject, $this->loadAttachmentsList(), $this->receiver->getUnsubscribeLink());
    }

    protected function getNextAwaitingItem()
    {
        $receiver = false;
        $collection = persistableCollection::getInstance('email_dispatchments_history');
        $order = ['startTime' => 'asc'];
        if ($objects = $collection->load([
            'dispatchmentId' => $this->id,
            'status' => 'awaiting',
        ], $order, false, 1)
        ) {
            $dispatchment = reset($objects);
            $receiver = new EmailDispatchmentReceiver($dispatchment);
        }
        return $receiver;
    }

    public function getType()
    {
        return $this->type;
    }

    protected function loadAttachmentsList()
    {
        if (is_null($this->attachmentsList)) {
            $this->attachmentsList = [];
            $collection = persistableCollection::getInstance('email_dispatchments_history');
            if ($objects = $collection->load(['dispatchmentId' => $this->id])) {
                foreach ($objects as &$object) {
                    $this->attachmentsList[] = [
                        "filePath" => $object->filePath,
                        "fileName" => $object->fileName,
                    ];
                }
            }
        }
        return $this->attachmentsList;
    }
}
