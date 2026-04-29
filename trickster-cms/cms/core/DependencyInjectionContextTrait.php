<?php
declare(strict_types=1);

use DI\Container;

/**
 * Trait DependencyInjectionContextTrait
 */
trait DependencyInjectionContextTrait
{
    private Container $container;

    /**
     * Returns the service: checks local services first, then falls through to PHP-DI container.
     *
     * @template T
     * @param class-string<T> $type
     * @return T
     */
    public function getService(string $type)
    {
        $service = $this->container->get($type);
        if ($service instanceof DependencyInjectionContextInterface) {
            $this->instantiateContext($service);
        }
        return $service;
    }

    protected function getContainer(): ?Container
    {
        return $this->container;
    }

    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * Passes current DI context (local services + container) to a child object,
     * so the child can resolve services from the same context.
     */
    protected function instantiateContext(DependencyInjectionContextInterface $object): void
    {
        $object->setContainer($this->container);
    }
}

