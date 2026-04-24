<?php
namespace AdminAddon\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use AdminAddon\Facets;

class FacetsHelper extends AbstractHelper
{

    use Facets;

    public function __construct($serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

}
