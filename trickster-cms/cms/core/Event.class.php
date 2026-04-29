<?php

class Event
{
    /**
     * @var string
     */
    protected $type = '';
    /**
     * @var int
     */
    protected $visitorId = 0;
    /**
     * @var int
     */
    protected $time = 0;
    /**
     * @var int
     */
    protected $elementId = 0;
    /**
     * @var array
     */
    protected $parameters = [];


    public function __construct() {
        $this->setTime(time());
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getVisitorId()
    {
        return $this->visitorId;
    }

    /**
     * @param int $visitorId
     */
    public function setVisitorId($visitorId)
    {
        $this->visitorId = $visitorId;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param int $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return int
     */
    public function getElementId()
    {
        return $this->elementId;
    }

    /**
     * @param int $elementId
     */
    public function setElementId($elementId)
    {
        $this->elementId = $elementId;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }
}