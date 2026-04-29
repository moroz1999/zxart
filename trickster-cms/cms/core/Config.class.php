<?php

class Config
{
    protected $data = [];
    /**
     * @var Config
     */
    protected $linkedConfig;
    /**
     * @var ConfigManager
     */
    protected $configManager;
    protected $path;

    public function __construct($configManager, $data, $path)
    {
        $this->data = $data;
        $this->path = $path;
        $this->configManager = $configManager;
    }

    public function __toArray()
    {
        return $this->data;
    }

    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        } elseif ($this->linkedConfig !== null) {
            return $this->linkedConfig->get($key);
        }

        return null;
    }

    public function getMerged($key)
    {
        if (isset($this->data[$key])) {
            $value = $this->data[$key];
        } else {
            $value = [];
        }
        if ($this->linkedConfig !== null) {
            if ($linkedValue = $this->linkedConfig->getMerged($key)) {
                $value = array_merge($value, $linkedValue);
                $value = array_unique($value);
            }
        }

        return $value;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function has($key)
    {
        return isset($this->data[$key]) || ($this->linkedConfig !== null && $this->linkedConfig->has($key));
    }

    public function getData()
    {
        return $this->data;
    }

    public function getLinkedData()
    {
        $result = $this->data;
        if ($this->linkedConfig !== null) {
            $result = array_merge($this->linkedConfig->getLinkedData(), $result);
        }
        return $result;
    }

    public function setData(array $newData)
    {
        $this->data = $newData;
    }

    public function linkConfig(Config $config)
    {
        if ($this->linkedConfig === null) {
            $this->linkedConfig = $config;
        } else {
            $this->linkedConfig->linkConfig($config);
        }
    }

    public function isEmpty()
    {
        return !$this->data;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($newPath)
    {
        $this->path = $newPath;
    }

    public function save()
    {
        $this->configManager->saveConfig($this);
    }

    public function refresh()
    {
        $this->configManager->refreshConfig($this);
    }
}
