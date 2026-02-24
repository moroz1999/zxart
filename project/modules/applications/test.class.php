<?php

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

        $this->createRenderer();
    }

    /**
     * @return void
     */
    public function execute($controller)
    {
        echo 123;
        $redis = $this->getService(Redis::class);
        echo 1;
        $keys = $redis->keys('reqlog:*');

        var_dump($keys);
    }

}