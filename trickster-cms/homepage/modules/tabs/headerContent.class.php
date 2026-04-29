<?php

class headerContentTab extends Tab
{
    use TabTrait;

    protected function init()
    {
        $this->action = 'showFullList';
        $this->view = 'headerContent';
        $this->icon = 'icon_headerContent';
    }
}