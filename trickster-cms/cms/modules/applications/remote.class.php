<?php

use Jaybizzle\CrawlerDetect\CrawlerDetect;

//this is visitor tracking starting app.
class remoteApplication extends controllerApplication
{
    protected $applicationName = 'remote';
    protected $mode = 'public';

    public function initialize()
    {
        $controller = controller::getInstance();
        if ($controller->getParameter('mode')) {
            $mode = $controller->getParameter('mode');
            if ($mode == 'admin') {
                $this->mode = 'admin';
            } else {
                $this->mode = 'public';
            }
        }
        $configManager = $controller->getConfigManager();
        $this->startSession('public', $configManager->get('main.publicSessionLifeTime'));
    }

    public function execute($controller)
    {
        $crawler = (new CrawlerDetect())->isCrawler();
        if ($crawler) {
            $this->deliverResponse(403);
        }
        $action = $controller->getParameter('action');
        if (!method_exists($this, 'action' . $action)) {
            $this->deliverResponse(404);
        }
        call_user_func([$this, 'action' . $action]);
    }

    public function getUrlName()
    {
        return '';
    }

    protected function actionStart()
    {
        $visitorsManager = $this->getService(VisitorsManager::class);
        $visitorsManager->recordVisit($this->controller->getParameter('referrer'));
    }

    protected function deliverResponse($code)
    {
        $httpResponse = CmsHttpResponse::getInstance();
        $httpResponse->setStatusCode($code);
        $httpResponse->setContentLength(0);
        $httpResponse->setCacheControl('no-cache');
        $httpResponse->sendHeaders();
        die;
    }
}