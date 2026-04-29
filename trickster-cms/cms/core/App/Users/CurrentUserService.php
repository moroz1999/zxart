<?php
declare(strict_types=1);

namespace App\Users;

use privilegesManager;
use ServerSessionManager;

class CurrentUserService
{
    private ?CurrentUser $currentUser = null;

    public function __construct(
        private privilegesManager    $privilegesManager,
        private ServerSessionManager $serverSessionManager
    )
    {
    }

    public function getCurrentUser(): CurrentUser
    {
        if ($this->currentUser === null) {
            $this->currentUser = new CurrentUser($this->privilegesManager, $this->serverSessionManager);
            $this->currentUser->initialize();
        }
        return $this->currentUser;
    }
}