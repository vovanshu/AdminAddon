<?php
namespace AdminAddon\Service\Form;

use AdminAddon\Form\SiteSettingsFieldset;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class SiteSettingsFieldsetFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $class = new SiteSettingsFieldset();
        $class->setServiceLocator($serviceLocator);
        return $class;
    }
}
