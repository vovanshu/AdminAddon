<?php

namespace AdminAddon\Service\ViewHelper;

use AdminAddon\View\Helper\FacetsHelper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FacetsFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        return new FacetsHelper($services);
    }
}
