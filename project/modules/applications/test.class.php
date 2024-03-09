<?php

use Illuminate\Database\Capsule\Manager;

class testApplication extends controllerApplication
{
    protected $applicationName = 'test';
    public $rendererName = 'smarty';
    /**
     * @var structureManager
     */
    protected $structureManager;
    protected $key;

    /**
     * @return void
     */
    public function initialize()
    {
        ini_set("display_errors", 1);

//        $this->startSession('public');
        $this->createRenderer();
    }

    /**
     * @return void
     */
    public function execute($controller)
    {
        echo 123;
        $redis = $this->getService('Redis');
        echo 1;
        $keys = $redis->keys('reqlog:*');

        var_dump($keys);
    }

}