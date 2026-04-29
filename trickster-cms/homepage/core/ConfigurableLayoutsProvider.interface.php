<?php

interface ConfigurableLayoutsProviderInterface
{
    public function getLayoutsSelection($layout = "layout");

    public function getDefaultLayout($layout = "layout");

    public function getCurrentLayout();

    public function getLayoutTypes();
}