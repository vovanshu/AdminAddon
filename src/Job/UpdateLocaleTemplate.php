<?php declare(strict_types=1);

namespace AdminAddon\Job;

use Omeka\Job\AbstractJob;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use AdminAddon\TraitGeneral;

class UpdateLocaleTemplate extends AbstractJob
{

    use TraitGeneral;

    public function perform(): void
    {

        $moduleName = 'AdminAddon';
        try {
            $result = system(('gulp i18n:module:template --module '.$moduleName));
            if ($result) {
                $this->getLogger()->notice($result);
            }
        } catch (\Exception $e) {
            $this->getLogger()->err($e->getMessage());
            $this->getLogger()->notice('May need to be configured \"gulp\", see the development guide Omeka S');
        }

    }

}
