<?php

class leftColumnTab extends Tab
{
    use TabTrait;

    protected function init()
    {
        $this->action = 'showFullList';
        $this->view = 'leftColumn';
        $this->icon = 'icon_leftColumn';
    }
}