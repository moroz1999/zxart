<?php

abstract class validator implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    abstract public function execute($formValue);
}

