<?php
namespace AdminAddon\Service\Controller\Admin;

use Laminas\ServiceManager\Factory\FactoryInterface;
use AdminAddon\Controller\Admin\SettingsController;

class SettingsControllerFactory implements FactoryInterface
{
    public function __invoke($services, $requestedName, array $options = null)
    {
        $class = new SettingsController();
        $class->setServiceLocator($services);
        return $class;
    }
}
