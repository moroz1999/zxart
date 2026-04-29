<?php

use App\Paths\PathsManager;
use App\Users\CurrentUserService;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

/**
 * Controller application is standardized script, which purpose is to receive external parameters (whether from GET/POST or other objects), operate some business logic according to them and optionally provide some rendered answer
 */
abstract class controllerApplication extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    use RequestsLogger;

    protected PathsManager $pathsManager;
    /**
     * @var string - used in URL building by default
     */
    protected $applicationName;
    /**
     * @var string - the name of renderer plugin used by application
     */
    public $rendererName;
    public rendererPlugin $renderer;
    /**
     * @var controller
     */
    protected $controller;

    public function __construct(controller $controller)
    {
        $this->controller = $controller;

        $this->setContainer($controller->getContainer());

        //temporary workaround for renderer object. Remove after "renderers" architecture change
        if ($factory = renderer::getFactory()) {
            $this->instantiateContext($factory);
        }

        $this->logRequest();
    }

    public function setPathsManager(PathsManager $pathsManager): void
    {
        $this->pathsManager = $pathsManager;
    }

    /**
     * Start session under specified name
     *
     * @param string $sessionName
     * @param $lifeTime
     */
    protected function startSession(string $sessionName = 'default', $lifeTime = false)
    {
        $clientIpAddress = $_SERVER['REMOTE_ADDR'];
        $serverIpAddress = $_SERVER['SERVER_ADDR'];

        $isRequestFromServer = $clientIpAddress === $serverIpAddress;
        if (!$isRequestFromServer){
            $isCrawler = new CrawlerDetect()->isCrawler();
            if ($isCrawler === true) {
                return;
            }
        }

        /**
         * @var $sessionManager ServerSessionManager
         */
        $sessionManager = $this->getService(ServerSessionManager::class);
        $sessionManager->setSessionName($sessionName);
        $sessionManager->setEnabled(true);
        if ($lifeTime) {
            $sessionManager->setSessionLifeTime($lifeTime);
        }
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Creates renderer plugin object for specified renderer name
     */
    protected function createRenderer()
    {
        if ($this->renderer = renderer::createInstance($this->rendererName)) {
            $controller = controller::getInstance();
            $this->renderer->debugMode = $controller->getDebugMode();
        }
    }

    /**
     * Every application can specify its name used in URL building. It's name can also be empty if needed to omit.
     *
     * @return string|null
     */
    public function getUrlName()
    {
        return $this->applicationName;
    }

    public function getApplicationName()
    {
        return $this->applicationName;
    }

    public function getRequestUrl()
    {
        return controller::getInstance()->fullParametersURL;
    }

    public function getParameter($parameter)
    {
        return $this->controller->getParameter($parameter);
    }

    public function getUser()
    {
        $currentUserService = $this->getService(CurrentUserService::class);
        return $currentUserService->getCurrentUser();
    }

    /**
     * Always called before execution of application
     *
     * @abstract
     * @return boolean
     */
    abstract public function initialize();

    /**
     * Starts execution of application main logic, usually echos some rendered content as well through renderer plugin
     *
     * @abstract
     * @param controller $controller
     * @return mixed
     */
    abstract public function execute($controller);

    public function executeEnd()
    {
        $this->logRequestDuration();
    }

    public function getDesignThemesManager()
    {
        return $this->getService(DesignThemesManager::class);
    }

    public function getLanguagesManager()
    {
        return $this->getService(LanguagesManager::class);
    }
}


