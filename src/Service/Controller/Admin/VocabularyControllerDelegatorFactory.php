<?php
namespace AdminAddon\Service\Controller\Admin;

use Interop\Container\ContainerInterface;
use AdminAddon\Controller\Admin\VocabularyControllerDelegator;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;

class VocabularyControllerDelegatorFactory implements DelegatorFactoryInterface
{
    public function __invoke(ContainerInterface $services, $name, callable $callback, ?array $options = null)
    {
        return new VocabularyControllerDelegator($services, $name, $callback, $options);
    }
}
