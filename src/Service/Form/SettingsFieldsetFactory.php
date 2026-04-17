<?php
namespace AdminAddon\Service\Form;

use AdminAddon\Form\SettingsFieldset;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class SettingsFieldsetFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $class = new SettingsFieldset();
        $class->setServiceLocator($serviceLocator);
        return $class;
    }
}
