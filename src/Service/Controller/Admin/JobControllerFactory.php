<?php
namespace JobAddon\Service\Controller\Admin;

use Interop\Container\ContainerInterface;
use JobAddon\Controller\Admin\JobController;
use Laminas\ServiceManager\Factory\FactoryInterface;

class JobControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new JobController($services);
    }
}
