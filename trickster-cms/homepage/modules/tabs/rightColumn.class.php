<?php

class rightColumnTab extends Tab
{
    use TabTrait;

    protected function init()
    {
        $this->action = 'showFullList';
        $this->view = 'rightColumn';
        $this->icon = 'icon_rightColumn';
    }
}