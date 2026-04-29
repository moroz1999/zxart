<?php

class bottomMenuTab extends Tab
{
    use TabTrait;

    protected function init()
    {
        $this->action = 'showFullList';
        $this->view = 'bottomMenu';
        $this->icon = 'icon_bottomMenu';
    }
}