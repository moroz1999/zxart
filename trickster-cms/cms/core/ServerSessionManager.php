<?php

use App\Paths\PathsManager;

class ServerSessionManager
{
    protected $sessionId;
    protected $sessionName;
    protected $sessionLifeTime = 1440;
    protected $sessionsPath;
    protected $started = false;
    protected $enabled = false;

    /**
     * @return string
     */
    public function getSessionName()
    {
        return $this->sessionName;
    }

    /**
     * @return mixed
     */
    public function getSessionsPath()
    {
        if ($this->sessionsPath === null) {
            if ($sessionsPath = $this->pathsManager->getPath('sessionsCache')) {
                $this->sessionsPath = $sessionsPath;
            }
        }
        return $this->sessionsPath;
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function __construct(
        protected PathsManager $pathsManager
    )
    {
        $this->sessionName = '';
    }

    public function setSessionLifeTime($lifetime)
    {
        $this->sessionLifeTime = $lifetime;
    }

    public function setSessionName($sessionName)
    {
        $this->sessionName = $sessionName;
    }

    public function startSession()
    {
        if ($sessionId = session_id()){
            $this->started = true;
            $this->sessionId = $sessionId;
        }
        if ($this->enabled && !$this->started) {
            $this->started = true;
            session_name($this->sessionName);
            if ($this->sessionId !== null) {
                session_id($this->sessionId);
            }

            if ($sessionsPath = $this->getSessionsPath()) {
                $currentSessionPath = $sessionsPath . $this->sessionName . '/';
                $this->pathsManager->ensureDirectory($currentSessionPath);
                session_save_path($currentSessionPath);
            }
            if ($this->sessionLifeTime) {
                ini_set('session.gc_maxlifetime', $this->sessionLifeTime);
                session_set_cookie_params($this->sessionLifeTime);
            }

            session_start();
            if ($this->sessionId === null) {
                $this->sessionId = session_id();
            }
        }
    }

    public function close()
    {
        session_write_close();
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function set($key, $value)
    {
        if ($this->enabled) {
            if (!$this->started) {
                $this->startSession();
            }
            $_SESSION[$key] = $value;
        }
    }

    public function get($key)
    {
        if ($this->enabled) {
            if (!$this->started) {
                $this->startSession();
            }
            if (isset($_SESSION[$key])) {
                return $_SESSION[$key];
            }
        }
        return null;
    }

    public function delete($key)
    {
        if ($this->enabled) {
            if (!$this->started) {
                $this->startSession();
            }
            if (isset($_SESSION[$key])) {
                unset($_SESSION[$key]);
            }
        }
    }

    public function getAll()
    {
        if ($this->enabled) {
            if (!$this->started) {
                $this->startSession();
            }
            return $_SESSION;
        }
        return null;
    }
}