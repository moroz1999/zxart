<?php

class PicturesModesManager
{
    /**
     * @var user
     */
    protected $user;
    protected $mode;

    /**
     * @param user $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getModeInfo()
    {
        if ($this->mode === null) {
            $controller = controller::getInstance();
            $this->mode = [];

            if ($controller->getParameter('mode') !== false) {
                $this->mode['mode'] = $controller->getParameter('mode');
                $this->user->setStorageAttribute('mode', $this->mode['mode']);
            } elseif ($gMode = $this->user->getStorageAttribute('mode')) {
                $this->mode['mode'] = $gMode;
            }
            if ($controller->getParameter('border') !== false) {
                $this->mode['border'] = $controller->getParameter('border');
                $this->user->setStorageAttribute('border', $this->mode['border']);
            } elseif (($border = $this->user->getStorageAttribute('border')) !== false) {
                $this->mode['border'] = $border;
            }
            if ($controller->getParameter('hidden') !== false) {
                $this->mode['hidden'] = $controller->getParameter('hidden');
                $this->user->setStorageAttribute('hidden', $this->mode['hidden']);
            } elseif ($hidden = $this->user->getStorageAttribute('hidden')) {
                $this->mode['hidden'] = $hidden;
            }
            if (!isset($this->mode['mode'])) {
                $this->mode['mode'] = 'mix';
            }
            if (!isset($this->mode['border'])) {
                $this->mode['border'] = '1';
            }
            if (!isset($this->mode['hidden'])) {
                $this->mode['hidden'] = '0';
            }
            if ($this->mode['hidden'] == '1') {
                $this->mode['border'] = '1';
            }
        }
        return $this->mode;
    }

    public function getMode()
    {
        return $this->getModeInfo()['mode'];
    }

    public function getBorder()
    {
        return $this->getModeInfo()['border'];
    }

    public function getHidden()
    {
        return $this->getModeInfo()['hidden'];
    }
}