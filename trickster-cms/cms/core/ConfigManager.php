<?php

class ConfigManager
{
    protected $configs = [];
    protected $paths = [];
    protected $parser = [];
    protected $projectPath = '';

    /**
     * @param bool $forceProjectPath
     */
    public function getConfig($name, $forceProjectPath = false): ?Config
    {
        if (!array_key_exists($name, $this->configs)) {
            if ($forceProjectPath) {
                if ($path = $this->projectPath) {
                    $configPath = $path . $name . '.php';
                    if ($config = $this->getConfigFromPath($configPath, true)) {
                        $this->configs[$name] = $config;
                        foreach ($this->paths as $path) {
                            if ($path !== $this->projectPath) {
                                $configPath = $path . $name . '.php';
                                if ($config = $this->getConfigFromPath($configPath, true)) {
                                    if (isset($this->configs[$name])) {
                                        $this->configs[$name]->linkConfig($config);
                                    } else {
                                        $this->configs[$name] = $config;
                                    }
                                }
                            }
                        }
                    }
                }
            } elseif ($this->paths) {
                foreach ($this->paths as $path) {
                    $configPath = $path . $name . '.php';
                    if ($config = $this->getConfigFromPath($configPath, true)) {
                        if (isset($this->configs[$name])) {
                            $this->configs[$name]->linkConfig($config);
                        } else {
                            $this->configs[$name] = $config;
                        }
                    }
                }
            }
            if (!isset($this->configs[$name])) {
                $this->configs[$name] = new Config($this, [], $this->projectPath . $name . '.php');
            }
        }
        return $this->configs[$name];
    }

    public function get($name)
    {
        $parts = explode('.', $name, 2);
        return $this->getConfig($parts[0])->get($parts[1]);
    }

    public function getMerged($name)
    {
        $parts = explode('.', $name, 2);
        return $this->getConfig($parts[0])->getMerged($parts[1]);
    }

    public function addSource($path, $primary = false): void
    {
        if ($primary) {
            $this->projectPath = $path;
        }
        $this->paths[] = $path;
        $this->paths = array_unique($this->paths);
    }

    public function saveConfigByName($name): void
    {
        if (isset($this->configs[$name])) {
            $this->saveConfig($this->configs[$name]);
        }
    }

    public function refreshConfig(Config $config): void
    {
        $data = $this->read($config->getPath());
        $config->setData($data);
    }

    public function saveConfig(Config $config): void
    {
        $this->write($config->getData(), $config->getPath());
    }

    public function getConfigFromPath($path, $dataRequired = false): ?Config
    {
        $data = $this->read($path);
        if (!$dataRequired || !empty($data)) {
            return new Config($this, $data, $path);
        }
        return null;
    }

    public function read($path)
    {
        $result = [];
        if (is_file($path)) {
            $result = include $path;
            if ((array)$result !== $result) {
                $result = [];
            }
        }
        return $result;
    }

    public function write(array $configData, $path): void
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory);
        }
        ini_set('default_charset', 'UTF-8');
        $contents = var_export($configData, true);
        $contents = preg_replace('/(\n\s*)([0-9]* => )/', '$1', $contents);
        $contents = preg_replace('/.\=\> \n(.)*array \(/', ' => array (', $contents);
        $contents = str_replace("array (\n", "[\n", $contents);
        $contents = preg_replace('/(.*)\)(,\n)/', "$1]$2", $contents);
        $contents = preg_replace('/\)$/', ']', $contents);
        $contents = preg_replace('/(\n)(\s*\n)/', '$1', $contents);
        $contents = preg_replace('/(?:\G|^) {2}/m', "\t", $contents);
        file_put_contents($path, "<?php return $contents;");
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path);
        }
    }
}
