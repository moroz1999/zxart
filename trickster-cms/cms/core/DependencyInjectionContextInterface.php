<?php
declare(strict_types=1);

use DI\Container;

interface DependencyInjectionContextInterface
{
    public function setContainer(Container $container);
}