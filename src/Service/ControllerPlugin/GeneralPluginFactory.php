<?php declare(strict_types=1);

namespace AdminAddon\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use AdminAddon\Mvc\Controller\Plugin\GeneralPlugin;

class GeneralPluginFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        return new GeneralPlugin($serviceLocator, $requestedName, $options);
    }
}
