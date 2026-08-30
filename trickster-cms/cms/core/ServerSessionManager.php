<?php

use App\Paths\PathsManager;

class ServerSessionManager
{
    protected ?string $sessionId = null;
    protected string $sessionName = '';
    protected int $sessionLifeTime = 1440;
    protected $sessionsPath;
    protected bool $started = false;
    protected bool $enabled = false;

    public function getSessionName(): string
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

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function __construct(
        protected PathsManager $pathsManager,
        protected ConfigManager $configManager,
    )
    {
    }

    public function setSessionLifeTime(int $lifetime): void
    {
        $this->sessionLifeTime = $lifetime;
    }

    public function setSessionName(string $sessionName): void
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
            if (headers_sent()) {
                // The response is already flushed (e.g. a late write from CurrentUser::__destruct()).
                // A session can no longer be started, so skip it instead of emitting PHP warnings.
                $this->started = true;
                return;
            }
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
            }
            session_set_cookie_params(['lifetime' => $this->sessionLifeTime] + $this->cookieAttributes());

            $carriedCookie = isset($_COOKIE[$this->sessionName]);
            session_start();
            if ($this->sessionId === null) {
                $sessionId = session_id();
                $this->sessionId = $sessionId === false ? null : $sessionId;
            }
            if ($carriedCookie) {
                $this->prolongCookie();
            }
        }
    }

    /**
     * PHP sends the session cookie only when it creates a session, so a visitor
     * who signed in an hour ago would be dropped mid-work however active they
     * are. Re-sending the cookie on every request that carried it makes the
     * configured lifetime the idle timeout it is meant to be.
     */
    protected function prolongCookie(): void
    {
        if ($this->sessionLifeTime <= 0 || headers_sent()) {
            return;
        }

        setcookie(
            $this->sessionName,
            (string)session_id(),
            ['expires' => time() + $this->sessionLifeTime] + $this->cookieAttributes(),
        );
    }

    /**
     * `secure` follows `main.protocol`, the protocol the site declares itself
     * served over and already 301-redirects to. Reading it from the request
     * instead would leave the flag off wherever TLS terminates ahead of PHP.
     *
     * @return array{path: string, secure: bool, httponly: bool, samesite: string}
     */
    protected function cookieAttributes(): array
    {
        return [
            'path' => '/',
            'secure' => $this->configManager->get('main.protocol') === 'https://',
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }

    public function close()
    {
        session_write_close();
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * A session exists only once something has been written to it. Reads must
     * therefore never bring one into being: a visitor who stores nothing on the
     * server costs no session file and gets no session cookie.
     */
    protected function sessionExists(): bool
    {
        return $this->started
            || session_id() !== ''
            || isset($_COOKIE[$this->sessionName]);
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
        if ($this->enabled && $this->sessionExists()) {
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
        if ($this->enabled && $this->sessionExists()) {
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
        if ($this->enabled && $this->sessionExists()) {
            if (!$this->started) {
                $this->startSession();
            }
            return $_SESSION;
        }
        return null;
    }
}