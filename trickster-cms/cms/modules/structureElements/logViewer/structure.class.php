<?php

class logViewerElement extends structureElement
{
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    protected $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'container';
    public $apiError = '';
    public $debugError = '';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    public function getLogsList()
    {
        $result = [];
        $times = [];
        $logPath = $this->getService(ConfigManager::class)->get('paths.logs');
        if (is_dir($logPath)) {
            foreach (new DirectoryIterator($logPath) as $fileInfo) {
                if (!$fileInfo->isFile()) {
                    continue;
                }
                $times[] = $fileInfo->getMTime();
                $result[] = $fileInfo->getFileInfo();
            }
        }
        array_multisort($times, SORT_ASC, $result);
        return $result;
    }

    public function getSelectedLogContents()
    {
        $result = false;
        if ($selectedLogPath = $this->getSelectedLogPath()) {
            $result = $this->getLogContents($selectedLogPath);
        }
        return $result;
    }

    public function getSelectedLogErrors()
    {
        $result = false;
        if ($selectedLogPath = $this->getSelectedLogPath()) {
            $result = $this->getAggregatedLogMessages($selectedLogPath);
        }
        return $result;
    }

    public function getSelectedLogPath()
    {
        $result = false;
        $logFileArgument = $this->getSelectedLogFileName();
        if ($logFileArgument && strpos($logFileArgument, '/') === false && strpos($logFileArgument, '\\') === false) {
            $logPath = $this->getService(ConfigManager::class)->get('paths.logs');
            $result = $logPath . $logFileArgument;
        }
        return $result;
    }

    public function getLogContents($path)
    {
        $result = false;
        if (is_file($path)) {
            $result = file_get_contents($path);
        }
        return $result;
    }

    public function getSelectedLogFileName()
    {
        return $this->getService(controller::class)->getParameter('log');
    }

    public function getLatestTopErrors($amount = 5)
    {
        $topMessages = false;
        if ($logs = $this->getLogsList()) {
            if ($lastLog = end($logs)) {
                if ($aggregatedMessages = $this->getAggregatedLogMessages($lastLog)) {
                    $topMessages = array_splice($aggregatedMessages, 0, $amount);
                };
            }
        }
        return $topMessages;
    }

    protected function getAggregatedLogMessages($logPath)
    {
        if ($messages = $this->getLogMessages($logPath)) {
            if ($aggregatedMessages = $this->aggregateMessages($messages)) {
                return $aggregatedMessages;
            }
        }
        return false;
    }

    protected function getLogMessages($logPath)
    {
        $logMessages = [];
        if ($content = $this->getLogContents($logPath)) {
            $logMessage = [];
            foreach (preg_split("/((\r?\n)|(\r\n?))/", $content) as $line) {
                if (isset($logMessage['uri'])) {
                    if (substr($line, 0, 16) == '- HTTP_REFERER: ') {
                        $logMessage['referrer'] = substr($line, 16);
                        $logMessages[] = $logMessage;
                        $logMessage = [];
                    }
                } elseif (isset($logMessage['error'])) {
                    if (substr($line, 0, 15) == '- REQUEST_URI: ') {
                        $logMessage['uri'] = substr($line, 15);
                    }
                } elseif (isset($logMessage['date'])) {
                    if ($line) {
                        $logMessage['error'] = substr($line, 2);
                    }
                } elseif (preg_match('#(^|\n)[0-9]{4}-[0-1][0-9]-[0-3][0-9] [0-2][0-9]:[0-5][0-9]#i', $line)) {
                    $logMessage['date'] = $line;
                }
            }
        }
        return $logMessages;
    }

    protected function aggregateMessages($messages)
    {
        $aggregatedMessages = [];
        foreach ($messages as $message) {
            if (!isset($aggregatedMessages[$message['error']])) {
                $aggregatedMessages[$message['error']] = $message;
                $aggregatedMessages[$message['error']]['count'] = 1;
            } else {
                $aggregatedMessages[$message['error']]['count']++;
            }
        }
        $sort = [];
        foreach ($aggregatedMessages as $aggregatedMessage) {
            $sort[] = $aggregatedMessage['count'];
        }
        array_multisort($sort, SORT_DESC, $aggregatedMessages);
        return $aggregatedMessages;
    }
}