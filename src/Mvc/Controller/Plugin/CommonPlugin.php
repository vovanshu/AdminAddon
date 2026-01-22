<?php declare(strict_types=1);

namespace AdminAddon\Mvc\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use AdminAddon\Common;

class CommonPlugin extends AbstractPlugin
{

    use Common;

    public function __construct($serviceLocator, $requestedName = Null, array $options = null)
    {
        $this->setServiceLocator($serviceLocator);
    }

    public function __invoke()
    {
        return $this;
    }

}
