<?php

use App\Paths\PathsManager;
use Illuminate\Database\Connection;

trait DbLoggableApplication
{
    protected $logFilePath;
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var transportObject
     */
    protected $transportObject;

    protected function startDbLogging(): void
    {
        if ($controller = controller::getInstance()) {
            if ($controller->getDebugMode()) {
                $pathsManager = $this->getService(PathsManager::class);

                $this->connection = $this->getService('db');
                $this->logFilePath = $pathsManager->getPath('logs') . 'db_' . time() . '.log';

                $this->connection->enableQueryLog();

                if (!class_exists('pdoTransport')) {
                    $path = $pathsManager->getIncludeFilePath('modules/transportObjects/pdoTransport.class.php');
                    include_once($path);
                }
                $this->transportObject = pdoTransport::getInstance($this->getService(ConfigManager::class)->getConfig('transport'));
                $this->transportObject->setDebug(true);
            }
        }
    }

    protected function saveDbLog(): void
    {
        if ($this->logFilePath) {
            $overall = 0;
            $text = '';
            if ($this->transportObject) {
                if ($log = $this->transportObject->getQueriesHistory()) {
                    foreach ($log as $item) {
                        $text .= $item['time'] . "\t" . $item['query'] . "\r\n";
                        $overall += $item['time'];
                    }
                }
            }
            if ($this->connection) {
                if ($log = $this->connection->getQueryLog()) {
                    foreach ($log as $item) {
                        $query = $item['query'];
                        while (($position = strpos($query, '?')) !== false) {
                            $query = substr_replace($query, "'" . array_shift($item['bindings']) . "'", $position, 1);
                        }
                        $text .= $item['time'] . "\t" . $query . ";\r\n";
                        $overall += $item['time'];
                    }
                }
            }
            $text .= 'Overall SQL time:' . $overall;
            file_put_contents($this->logFilePath, $text);
        }
    }
    public function __destruct()
    {
        $this->saveDbLog();
    }
}
