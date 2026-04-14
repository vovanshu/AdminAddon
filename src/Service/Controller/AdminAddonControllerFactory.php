<?php
namespace AdminAddon\Service\Controller;

use Laminas\ServiceManager\Factory\FactoryInterface;
use AdminAddon\Controller\AdminAddonController;

class AdminAddonControllerFactory implements FactoryInterface
{
    public function __invoke($services, $requestedName, ?array $options = null)
    {
        $class = new AdminAddonController();
        $class->setServiceLocator($services);
        return $class;
    }
}
