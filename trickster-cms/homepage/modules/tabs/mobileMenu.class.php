<?php

class mobileMenuTab extends Tab
{
    use TabTrait;

    protected function init()
    {
        $this->action = 'showFullList';
        $this->view = 'mobileMenu';
        $this->icon = 'icon_mobileMenu';
    }
}